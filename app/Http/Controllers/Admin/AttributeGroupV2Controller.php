<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;

class AttributeGroupV2Controller extends Controller
{
    public function index(Request $request)
    {
        $query = AttributeGroup::withCount('attributes');

        // Filtro de pesquisa
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $groups = $query->orderBy('order')->paginate(15)->withQueryString();

        // Estatísticas
        $stats = [
            [
                'title' => 'Total de Grupos',
                'value' => AttributeGroup::count(),
                'color' => 'primary',
                'icon' => 'bi-folder'
            ],
            [
                'title' => 'Atributos Totais',
                'value' => \App\Models\VehicleAttribute::count(),
                'color' => 'info',
                'icon' => 'bi-tags'
            ]
        ];

        return view('admin.v2.attribute-groups.index', compact('groups', 'stats'));
    }

    public function create()
    {
        return view('admin.v2.attribute-groups.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:attribute_groups|max:255',
            'order' => 'required|integer',
        ]);

        AttributeGroup::create($validated);

        return redirect()->route('admin.v2.attribute-groups.index')
            ->with('success', 'Grupo criado com sucesso!');
    }

    public function edit($id)
    {
        $group = AttributeGroup::findOrFail($id);
        return view('admin.v2.attribute-groups.form', compact('group'));
    }

    public function update(Request $request, $id)
    {
        $group = AttributeGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255|unique:attribute_groups,name,' . $group->id,
            'order' => 'required|integer',
        ]);

        $group->update($validated);

        return redirect()->route('admin.v2.attribute-groups.index')
            ->with('success', 'Grupo atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $group = AttributeGroup::findOrFail($id);
        
        // Verificar se tem atributos associados
        if ($group->attributes()->count() > 0) {
            return redirect()->route('admin.v2.attribute-groups.index')
                ->with('error', 'Não é possível eliminar um grupo com atributos associados!');
        }

        $group->delete();

        return redirect()->route('admin.v2.attribute-groups.index')
            ->with('success', 'Grupo eliminado com sucesso!');
    }
}
