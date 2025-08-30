<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::all();
        return view('menus.index', compact('menus'));
    }

    public function create()
    {
        return view('menus.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menus,slug',
        ]);

        $menu = Menu::create($request->only('name', 'slug'));

        return redirect()->route('menus.edit', $menu)->with('success', 'Menu criado com sucesso.');
    }

    public function edit(Menu $menu)
    {
        $menu->load('items');
        $pages = Page::all();
        return view('menus.edit', compact('menu', 'pages'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menus,slug,' . $menu->id,
        ]);
        $menu->update($request->only('name', 'slug'));
        // criar ou atualizar os itens do menu
        if ($request->has('items')) {
            // Obter todos os títulos (labels) dos itens enviados na requisição
            $itemLabels = collect($request->items)->pluck('url')->filter()->toArray();

            // Apagar itens antigos que não estão mais na requisição (comparando pelo título e menu_id)
            MenuItem::where('menu_id', $menu->id)
                ->when(!empty($itemLabels), function ($query) use ($itemLabels) {
                    $query->whereNotIn('url', $itemLabels);
                }, function ($query) {
                    // Se não houver itens enviados, apaga todos
                    $query->whereRaw('1=1');
                })
                ->delete();

            // Atualizar ou criar itens
            foreach ($request->items as $itemData) {
                // Procurar item existente pelo título e menu_id
                $item = MenuItem::where('menu_id', $menu->id)
                    ->where('url', $itemData['item_label'])
                    ->first();

                if ($item) {
                    // Atualizar item existente
                    $item->update([
                        'url' => $itemData['url'] ?? null,
                        'page_id' => $itemData['page_id'] ?? null,
                        'order' => $itemData['order'] ?? null,
                    ]);
                } else {
                    // Criar novo item
                    MenuItem::create([
                        'menu_id' => $menu->id,
                        'title' => $itemData['title'],
                        'url' => $itemData['item_label'] ?? null,
                        'page_id' => $itemData['page_id'] ?? null,
                        'order' => $itemData['order'] ?? null,
                    ]);
                }
            }
        }
        return redirect()->route('menus.index')->with('success', 'Menu atualizado com sucesso.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('menus.index')->with('success', 'Menu removido com sucesso.');
    }

    // Menu Item Management
    public function storeItem(Request $request, Menu $menu)
    {
        $request->validate([
            'title' => 'required',
            'url' => 'nullable|url',
            'page_id' => 'nullable|exists:pages,id',
            'order' => 'nullable|integer'
        ]);

        $menu->items()->create($request->only('title', 'url', 'page_id', 'order'));

        return back()->with('success', 'Item de menu adicionado.');
    }

    public function destroyItem(MenuItem $item)
    {
        $item->delete();
        // Remover o item do menu
        $item->menu->items()->detach($item->id);
        
        return back()->with('success', 'Item de menu removido.');
    }
}
