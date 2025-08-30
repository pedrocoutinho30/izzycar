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

        
        $request->validate([
            'page_id' => 'required|exists:pages,id',
            'field_slug' => 'required',
            'value' => 'nullable'
        ]);

        return PageContent::create($request->all());
    }

    public function update(Request $request, PageContent $pageContent)
    {
        $pageContent->update($request->all());
        return $pageContent;
    }

    public function destroy(PageContent $pageContent)
    {
        $pageContent->delete();
        return response()->noContent();
    }
}
