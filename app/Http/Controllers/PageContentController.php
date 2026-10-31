<?php

namespace App\Http\Controllers;

use App\Models\PageContent;
use Illuminate\Http\Request;

class PageContentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function store(Request $request)
    {
        // Só os campos conhecidos entram (nada de $request->all()).
        $dados = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'field_name' => 'required|string|max:255',
            'field_value' => 'nullable|string|max:65000',
        ]);

        return PageContent::create($dados);
    }

    public function update(Request $request, PageContent $pageContent)
    {
        $dados = $request->validate([
            'field_name' => 'sometimes|required|string|max:255',
            'field_value' => 'nullable|string|max:65000',
        ]);

        $pageContent->update($dados);

        return $pageContent;
    }

    public function destroy(PageContent $pageContent)
    {
        $pageContent->delete();
        return response()->noContent();
    }
}
