<?php
namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
    }
    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'label' => 'required',
            'url' => 'nullable',
            'page_id' => 'nullable|exists:pages,id',
            'parent_id' => 'nullable|exists:menu_items,id',
            'order' => 'nullable|integer'
        ]);

        return MenuItem::create($request->validated());
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $request->validate([
            'label'     => 'sometimes|required|string|max:255',
            'url'       => 'nullable|string|max:500',
            'page_id'   => 'nullable|exists:pages,id',
            'parent_id' => 'nullable|exists:menu_items,id',
            'order'     => 'nullable|integer',
        ]);
        $menuItem->update($request->only(['label', 'url', 'page_id', 'parent_id', 'order']));
        return $menuItem;
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();
        return response()->noContent();
    }
}
