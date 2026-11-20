<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MarketingFormat;
use App\Enums\MarketingPostStatus;
use App\Enums\MarketingPostType;
use App\Http\Controllers\Controller;
use App\Http\Requests\MarketingTextPostRequest;
use App\Marketing\ImportPostData;
use App\Marketing\TemplateRegistry;
use App\Models\V3Vehicle;
use App\Services\MarketingPhotoStore;
use App\Marketing\TestimonialPostData;
use App\Models\Testimonial;
use Illuminate\Validation\ValidationException;
use App\Marketing\TextPostData;
use App\Models\MarketingPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Criação de publicações sem viatura de origem: educativo, serviço, importação e testemunho. */
class MarketingContentController extends Controller
{
    public function create(string $type)
    {
        $enum = MarketingPostType::from($type);

        return match ($enum) {
            MarketingPostType::Educativo, MarketingPostType::Servico => $this->createText($enum),
            MarketingPostType::Testemunho => $this->createTestimonial(),
            MarketingPostType::Importacao => $this->createImport(),
            default => abort(404),
        };
    }

    public function store(Request $request, string $type)
    {
        $enum = MarketingPostType::from($type);

        return match ($enum) {
            MarketingPostType::Educativo, MarketingPostType::Servico => $this->storeText(app(MarketingTextPostRequest::class), $enum),
            MarketingPostType::Testemunho => $this->storeTestimonial($request),
            MarketingPostType::Importacao => $this->storeImport($request, app(MarketingPhotoStore::class)),
            default => abort(404),
        };
    }

    /** Pesquisa (JSON) só de testemunhos com autorização de uso. */
    public function searchTestimonials(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        return response()->json(
            Testimonial::where('marketing_consent', true)
                ->whereNotNull('comment')->where('comment', '!=', '')
                ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%{$q}%")->orWhere('comment', 'like', "%{$q}%")))
                ->latest('id')->limit(20)->get()
                ->map(fn (Testimonial $t) => [
                    'id' => $t->id,
                    'label' => (preg_split('/\s+/u', trim($t->name))[0] ?? '') . ' · ' . (Testimonial::ORIGINS[$t->origin] ?? $t->origin) . ' · ' . $t->rating . '★',
                    'excerpt' => \Illuminate\Support\Str::limit($t->comment, 140),
                ])->values()
        );
    }

    private function createImport()
    {
        return view('admin.marketing.import-create', [
            'templates' => collect(TemplateRegistry::forType(MarketingPostType::Importacao)),
            'formats' => MarketingFormat::cases(),
        ]);
    }

