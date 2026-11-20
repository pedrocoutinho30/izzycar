<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MarketingFormat;
use App\Enums\MarketingPostStatus;
use App\Enums\MarketingPostType;
use App\Http\Controllers\Controller;
use App\Http\Requests\MarketingPostRequest;
use App\Http\Requests\MarketingPostUpdateRequest;
use App\Marketing\PostContent;
use App\Marketing\PostRenderer;
use App\Marketing\TemplateRegistry;
use App\Marketing\VehiclePostData;
use App\Models\MarketingPost;
use App\Marketing\TestimonialPostData;
use App\Models\Testimonial;
use App\Models\V3Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MarketingPostController extends Controller
{
    public function index(Request $request)
    {
        $posts = MarketingPost::with('creator')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->query('q') . '%'))
            ->latest('updated_at')
            ->paginate(24)
            ->withQueryString();

        return view('admin.marketing.index', [
            'posts' => $posts,
            'statuses' => MarketingPostStatus::cases(),
            'types' => MarketingPostType::cases(),
            'filters' => $request->only(['status', 'type', 'q']),
        ]);
    }

    /** Passo 0: escolher o tipo de publicação (só "Viatura" exige uma viatura). */
    public function create()
    {
        return view('admin.marketing.create', ['types' => MarketingPostType::cases()]);
    }

    public function createVehicle()
    {
        return view('admin.marketing.vehicle-create', [
            'templates' => TemplateRegistry::forType(MarketingPostType::Veiculo),
            'formats' => MarketingFormat::cases(),
        ]);
    }

    /** Pesquisa de viaturas do catálogo (JSON) para o passo 1 de "Criar publicação". */
    public function searchVehicles(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $vehicles = V3Vehicle::with('photos')
            ->when($request->boolean('imported'), fn ($query) => $query->where('is_imported', true))
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('brand', 'like', "%{$q}%")
                    ->orWhere('model', 'like', "%{$q}%")
                    ->orWhere('reference', 'like', "%{$q}%");
            }))
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return response()->json($vehicles->map(function (V3Vehicle $v) {
            $capa = $v->photos->sortBy([['is_cover', 'desc'], ['order_position', 'asc']])->first();

            return [
                'id' => $v->id,
                'reference' => $v->reference,
                'label' => trim("{$v->brand} {$v->model} {$v->version}") . ($v->year ? " ({$v->year})" : ''),
                'status' => $v->status,
                'cover_url' => $capa ? route('admin.marketing.photos.show', $capa) : null,
            ];
        })->values());
    }

    public function store(MarketingPostRequest $request)
    {
        $vehicle = V3Vehicle::findOrFail($request->integer('vehicle_id'));
        $data = VehiclePostData::fromVehicle($vehicle);

        $template = $this->resolveTemplate($request->validated('template'), MarketingPostType::Veiculo, MarketingFormat::from($request->validated('format')));
        try {
            VehiclePostData::assertTemplateAllowed($data, $template->key);
        } catch (\DomainException $e) {
            throw ValidationException::withMessages(['template' => $e->getMessage()]);
        }

        $post = MarketingPost::create([
            'type' => MarketingPostType::Veiculo,
            'template' => $template->key,
            'format' => $request->validated('format'),
            'status' => MarketingPostStatus::Rascunho,
            'title' => Str::limit(trim("{$vehicle->brand} {$vehicle->model}"), 255, ''),
            'source_type' => V3Vehicle::class,
            'source_id' => $vehicle->id,
            'data' => $data,
            'config' => [],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $url = route('admin.marketing.editor', $post);

        return $request->expectsJson()
            ? response()->json(['redirect' => $url], 201)
            : redirect($url);
    }

    public function editor(MarketingPost $post)
    {
        $config = $post->config ?? [];
        $preview = PostRenderer::render($post);

        return view('admin.marketing.editor', [
            'post' => $post,
            'config' => $config,
            'defaults' => PostContent::defaults($post),
            'overrides' => $config['overrides'] ?? [],
            'priceDiffers' => PostContent::priceDiffersFromSource($post, $config),
            'templates' => TemplateRegistry::forType($post->type),
            'formats' => MarketingFormat::cases(),
            'preview' => $preview,
            'kind' => PostContent::kind($post->type),
            'sourceLost' => $this->sourceLostReason($post),
            'aiEnabled' => filled(config('services.openai.key')),
            'legalCheck' => (bool) ($post->data['legal_check'] ?? false),
            'topicHint' => collect(config('marketing.topics'))->firstWhere('key', $post->data['topic'] ?? null)['hint'] ?? null,
            'photoCount' => count($post->data['photos'] ?? []),
        ]);
    }

    /** HTML dos slides para o estado atual do editor (mesmo HTML que a exportação rasteriza). */
    public function preview(MarketingPostUpdateRequest $request, MarketingPost $post)
    {
        $state = $request->validated();
        $this->assertAllowed($post, $state['template'] ?? $post->template, $state['format'] ?? $post->format->value);
        $this->assertQuote($post, $state['overrides'] ?? []);

        return response()->json(PostRenderer::render($post, $state));
    }

    public function update(MarketingPostUpdateRequest $request, MarketingPost $post)
    {
        $request->validate(['template' => 'required', 'format' => 'required']);
        $v = $request->validated();
        $this->assertAllowed($post, $v['template'], $v['format']);
        $this->assertQuote($post, $v['overrides'] ?? []);

        $config = array_replace($post->config ?? [], array_intersect_key($v, array_flip([
            'photo_order', 'focal', 'gallery_layouts', 'gallery_photos_per_slide',
        ])));
        if (array_key_exists('overrides', $v)) {
            $config['overrides'] = PostContent::normalizeOverrides($post, $v['overrides'] ?? []);
        }

        $status = MarketingPostStatus::tryFrom($v['status'] ?? '') ?? $post->status;
        $when = array_key_exists('scheduled_for', $v) ? ($v['scheduled_for'] ? \Illuminate\Support\Carbon::parse($v['scheduled_for']) : null) : $post->scheduled_for;
        // Só um post aprovado passa a "agendado" (e volta a aprovado se perder a data). Nunca a "publicado".
        if ($when && $status === MarketingPostStatus::Aprovado) {
            $status = MarketingPostStatus::Agendado;
        } elseif (! $when && $status === MarketingPostStatus::Agendado) {
            $status = MarketingPostStatus::Aprovado;
        }

        if (! empty($post->data['pending_selection']) && $status !== MarketingPostStatus::Rascunho) {
            throw ValidationException::withMessages(['status' => 'Escolhe primeiro a viatura/conteúdo real: um marcador "por selecionar" não pode ser aprovado nem agendado.']);
        }

        $post->update([
            'scheduled_for' => $when,
            'channel' => array_key_exists('channel', $v) ? $v['channel'] : $post->channel,
            'template' => $v['template'],
            'format' => $v['format'],
            'title' => array_key_exists('title', $v) ? $v['title'] : $post->title,
            'caption' => array_key_exists('caption', $v) ? $v['caption'] : $post->caption,
            'hashtags' => array_key_exists('hashtags', $v) ? $v['hashtags'] : $post->hashtags,
            'cta' => array_key_exists('cta', $v) ? $v['cta'] : $post->cta,
            'status' => $status,
            'config' => $config,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'ok' => true,
            'status' => $status->value,
            'price_differs' => PostContent::priceDiffersFromSource($post, $config),
        ]);
    }

    /** Substitui o instantâneo pelos dados atuais da fonte. Só por ação explícita. Preserva título, legenda e config. */
    public function refresh(MarketingPost $post)
    {
        if ($post->source_type === Testimonial::class) {
            return $this->refreshTestimonial($post);
        }
        if ($post->type === MarketingPostType::Importacao) {
            return back()->with('error', 'Os posts de importação não se atualizam a partir da viatura: não guardam ligação ao cliente. Edita no editor.');
        }
        $vehicle = $post->source_type === V3Vehicle::class ? V3Vehicle::find($post->source_id) : null;
        if (! $vehicle) {
            return back()->with('error', 'A fonte deste post já não existe, por isso não há dados para atualizar.');
        }

        $novo = VehiclePostData::fromVehicle($vehicle);
        try {
            VehiclePostData::assertTemplateAllowed($novo, $post->template);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage() . ' Muda de template antes de atualizar os dados.');
        }

        // A ordem e os pontos focais guardam posições: remapeia-os pelo id da foto.
        $config = $post->config ?? [];
        $antigos = array_column($post->data['photos'] ?? [], 'id');
        $novosIds = array_column($novo['photos'], 'id');
        $remap = [];
        foreach ($antigos as $i => $id) {
            $j = array_search($id, $novosIds, true);
            if ($j !== false) {
                $remap[$i] = $j;
            }
        }
        if (isset($config['photo_order'])) {
            $config['photo_order'] = array_values(array_filter(array_map(fn ($i) => $remap[(int) $i] ?? null, $config['photo_order']), fn ($j) => $j !== null));
        }
        if (isset($config['focal'])) {
            $focal = [];
            foreach ($config['focal'] as $i => $xy) {
                if (isset($remap[(int) $i])) {
                    $focal[$remap[(int) $i]] = $xy;
                }
            }
            $config['focal'] = $focal;
        }

        $post->update(['data' => $novo, 'config' => $config, 'updated_by' => auth()->id()]);

        return back()->with('success', 'Dados atualizados a partir da viatura.');
    }

    private function refreshTestimonial(MarketingPost $post)
    {
        $t = Testimonial::find($post->source_id);
        if (! $t) {
            return back()->with('error', 'O testemunho de origem já não existe, por isso não há dados para atualizar.');
        }
        try {
            $novo = TestimonialPostData::fromTestimonial($t);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage() . ' Os dados do post não foram alterados.');
        }

        $config = $post->config ?? [];
        // O excerto escolhido tem de continuar a existir no comentário atual.
        if (isset($config['overrides']['quote']) && ! TestimonialPostData::isExcerpt($novo['comment'], $config['overrides']['quote'])) {
            unset($config['overrides']['quote']);
        }
        $post->update(['data' => $novo, 'config' => $config, 'updated_by' => auth()->id()]);

        return back()->with('success', 'Dados atualizados a partir do testemunho.');
    }

    /** Testemunho cuja autorização foi retirada (ou que foi apagado): null se está tudo bem. */
    private function sourceLostReason(MarketingPost $post): ?string
    {
        if ($post->data['pending_selection'] ?? false) {
            return 'Este rascunho do plano ainda não tem conteúdo real: cria a publicação (viatura, testemunho, importação…) e elimina este marcador.';
        }
        if ($post->source_type !== Testimonial::class) {
            return null;
        }
        $t = Testimonial::find($post->source_id);
        if (! $t || ! $t->marketing_consent) {
            return 'O testemunho de origem já não tem autorização de uso (ou foi apagado).';
        }

        return null;
    }

    /** Num post de testemunho, o texto na imagem tem de ser um excerto contínuo do comentário original. */
    private function assertQuote(MarketingPost $post, array $overrides): void
    {
        if ($post->type !== MarketingPostType::Testemunho) {
            return;
        }
        $q = trim((string) ($overrides['quote'] ?? ''));
        if ($q !== '' && ! TestimonialPostData::isExcerpt((string) ($post->data['comment'] ?? ''), $q)) {
            throw ValidationException::withMessages(['overrides.quote' => 'O texto tem de ser um excerto exato do comentário do cliente.']);
        }
    }

    public function duplicate(MarketingPost $post)
    {
        if ($erro = $this->sourceLostReason($post)) {
            return back()->with('error', $erro . ' Não é possível duplicar.');
        }
        $copia = $post->replicate(['published_at', 'scheduled_for']);
        $copia->title = Str::limit(trim(($post->title ?: 'Publicação') . ' (cópia)'), 255, '');
        $copia->status = MarketingPostStatus::Rascunho;
        // A cópia não faz parte do plano de 30 dias do original.
        $copia->data = array_diff_key($post->data ?? [], array_flip(['plan_start', 'plan_day']));
        $copia->created_by = auth()->id();
        $copia->updated_by = auth()->id();
        $copia->save();

        foreach ($post->slides as $slide) {
            $copia->slides()->create($slide->only(['position', 'layout', 'content']));
        }

        return redirect()->route('admin.marketing.editor', $copia)->with('success', 'Publicação duplicada.');
    }

    /** "Publicado" só por confirmação manual de que o post foi mesmo publicado. */
    public function markPublished(MarketingPost $post)
    {
        if (! empty($post->data['pending_selection'])) {
            return back()->with('error', 'Um marcador "por selecionar" não pode ser marcado como publicado.');
        }
        $post->update([
            'status' => MarketingPostStatus::Publicado,
            'published_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Publicação marcada como publicada.');
    }

    public function destroy(MarketingPost $post)
    {
        // Fotos carregadas (recomendações, importações) vivem em marketing-posts/{id}/; as das viaturas não são tocadas.
        $post->delete();
        app(\App\Services\MarketingPhotoStore::class)->discard($post);

        return redirect()->route('admin.marketing.index')->with('success', 'Publicação eliminada.');
    }

    private function resolveTemplate(string $key, MarketingPostType $type, MarketingFormat $format)
    {
        return TemplateRegistry::resolve($key, $type, $format);
    }

    /** Template válido para o tipo, formato suportado e regras de estado da viatura (usa o instantâneo). */
    private function assertAllowed(MarketingPost $post, string $templateKey, string $format): void
    {
        $this->resolveTemplate($templateKey, $post->type, MarketingFormat::from($format));
        if ($post->type === MarketingPostType::Veiculo) {
            try {
                VehiclePostData::assertTemplateAllowed($post->data ?? [], $templateKey);
            } catch (\DomainException $e) {
                throw ValidationException::withMessages(['template' => $e->getMessage()]);
            }
        }
    }
}
