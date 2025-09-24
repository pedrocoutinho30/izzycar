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


        $fields = [];
        $fields = $request->all()['fields'] ?? [];

        foreach ($fields as $fieldLabel => $value) {
            $fieldValue = '';
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                $originalName = $value->getClientOriginalName();
                $fieldValue = $value->storeAs('files', $originalName, 'public');
            } elseif (is_array($value) && count($value) > 0 && $value[0] instanceof \Illuminate\Http\UploadedFile) {
                // múltiplos ficheiros (galeria)
                $filePaths = [];
                foreach ($value as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filePaths[] = $file->storeAs('files', $originalName, 'public');
                }
                $fieldValue = json_encode($filePaths);
            } else {
                $fieldValue = is_array($value) ? json_encode($value) : $value;
            }

            // atualizar ou criar campo
            $content = $page->contents()->where('field_name', $fieldLabel)->first();
            if ($content) {
                $content->update(['field_value' => $fieldValue]);
            } else {
                $page->contents()->create([
                    'field_name' => $fieldLabel,
                    'field_value' => $fieldValue,
                ]);
            }
        }




        $redirect = '';
        $pageType = $page->pageType->name;
        if ($pageType == PageType::NEWS) {
            $redirect = 'news';
        } else if ($pageType == PageType::CATEGORIES) {
            $redirect = 'categories';
        } else if ($pageType == PageType::LEGALIZATIONS) {
            $redirect = 'legalizations';
        } else if ($pageType == PageType::IMPORTS) {
            $redirect = 'imports';
        } else if ($pageType == PageType::SELLING) {
            $redirect = 'selling';
        } else if ($pageType == PageType::HOMEPAGE) {
            $redirect = 'homepage';
        } else {
            $redirect = 'index';
        }
        return redirect()->route('pages.' . $redirect)->with('success', 'Página criada com sucesso.');
        // Pega campos normais
        if ($request->has('fields')) {
            $fields = $request->input('fields');
        }

        // trata de campos de texto
        $fields = $request->input('fields', []);
        foreach ($fields as $fieldLabel => $value) {
            $page->contents()->create([
                'field_name' => $fieldLabel,
                'field_value' => is_array($value) ? json_encode($value) : $value,
            ]);
        }

        // Pega arquivos dentro de fields
        if ($request->hasFile('fields')) {

            foreach ($request->file('fields') as $key => $file) {
                $originalName = $file->getClientOriginalName();
                $fieldValue = $file->storeAs('files', $originalName, 'public');
                $page->contents()->create([
                    'field_name' => $key,
                    'field_value' => $fieldValue
                ]);
            }
        }
        $redirect = '';
        $pageType = $page->pageType->name;
        if ($pageType == PageType::NEWS) {
            $redirect = 'news';
        } else if ($pageType == PageType::CATEGORIES) {
            $redirect = 'categories';
        } else if ($pageType == PageType::LEGALIZATIONS) {
            $redirect = 'legalizations';
        } else if ($pageType == PageType::IMPORTS) {
            $redirect = 'imports';
        } else if ($pageType == PageType::SELLING) {
            $redirect = 'selling';
        } else if ($pageType == PageType::HOMEPAGE) {
            $redirect = 'homepage';
        } else {
            $redirect = 'index';
        }
        return redirect()->route('pages.' . $redirect)->with('success', 'Página criada com sucesso.');
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

        $fields = $request->all()['fields'] ?? [];


        foreach ($fields as $fieldLabel => $value) {
            $fieldValue = '';
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                $originalName = $value->getClientOriginalName();
                $fieldValue = $value->storeAs('files', $originalName, 'public');
            } elseif (is_array($value) && count($value) > 0 && $value[0] instanceof \Illuminate\Http\UploadedFile) {
                // múltiplos ficheiros (galeria)
                $filePaths = [];
                foreach ($value as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filePaths[] = $file->storeAs('files', $originalName, 'public');
                }
                $fieldValue = json_encode($filePaths);
            } else {
                $fieldValue = is_array($value) ? json_encode($value) : $value;
            }

            // atualizar ou criar campo
            $content = $page->contents()->where('field_name', $fieldLabel)->first();
            if ($content) {
                
                $content->update(['field_value' => $fieldValue]);
            } else {
               
                $page->contents()->create([
                    'field_name' => $fieldLabel,
                    'field_value' => $fieldValue,
                ]);
            }
        }


        $redirect = '';
        $pageType = $page->pageType->name;
        if ($pageType == PageType::NEWS) {
            $redirect = 'news';
        } else if ($pageType == PageType::CATEGORIES) {
            $redirect = 'categories';
        } else if ($pageType == PageType::LEGALIZATIONS) {
            $redirect = 'legalizations';
        } else if ($pageType == PageType::IMPORTS) {
            $redirect = 'imports';
        } else if ($pageType == PageType::SELLING) {
            $redirect = 'selling';
        } else if ($pageType == PageType::HOMEPAGE) {
            $redirect = 'homepage';
        } else {
            $redirect = 'index';
        }
        return redirect()->route('pages.' . $redirect)->with('success', 'Página atualizada com sucesso.');

        // Atualizar conteúdos (excluir os antigos e inserir os novos)
        $page->contents()->delete();
        $fields = [];
        if ($request->has('fields')) {
            $fields = $request->input('fields');
        }
        $existingFields = $request->input('fields_existing', []);





        // trata de campos de texto
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


        // Arquivos dentro de fields
        if ($request->hasFile('fields')) {

            foreach ($request->file('fields') as $key => $file) {
                $originalName = $file->getClientOriginalName();
                $fieldValue = $file->storeAs('files', $originalName, 'public');
                $page->contents()->create([
                    'field_name' => $key,
                    'field_value' => $fieldValue
                ]);
                //remover a que foi alterada do existing fields
                if (isset($existingFields[$key])) {
                    unset($existingFields[$key]);
                }
            }
        }
        //verificar se existe ainda alguma coisa no existingFields e criar ou seja, aqui são imagens antigas que não foram alteradas
        foreach ($existingFields as $key => $value) {

            $page->contents()->create([
                'field_name' => $key,
                'field_value' => $value
            ]);
        }


        $hasFiles = collect($request->all()['fields'] ?? [])
            ->flatten()
            ->contains(fn($val) => $val instanceof \Illuminate\Http\UploadedFile);
        $fields = $request->all()['fields'] ?? [];

        if ($hasFiles) {
            foreach ($fields as $fieldLabel => $value) {
                $fieldValue = '';
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $originalName = $value->getClientOriginalName();
                    $fieldValue = $value->storeAs('files', $originalName, 'public');
                } elseif (is_array($value) && count($value) > 0 && $value[0] instanceof \Illuminate\Http\UploadedFile) {
                    // múltiplos ficheiros (galeria)
                    $filePaths = [];
                    foreach ($value as $file) {
                        $originalName = $file->getClientOriginalName();
                        $filePaths[] = $file->storeAs('files', $originalName, 'public');
                    }
                    $fieldValue = json_encode($filePaths);
                } else {
                    $fieldValue = is_array($value) ? json_encode($value) : $value;
                }

                // atualizar ou criar campo
                $content = $page->contents()->where('field_name', $fieldLabel)->first();
                if ($content) {
                    $content->update(['field_value' => $fieldValue]);
                } else {
                    $page->contents()->create([
                        'field_name' => $fieldLabel,
                        'field_value' => $fieldValue,
                    ]);
                }
            }
        }



        $redirect = '';
        $pageType = $page->pageType->name;
        if ($pageType == PageType::NEWS) {
            $redirect = 'news';
        } else if ($pageType == PageType::CATEGORIES) {
            $redirect = 'categories';
        } else if ($pageType == PageType::LEGALIZATIONS) {
            $redirect = 'legalizations';
        } else if ($pageType == PageType::IMPORTS) {
            $redirect = 'imports';
        } else if ($pageType == PageType::SELLING) {
            $redirect = 'selling';
        } else if ($pageType == PageType::HOMEPAGE) {
            $redirect = 'homepage';
        } else {
            $redirect = 'index';
        }
        return redirect()->route('pages.' . $redirect)->with('success', 'Página atualizada com sucesso.');
    }

    public function destroy(Page $page)
    {
        $page->contents()->delete();
        $page->delete();


        $redirect = '';
        $pageType = $page->pageType->name;
        if ($pageType == PageType::NEWS) {
            $redirect = 'news';
        } else if ($pageType == PageType::CATEGORIES) {
            $redirect = 'categories';
        } else if ($pageType == PageType::LEGALIZATIONS) {
            $redirect = 'legalizations';
        } else if ($pageType == PageType::IMPORTS) {
            $redirect = 'imports';
        } else if ($pageType == PageType::SELLING) {
            $redirect = 'selling';
        } else if ($pageType == PageType::HOMEPAGE) {
            $redirect = 'homepage';
        } else {
            $redirect = 'index';
        }
        return redirect()->route('pages.' . $redirect)->with('success', 'Página eliminada com sucesso.');
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

    public function getHomepage()
    {

        $pageType = PageType::where('name', PageType::HOMEPAGE)->first();
        $pages = Page::where('page_type_id', $pageType->id)->get();



        return view('pages.homepage', compact('pages', 'pageType'));
    }

    public function getSelling()
    {
        $pageType = PageType::where('name', PageType::SELLING)->first();
        $pages = Page::where('page_type_id', $pageType->id)->latest()->get();
        return view('pages.selling', compact('pages', 'pageType'));
    }
}
