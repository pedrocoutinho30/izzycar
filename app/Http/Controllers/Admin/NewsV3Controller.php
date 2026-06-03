<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageContent;
use App\Models\PageType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NewsV3Controller extends Controller
{
    private function newsPageType(): PageType
    {
        return PageType::where('name', 'Notícias')->firstOrFail();
    }

    /* ─────────────── INDEX ─────────────── */
    public function index(Request $request)
    {
        $pt    = $this->newsPageType();
        $query = Page::where('page_type_id', $pt->id)->with('contents');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('contents', fn($s) =>
                $s->where('field_name', 'title')->where('field_value', 'like', "%{$q}%")
            );
        }

        if ($request->filled('status')) {
            $st = $request->status;
            $query->whereHas('contents', fn($s) =>
                $s->where('field_name', 'status')->where('field_value', $st)
            );
        }

        $articles = $query
            ->leftJoin('page_contents as d', fn($j) =>
                $j->on('d.page_id', '=', 'pages.id')->where('d.field_name', '=', 'date')
            )
            ->orderByRaw('COALESCE(d.field_value, pages.created_at) DESC')
            ->select('pages.*')
            ->paginate(15)->withQueryString();

        $total     = Page::where('page_type_id', $pt->id)->count();
        $published = Page::where('page_type_id', $pt->id)
            ->whereHas('contents', fn($s) => $s->where('field_name', 'status')->where('field_value', 'Publicado'))
            ->count();

        return view('admin.v3.news.index', compact('articles', 'total', 'published'));
    }

    /* ─────────────── CREATE ─────────────── */
    public function create()
    {
        return view('admin.v3.news.form');
    }

    /* ─────────────── STORE ─────────────── */
    public function store(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:255',
            'slug'    => 'required|string|max:255|unique:pages,slug',
            'status'  => 'required|in:Publicado,Rascunho',
            'content' => 'required|string|min:10',
            'image'   => 'nullable|image|max:5120',
        ]);

        $page = Page::create([
            'page_type_id' => $this->newsPageType()->id,
            'title'        => Str::slug($request->title),
            'slug'         => $request->slug,
            'is_active'    => true,
        ]);

        $this->syncContents($page, $request);
        $this->syncSeo($page, $request);

        return redirect()->route('admin.v3.news.edit', $page->id)
            ->with('success', 'Artigo criado com sucesso!');
    }

    /* ─────────────── EDIT ─────────────── */
    public function edit($id)
    {
        $page     = Page::with('contents', 'seo')->findOrFail($id);
        $contents = $page->contents->pluck('field_value', 'field_name');
        return view('admin.v3.news.form', compact('page', 'contents'));
    }

    /* ─────────────── UPDATE ─────────────── */
    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $request->validate([
            'title'   => 'required|string|max:255',
            'slug'    => 'required|string|max:255|unique:pages,slug,' . $page->id,
            'status'  => 'required|in:Publicado,Rascunho',
            'content' => 'required|string|min:10',
            'image'   => 'nullable|image|max:5120',
        ]);

        $page->update(['slug' => $request->slug]);
        $this->syncContents($page, $request);
        $this->syncSeo($page, $request);

        return redirect()->route('admin.v3.news.edit', $page->id)
            ->with('success', 'Artigo guardado!');
    }

    /* ─────────────── DESTROY ─────────────── */
    public function destroy($id)
    {
        $page = Page::with('contents')->findOrFail($id);

        // Remove images from storage
        $img = $page->contents->firstWhere('field_name', 'image')?->field_value;
        if ($img) Storage::disk('public')->delete($img);

        $gallery = json_decode($page->contents->firstWhere('field_name', 'gallery')?->field_value ?? '[]', true);
        foreach ($gallery as $g) Storage::disk('public')->delete($g);

        $page->contents()->delete();
        $page->seo?->delete();
        $page->delete();

        return redirect()->route('admin.v3.news.index')->with('success', 'Artigo eliminado.');
    }

    /* ─────────────── QUICK STATUS (AJAX) ─────────────── */
    public function updateStatus(Request $request, $id)
    {
        $page      = Page::findOrFail($id);
        $newStatus = $request->status === 'Publicado' ? 'Publicado' : 'Rascunho';

        PageContent::updateOrCreate(
            ['page_id' => $page->id, 'field_name' => 'status'],
            ['field_value' => $newStatus]
        );

        return response()->json(['status' => $newStatus]);
    }

    /* ─────────────── GALLERY DELETE (AJAX) ─────────────── */
    public function deleteGalleryImage(Request $request, $id)
    {
        $page = Page::findOrFail($id);
        $path = $request->path;

        $content = PageContent::where('page_id', $page->id)->where('field_name', 'gallery')->first();
        if (!$content) return response()->json(['ok' => false]);

        $gallery = json_decode($content->field_value ?? '[]', true);
        $gallery = array_values(array_filter($gallery, fn($g) => $g !== $path));
        Storage::disk('public')->delete($path);
        $content->update(['field_value' => json_encode($gallery)]);

        return response()->json(['ok' => true]);
    }

    /* ─────────────── HELPERS ─────────────── */
    private function syncContents(Page $page, Request $request): void
    {
        $text = ['title', 'subtitle', 'content', 'summary', 'status', 'date'];
        foreach ($text as $f) {
            PageContent::updateOrCreate(
                ['page_id' => $page->id, 'field_name' => $f],
                ['field_value' => $request->input($f, '')]
            );
        }

        // Cover image
        if ($request->hasFile('image')) {
            $old = PageContent::where('page_id', $page->id)->where('field_name', 'image')->first();
            if ($old?->field_value) Storage::disk('public')->delete($old->field_value);

            $path = $request->file('image')->store('news', 'public');
            PageContent::updateOrCreate(
                ['page_id' => $page->id, 'field_name' => 'image'],
                ['field_value' => $path]
            );
        }

        // Gallery (append new images to existing)
        if ($request->hasFile('gallery')) {
            $content = PageContent::firstOrNew(['page_id' => $page->id, 'field_name' => 'gallery']);
            $existing = json_decode($content->field_value ?? '[]', true);

            foreach ($request->file('gallery') as $file) {
                $existing[] = $file->store('news/gallery', 'public');
            }

            $content->field_value = json_encode(array_values($existing));
            $content->page_id     = $page->id;
            $content->field_name  = 'gallery';
            $content->save();
        }
    }

    private function syncSeo(Page $page, Request $request): void
    {
        $page->updateSeo([
            'title'            => $request->input('seo_title', $request->input('title', '')),
            'meta_description' => $request->input('seo_description', ''),
            'meta_keywords'    => $request->input('seo_keywords', ''),
            'og_title'         => $request->input('seo_title', $request->input('title', '')),
            'og_description'   => $request->input('seo_description', ''),
            'og_type'          => 'article',
            'canonical_url'    => 'https://izzycar.pt/noticias/' . $page->slug,
        ]);
    }
}
