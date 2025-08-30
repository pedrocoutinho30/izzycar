<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class PageController extends Controller
{

    public function index(Request $request)
    {
        $query = Page::query();

        if ($request->filled('type')) {
            $query->where('page_type_id', $request->type);
        }

        $pages = $query->where('parent_id', null)->with('pageType')->latest()->get();

        //para cada pagina com id null pegar os filhos
        foreach ($pages as $page) {
            $page->children = Page::where('parent_id', $page->id)->get();
        }
        $pageTypes = PageType::all(); // Para popular o select

        return view('pages.index', compact('pages', 'pageTypes'));
    }


    public function create(Request $request)
    {
        $pageTypes = PageType::with('fields')->get();
        $allPages = Page::all();
        $page_type_id = $request->query('page_type_id', null);

        return view('pages.form', compact('pageTypes', 'allPages', 'page_type_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'page_type_id' => 'required|exists:page_types,id',
        ]);

        $page = Page::create([
            'title' => $request->title,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->title),
            'page_type_id' => $request->page_type_id,
        ]);

        $fields = $request->input('fields', []);
        foreach ($fields as $fieldLabel => $value) {
            $page->contents()->create([
                'field_name' => $fieldLabel,
                'field_value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }

        return redirect()->route('pages.index')->with('success', 'Página criada com sucesso.');
    }

    public function edit(Page $page)
    {
        $pageTypes = PageType::with('fields')->get();
        $allPages = Page::all();

        // Carregar conteúdos já salvos da página
        $contents = $page->contents->pluck('field_value', 'field_name')->toArray();
        $page_type_id = $page->page_type_id;
        return view('pages.form', compact('page', 'pageTypes', 'contents', 'allPages', 'page_type_id'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'page_type_id' => 'required|exists:page_types,id',
        ]);


        $page->update([
            'title' => $request->title,
            'slug' => $request->slug ? Str::slug($request->slug) : Str::slug($request->title),
            'page_type_id' => $request->page_type_id,
        ]);


        // Atualizar conteúdos (excluir os antigos e inserir os novos)
        $page->contents()->delete();

        $fields = $request->input('fields', []);
        foreach ($fields as $fieldLabel => $value) {

            if (is_array($value)) {
                foreach ($value as $key => $val) {

                    $pageToEditParent = Page::find($val);
                    $pageToEditParent->update(['parent_id' => $page->id]);
                }
            }
            $page->contents()->create([
                'field_name' => $fieldLabel,
                'field_value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }

        return redirect()->route('pages.index')->with('success', 'Página atualizada com sucesso.');
    }

    public function destroy(Page $page)
    {
        $page->contents()->delete();
        $page->delete();

        return redirect()->route('pages.index')->with('success', 'Página eliminada com sucesso.');
    }

    public function getNews()
    {
        $pageType = PageType::where('name', PageType::NEWS)->first();
        $pages = Page::where('page_type_id', $pageType->id)->latest()->get();
        return view('pages.news', compact('pages', 'pageType'));
    }

    public function getCategories()
    {
        $pageType = PageType::where('name', PageType::CATEGORIES)->first();
        $pages = Page::where('page_type_id', $pageType->id)->latest()->get();

        return view('pages.categories', compact('pages', 'pageType'));
    }

    public function getLegalizations()
    {
        $pageType = PageType::where('name', PageType::LEGALIZATIONS)->first();
        $pages = Page::where('page_type_id', $pageType->id)->latest()->get();
        return view('pages.legalizations', compact('pages', 'pageType'));
    }

    public function getImports()
    {

        $pageType = PageType::where('name', PageType::IMPORTS)->first();
        $pages = Page::where('page_type_id', $pageType->id)->get();



        return view('pages.imports', compact('pages', 'pageType'));
    }

    public function getSelling()
    {
        $pageType = PageType::where('name', PageType::SELLING)->first();
        $pages = Page::where('page_type_id', $pageType->id)->latest()->get();
        return view('pages.selling', compact('pages', 'pageType'));
    }
}