    private function storeImport(Request $request, MarketingPhotoStore $photos)
    {
        $v = $request->validate([
            'authorized' => ['accepted'],
            'vehicle_id' => ['nullable', 'integer', 'exists:v3_vehicles,id'],
            'brand' => ['nullable', 'string', 'max:100', 'required_without:vehicle_id'],
            'model' => ['nullable', 'string', 'max:100', 'required_without:vehicle_id'],
            'version' => ['nullable', 'string', 'max:150'],
            'year' => ['nullable', 'integer', 'min:1990', 'max:' . (date('Y') + 1)],
            'origin_country' => ['nullable', 'string', 'max:80'],
            'highlights' => ['nullable', 'string', 'max:1000'],
            'savings' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'photos' => ['nullable', 'array', 'max:12'],
            'photos.*' => ['image', 'max:8192'],
            'template' => ['required', 'string'],
            'format' => ['required', \Illuminate\Validation\Rule::in(MarketingFormat::values())],
        ], [
            'authorized.accepted' => 'Confirma que a importação está concluída e que o cliente autorizou a divulgação.',
            'brand.required_without' => 'Escolhe uma viatura importada ou indica a marca.',
            'model.required_without' => 'Escolhe uma viatura importada ou indica o modelo.',
        ]);

        $format = MarketingFormat::from($v['format']);
        $template = TemplateRegistry::resolve($v['template'], MarketingPostType::Importacao, $format);

        $vehicle = null;
        if (! empty($v['vehicle_id'])) {
            $vehicle = V3Vehicle::findOrFail($v['vehicle_id']);
            if (! $vehicle->is_imported) {
                throw ValidationException::withMessages(['vehicle_id' => 'Esta viatura não é uma importação.']);
            }
        }
        $label = $vehicle ? trim("{$vehicle->brand} {$vehicle->model}") : trim("{$v['brand']} {$v['model']}");

        // Sem fonte nem ligação ao cliente: o instantâneo é só o que se pode divulgar.
        $post = MarketingPost::create([
            'type' => MarketingPostType::Importacao,
            'template' => $template->key,
            'format' => $format,
            'status' => MarketingPostStatus::Rascunho,
            'title' => Str::limit($label, 255, ''),
            'source_type' => null,
            'source_id' => null,
            'data' => [],
            'config' => [],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        try {
            $data = $vehicle
                ? ImportPostData::fromVehicle($vehicle, $v)
                : ImportPostData::fromManual($v, $photos->store($post, $request->file('photos', [])));
            $post->update(['data' => $data]);
        } catch (\Throwable $e) {
            $photos->discard($post);
            $post->delete();
            throw $e;
        }

        $url = route('admin.marketing.editor', $post);

        return $request->expectsJson() ? response()->json(['redirect' => $url], 201) : redirect($url);
    }

    private function createTestimonial()
    {
        return view('admin.marketing.testimonial-create', [
            'templates' => collect(TemplateRegistry::forType(MarketingPostType::Testemunho)),
            'formats' => MarketingFormat::cases(),
        ]);
    }

    private function storeTestimonial(Request $request)
    {
        $v = $request->validate([
            'testimonial_id' => ['required', 'integer', 'exists:testimonials,id'],
            'template' => ['required', 'string'],
            'format' => ['required', \Illuminate\Validation\Rule::in(MarketingFormat::values())],
        ], ['testimonial_id.required' => 'Escolhe um testemunho.', 'testimonial_id.exists' => 'O testemunho escolhido não existe.']);

        $format = MarketingFormat::from($v['format']);
        $template = TemplateRegistry::resolve($v['template'], MarketingPostType::Testemunho, $format);
        $t = Testimonial::findOrFail($v['testimonial_id']);
        try {
            $data = TestimonialPostData::fromTestimonial($t);
        } catch (\DomainException $e) {
            throw ValidationException::withMessages(['testimonial_id' => $e->getMessage()]);
        }

        $post = MarketingPost::create([
            'type' => MarketingPostType::Testemunho,
            'template' => $template->key,
            'format' => $format,
            'status' => MarketingPostStatus::Rascunho,
            'title' => Str::limit('Testemunho — ' . $data['first_name'], 255, ''),
            'source_type' => Testimonial::class,
            'source_id' => $t->id,
            'data' => $data,
            'config' => [],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $url = route('admin.marketing.editor', $post);

        return $request->expectsJson() ? response()->json(['redirect' => $url], 201) : redirect($url);
    }

    private function createText(MarketingPostType $type)
    {
        return view('admin.marketing.content-create', [
            'type' => $type,
            'topics' => collect(config('marketing.topics'))->where('type', $type->value)->values(),
            'templates' => collect(TemplateRegistry::forType($type)),
            'formats' => MarketingFormat::cases(),
        ]);
    }

    private function storeText(MarketingTextPostRequest $request, MarketingPostType $type)
    {
        $v = $request->validated();
        $format = MarketingFormat::from($v['format']);
        $template = TemplateRegistry::resolve($v['template'], $type, $format);
        $topic = isset($v['topic']) ? collect(config('marketing.topics'))->firstWhere('key', $v['topic']) : null;

        $title = trim((string) ($v['title'] ?? '')) ?: $topic['title'];
        $data = TextPostData::fromInput([
            'title' => $title,
            'topic' => $topic['key'] ?? null,
            'legal_check' => $topic['legal_check'] ?? false,
        ]);

        $post = MarketingPost::create([
            'type' => $type,
            'template' => $template->key,
            'format' => $format,
            'status' => MarketingPostStatus::Rascunho,
            'title' => Str::limit($title, 255, ''),
            'source_type' => null,
            'source_id' => null,
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
}
