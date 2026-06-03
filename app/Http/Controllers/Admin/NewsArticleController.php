<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsArticleController extends Controller
{
    /* ── INDEX ── */
    public function index(Request $request)
    {
        $q = NewsArticle::orderByDesc('published_at');

        if ($request->filled('search')) {
            $q->where('title', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        $articles  = $q->paginate(15)->withQueryString();
        $total     = NewsArticle::count();
        $published = NewsArticle::published()->count();
        $drafts    = $total - $published;

        return view('admin.news.index', compact('articles', 'total', 'published', 'drafts'));
    }

    /* ── CREATE ── */
    public function create()
    {
        return view('admin.news.form');
    }

    /* ── STORE ── */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug']        = NewsArticle::generateSlug($request->title);
        $data['cover_image'] = $this->storeCover($request);
        $data['gallery']     = $this->storeGallery($request, []);

        $article = NewsArticle::create($data);

        return redirect()->route('admin.news.edit', $article->id)
            ->with('success', 'Artigo criado com sucesso!');
    }

    /* ── EDIT ── */
    public function edit($id)
    {
        $article = NewsArticle::findOrFail($id);
        return view('admin.news.form', compact('article'));
    }

    /* ── UPDATE ── */
    public function update(Request $request, $id)
    {
        $article = NewsArticle::findOrFail($id);
        $data    = $this->validated($request, $article->id);

        if ($request->filled('slug') && $request->slug !== $article->slug) {
            $data['slug'] = NewsArticle::generateSlug($request->slug, $article->id);
        }

        unset($data['cover_image'], $data['gallery']);

        if ($request->hasFile('cover_image')) {
            if ($article->cover_image) Storage::disk('public')->delete($article->cover_image);
            $data['cover_image'] = $this->storeCover($request);
        } elseif ($request->input('remove_cover') === '1' && $article->cover_image) {
            Storage::disk('public')->delete($article->cover_image);
            $data['cover_image'] = null;
        }

        $data['gallery'] = $this->storeGallery($request, $article->gallery ?? []);

        $article->update($data);

        return redirect()->route('admin.news.edit', $article->id)
            ->with('success', 'Artigo guardado!');
    }

    /* ── DESTROY ── */
    public function destroy($id)
    {
        $article = NewsArticle::findOrFail($id);

        if ($article->cover_image) {
            Storage::disk('public')->delete($article->cover_image);
        }
        foreach ($article->gallery ?? [] as $img) {
            Storage::disk('public')->delete($img);
        }

        $article->delete();

        return redirect()->route('admin.news.index')
            ->with('success', 'Artigo eliminado.');
    }

    /* ── TOGGLE STATUS (AJAX) ── */
    public function toggleStatus($id)
    {
        $article = NewsArticle::findOrFail($id);
        $new     = $article->status === 'Publicado' ? 'Rascunho' : 'Publicado';
        $article->update(['status' => $new]);
        return response()->json(['status' => $new]);
    }

    /* ── DELETE GALLERY IMAGE (AJAX) ── */
    public function deleteGalleryImage(Request $request, $id)
    {
        $article = NewsArticle::findOrFail($id);
        $path    = $request->path;

        $gallery = array_values(array_filter($article->gallery ?? [], fn($g) => $g !== $path));
        Storage::disk('public')->delete($path);
        $article->update(['gallery' => $gallery]);

        return response()->json(['ok' => true]);
    }

    /* ── PRIVATE HELPERS ── */
    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'           => 'required|string|max:255',
            'subtitle'        => 'nullable|string',
            'content'         => 'required|string|min:10',
            'summary'         => 'nullable|string',
            'status'          => 'required|in:Publicado,Rascunho',
            'published_at'    => 'nullable|date',
            'seo_title'       => 'nullable|string|max:100',
            'seo_description' => 'nullable|string|max:300',
            'seo_keywords'    => 'nullable|string|max:255',
            'cover_image'     => 'nullable|image|max:5120',
            'gallery.*'       => 'nullable|image|max:5120',
        ]);
    }

    private function storeCover(Request $request): ?string
    {
        if (!$request->hasFile('cover_image')) return null;
        return $request->file('cover_image')->store('news/covers', 'public');
    }

    private function storeGallery(Request $request, array $existing): array
    {
        if (!$request->hasFile('gallery')) return $existing;
        foreach ($request->file('gallery') as $file) {
            $existing[] = $file->store('news/gallery', 'public');
        }
        return $existing;
    }
}
