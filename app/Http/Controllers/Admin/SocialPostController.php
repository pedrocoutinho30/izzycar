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
            'gallery_photos' => 'nullable|array|max:12',
            'gallery_photos.*' => 'nullable|image|max:8192',
            'gallery_layouts' => 'nullable|string|max:2000',
            'gallery_photos_per_slide' => 'nullable|integer|in:1,3',
            'gallery_order' => 'nullable|string|max:4000',
            'remove_gallery_photos' => 'nullable|string|max:2000',
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

        // Galeria de fotos ("mais fotos do carro"): o cliente envia a ordem
        // final desejada em "gallery_order" (tokens "existing:<path>" ou
        // "new:<índice>", este último referente à posição do ficheiro em
        // gallery_photos[]) — isto permite reordenar livremente fotos já
        // guardadas misturadas com fotos novas, sem perder a ordem escolhida.
        $toRemove = array_filter(json_decode($request->input('remove_gallery_photos', '[]'), true) ?: []);
        foreach ($toRemove as $path) {
            Storage::disk('public')->delete($path);
        }

        $storedNewPaths = [];
        if ($request->hasFile('gallery_photos')) {
            foreach ($request->file('gallery_photos') as $idx => $photo) {
                if ($photo && $photo->isValid()) {
                    $storedNewPaths[$idx] = $photo->store('recommendation-posts/gallery', 'public');
                }
            }
        }

        $order = json_decode($request->input('gallery_order', '[]'), true) ?: [];
        $galleryPhotos = [];
        foreach ($order as $token) {
            if (str_starts_with($token, 'existing:')) {
                $path = substr($token, 9);
                if (!in_array($path, $toRemove, true)) {
                    $galleryPhotos[] = $path;
                }
            } elseif (str_starts_with($token, 'new:')) {
                $idx = (int) substr($token, 4);
                if (isset($storedNewPaths[$idx])) {
                    $galleryPhotos[] = $storedNewPaths[$idx];
                }
            }
        }

        $validated['gallery_photos'] = array_slice($galleryPhotos, 0, 12);
        $validated['gallery_layouts'] = json_decode($request->input('gallery_layouts', '{}'), true) ?: [];
        $validated['gallery_photos_per_slide'] = $request->input('gallery_photos_per_slide', 3);
        unset($validated['remove_gallery_photos'], $validated['gallery_order']);

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
        foreach ($post->gallery_photos ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }
        $post->delete();

        return redirect()->route('admin.v2.social-posts.index')
            ->with('success', 'Post eliminado com sucesso!');
    }
}
