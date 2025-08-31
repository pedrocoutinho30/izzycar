<?php

// app/Http/Controllers/MenuController.php
namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::whereNull('parent_id')->orderBy('order')->with('children')->get();
        return view('menus.index', compact('menus'));
    }

    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Mostra o formul rio de cria o de menu.
     * 
     * @return \Illuminate\Http\Response
     */
    /*******  9880a523-9560-4c88-afde-df814901d255  *******/
    public function create()
    {
        $parents = Menu::whereNull('parent_id')->get(); // só menus principais podem ter submenus
        return view('menus.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'url'   => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer',
            'show_online' => 'required|boolean'
        ]);

        Menu::create($data);

        return redirect()->route('menus.index')->with('success', 'Menu criado com sucesso!');
    }

    public function edit(Menu $menu)
    {
        $parents = Menu::whereNull('parent_id')->where('id', '!=', $menu->id)->get();
        return view('menus.edit', compact('menu', 'parents'));
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'url'   => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:menus,id',
            'order' => 'nullable|integer',
            'show_online' => 'required|boolean',
        ]);

        $menu->update($data);

        return redirect()->route('menus.index')->with('success', 'Menu atualizado com sucesso!');
    }
}
