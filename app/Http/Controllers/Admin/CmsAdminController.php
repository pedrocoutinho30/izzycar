<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsBlock;
use App\Models\CmsPage;
use Illuminate\Http\Request;

class CmsAdminController extends Controller
{
    // ─── Pages ───────────────────────────────────────────

    public function index()
    {
        $pages = CmsPage::withCount('blocks')->orderBy('order')->get();
        return view('admin.v2.cms.index', compact('pages'));
    }

    public function createPage()
    {
        return view('admin.v2.cms.page-form', ['page' => null]);
    }

    public function storePage(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'slug'   => 'required|string|max:100|unique:cms_pages,slug',
            'active' => 'boolean',
            'order'  => 'integer|min:0',
        ]);
        $page = CmsPage::create($data + ['active' => $request->boolean('active')]);
        return redirect()->route('admin.v2.cms.page', $page->id)->with('success', 'Página criada.');
    }

    public function page(CmsPage $page)
    {
        $blocks = $page->blocks()->get();
        return view('admin.v2.cms.page', compact('page', 'blocks'));
    }

    public function updatePage(Request $request, CmsPage $page)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'slug'   => 'required|string|max:100|unique:cms_pages,slug,' . $page->id,
            'active' => 'boolean',
            'order'  => 'integer|min:0',
        ]);
        $page->update($data + ['active' => $request->boolean('active')]);
        return back()->with('success', 'Página guardada.');
    }

    public function destroyPage(CmsPage $page)
    {
        $page->delete();
        return redirect()->route('admin.v2.cms.index')->with('success', 'Página eliminada.');
    }

    // ─── Blocks ──────────────────────────────────────────

    public function createBlock(CmsPage $page)
    {
        return view('admin.v2.cms.block-form', [
            'page'  => $page,
            'block' => null,
            'types' => CmsBlock::TYPES,
            'dataSchema' => CmsBlock::DATA_SCHEMA,
        ]);
    }

    public function storeBlock(Request $request, CmsPage $page)
    {
        $data = $request->validate([
            'type'         => 'required|string|max:30',
            'name'         => 'required|string|max:100',
            'title'        => 'nullable|string|max:255',
            'subtitle'     => 'nullable|string|max:500',
            'body'         => 'nullable|string',
            'button_text'  => 'nullable|string|max:100',
            'button_url'   => 'nullable|string|max:255',
            'button2_text' => 'nullable|string|max:100',
            'button2_url'  => 'nullable|string|max:255',
            'image'        => 'nullable|string|max:255',
            'active'       => 'boolean',
            'order'        => 'integer|min:0',
            'data'         => 'nullable|string', // JSON encoded from repeater
        ]);

        $data['cms_page_id'] = $page->id;
        $data['active']      = $request->boolean('active', true);
        $data['data']        = $this->parseDataField($request->input('data'));

        CmsBlock::create($data);
        return redirect()->route('admin.v2.cms.page', $page->id)->with('success', 'Bloco adicionado.');
    }

    public function editBlock(CmsPage $page, CmsBlock $block)
    {
        return view('admin.v2.cms.block-form', [
            'page'       => $page,
            'block'      => $block,
            'types'      => CmsBlock::TYPES,
            'dataSchema' => CmsBlock::DATA_SCHEMA,
        ]);
    }

    public function updateBlock(Request $request, CmsPage $page, CmsBlock $block)
    {
        $data = $request->validate([
            'type'         => 'required|string|max:30',
            'name'         => 'required|string|max:100',
            'title'        => 'nullable|string|max:255',
            'subtitle'     => 'nullable|string|max:500',
            'body'         => 'nullable|string',
            'button_text'  => 'nullable|string|max:100',
            'button_url'   => 'nullable|string|max:255',
            'button2_text' => 'nullable|string|max:100',
            'button2_url'  => 'nullable|string|max:255',
            'image'        => 'nullable|string|max:255',
            'active'       => 'boolean',
            'order'        => 'integer|min:0',
            'data'         => 'nullable|string',
        ]);

        $data['active'] = $request->boolean('active', true);
        $data['data']   = $this->parseDataField($request->input('data'));

        $block->update($data);
        return redirect()->route('admin.v2.cms.page', $page->id)->with('success', 'Bloco guardado.');
    }

    public function destroyBlock(CmsPage $page, CmsBlock $block)
    {
        $block->delete();
        return redirect()->route('admin.v2.cms.page', $page->id)->with('success', 'Bloco eliminado.');
    }

    public function toggleBlock(CmsPage $page, CmsBlock $block)
    {
        $block->update(['active' => ! $block->active]);
        return response()->json(['active' => $block->active]);
    }

    public function reorderBlocks(Request $request, CmsPage $page)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);
        foreach ($request->order as $position => $blockId) {
            CmsBlock::where('cms_page_id', $page->id)->where('id', $blockId)->update(['order' => $position]);
        }
        return response()->json(['ok' => true]);
    }

    private function parseDataField(?string $raw): ?array
    {
        if (blank($raw)) return null;
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? array_values(array_filter($decoded, fn($r) => !empty(array_filter((array)$r)))) : null;
    }
}
