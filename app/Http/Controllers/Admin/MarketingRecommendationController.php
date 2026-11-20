<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MarketingFormat;
use App\Enums\MarketingPostStatus;
use App\Enums\MarketingPostType;
use App\Http\Controllers\Controller;
use App\Marketing\RecommendationPostData;
use App\Marketing\TemplateRegistry;
use App\Models\MarketingPost;
use App\Services\MarketingPhotoStore;
use App\Services\MarketingRecommendationImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Posts de recomendação: carro de fora com entrada manual (substitui o antigo Criador de Posts). */
class MarketingRecommendationController extends Controller
{
    public function create()
    {
        return view('admin.marketing.recommendation-create', [
            'templates' => TemplateRegistry::forType(MarketingPostType::Recomendacao),
            'formats' => MarketingFormat::cases(),
        ]);
    }

    public function store(Request $request, MarketingPhotoStore $photos)
    {
        $v = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'mileage' => 'nullable|integer|min:0',
            'power' => 'nullable|integer|min:0',
            'fuel' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'equipment_raw' => 'nullable|string|max:2000',
            'price' => 'nullable|numeric|min:0',
            'savings' => 'nullable|numeric|min:0',
            'url' => 'nullable|url|max:1000',
            'image' => 'nullable|image|max:8192',
            'gallery_photos' => 'nullable|array|max:12',
            'gallery_photos.*' => 'image|max:8192',
            'template' => ['required', Rule::in(array_keys(TemplateRegistry::forType(MarketingPostType::Recomendacao)))],
            'format' => ['required', Rule::in(MarketingFormat::values())],
        ]);

        $template = TemplateRegistry::get($v['template']);
        if (! $template->supports(MarketingFormat::from($v['format']))) {
            return back()->withInput()->withErrors(['format' => 'Este template não suporta o formato escolhido.']);
        }

        $post = MarketingPost::create([
            'type' => MarketingPostType::Recomendacao,
            'template' => $template->key,
            'format' => $v['format'],
            'status' => MarketingPostStatus::Rascunho,
            'title' => Str::limit(trim("{$v['brand']} {$v['model']}"), 255, ''),
            'data' => [],
            'config' => [],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        // Se guardar as fotos falhar, não fica um post vazio nem ficheiros órfãos.
        try {
            $paths = $photos->store($post, array_merge([$request->file('image')], $request->file('gallery_photos', [])));
            $post->update(['data' => RecommendationPostData::fromInput($v, $paths)]);
        } catch (\Throwable $e) {
            $photos->discard($post);
            $post->delete();
            throw $e;
        }

        return redirect()->route('admin.marketing.editor', $post);
    }

    public function import(MarketingRecommendationImporter $importer)
    {
        $r = $importer->import(auth()->id());

        return redirect()->route('admin.marketing.index')
            ->with('success', "Importadas {$r['imported']} recomendações (já existentes: {$r['skipped']}). Os posts originais não foram alterados.");
    }
}
