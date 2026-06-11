<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuV2Controller extends Controller
{
    public function index()
    {
        $menuItems = Menu::whereNull('parent_id')->orderBy('order')->with('children')->get();
        return view('admin.v2.menus.index', compact('menuItems'));
    }

    public function create()
    {
        $parents = Menu::whereNull('parent_id')->orderBy('order')->get();
        return view('admin.v2.menus.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'url'         => 'nullable|string|max:255',
            'parent_id'   => 'nullable|exists:menus,id',
            'order'       => 'nullable|integer|min:0',
            'show_online' => 'required|boolean',
        ]);

        Menu::create($data);

        return redirect()->route('admin.v2.menus.index')->with('success', 'Item de menu criado com sucesso.');
    }

    public function edit(Menu $menu)
    {
        $parents = Menu::whereNull('parent_id')->where('id', '!=', $menu->id)->orderBy('order')->get();
        return view('admin.v2.menus.edit', compact('menu', 'parents'));
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'url'         => 'nullable|string|max:255',
            'parent_id'   => 'nullable|exists:menus,id',
            'order'       => 'nullable|integer|min:0',
            'show_online' => 'required|boolean',
        ]);

        $menu->update($data);

        return redirect()->route('admin.v2.menus.index')->with('success', 'Item de menu atualizado com sucesso.');
    }

    public function destroy(Menu $menu)
    {
        $menu->children()->delete();
        $menu->delete();

        return redirect()->route('admin.v2.menus.index')->with('success', 'Item de menu eliminado.');
    }

    public function toggle(Menu $menu)
    {
        $menu->update(['show_online' => !$menu->show_online]);
        return response()->json(['show_online' => $menu->show_online]);
    }
}
