<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecommendationPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Ferramentas de criação de conteúdo para redes sociais.
 *
 * A geração das imagens (slides) acontece inteiramente no browser (canvas via
 * html2canvas) — o servidor só guarda os dados introduzidos (e a foto do
 * carro), para que cada post criado fique disponível para consulta,
 * reedição ou novo download mais tarde.
 */
class SocialPostController extends Controller
{
    public function index()
    {
        $posts = RecommendationPost::orderBy('created_at', 'desc')->paginate(15);

        return view('admin.v2.social-posts.index', compact('posts'));
    }

    public function recommendation(?RecommendationPost $post = null)
    {
        return view('admin.v2.social-posts.recommendation', ['post' => $post]);
    }

    public function edit(RecommendationPost $post)
    {
        return view('admin.v2.social-posts.recommendation', compact('post'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|integer|exists:recommendation_posts,id',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'mileage' => 'nullable|integer|min:0',
            'power' => 'nullable|integer|min:0',
            'fuel' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1990|max:'.(date('Y') + 1),
            'equipment_raw' => 'nullable|string|max:2000',
            'price' => 'nullable|numeric|min:0',
            'savings' => 'nullable|numeric|min:0',
            'url' => 'nullable|url|max:1000',
            'image' => 'nullable|image|max:8192',
        ]);

        $validated['equipment'] = collect(explode("\n", $validated['equipment_raw'] ?? ''))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->take(4)
            ->values()
            ->all();
        unset($validated['equipment_raw'], $validated['id']);

        $post = RecommendationPost::findOrNew($request->input('id'));

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $validated['image'] = $request->file('image')->store('recommendation-posts', 'public');
        } else {
            unset($validated['image']);
        }

        $post->fill($validated);
        $post->save();

        return redirect()->route('admin.v2.social-posts.index')
            ->with('success', $post->wasRecentlyCreated ? 'Post guardado com sucesso!' : 'Post atualizado com sucesso!');
    }

    public function destroy(RecommendationPost $post)
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete();

        return redirect()->route('admin.v2.social-posts.index')
            ->with('success', 'Post eliminado com sucesso!');
    }
}
