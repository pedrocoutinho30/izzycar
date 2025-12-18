<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleAttribute;
use App\Models\AttributeGroup;
use Illuminate\Http\Request;

class VehicleAttributeV2Controller extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleAttribute::with('attributeGroup');

        // Filtro de pesquisa
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('key', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por grupo
        if ($request->filled('group')) {
            $query->where('attribute_group', $request->group);
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $attributes = $query->orderBy('attribute_group')
            ->orderBy('order')
            ->paginate(20)
            ->withQueryString();

        // Grupos para filtros e estatísticas
        $groups = AttributeGroup::orderBy('order')->get();

        // Estatísticas
        $stats = [
            [
                'title' => 'Total de Atributos',
                'value' => VehicleAttribute::count(),
                'color' => 'primary',
                'icon' => 'bi-tags'
            ],
            [
                'title' => 'Tipo Texto',
                'value' => VehicleAttribute::where('type', 'text')->count(),
                'color' => 'info',
                'icon' => 'bi-input-cursor-text'
            ],
            [
                'title' => 'Tipo Select',
                'value' => VehicleAttribute::where('type', 'select')->count(),
                'color' => 'success',
                'icon' => 'bi-list-ul'
            ],
            [
                'title' => 'Tipo Boolean',
                'value' => VehicleAttribute::where('type', 'boolean')->count(),
                'color' => 'warning',
                'icon' => 'bi-toggle-on'
            ]
        ];

        return view('admin.v2.vehicle-attributes.index', compact('attributes', 'groups', 'stats'));
    }

    public function create()
    {
        $groups = AttributeGroup::orderBy('order')->get();
        return view('admin.v2.vehicle-attributes.form', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:vehicle_attributes,key',
            'type' => 'required|in:text,number,boolean,select',
            'options' => 'nullable|string',
            'attribute_group' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'field_name_autoscout' => 'nullable|string|max:255',
            'field_name_mobile' => 'nullable|string|max:255',
        ]);

        $validated['options'] = $request->type === 'select'
            ? json_encode(array_map('trim', explode(',', $request->options)))
            : null;

        VehicleAttribute::create($validated);

        return redirect()->route('admin.v2.vehicle-attributes.index')
            ->with('success', 'Atributo criado com sucesso!');
    }

    public function edit($id)
    {
        $attribute = VehicleAttribute::findOrFail($id);
        $groups = AttributeGroup::orderBy('order')->get();
        return view('admin.v2.vehicle-attributes.form', compact('attribute', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $attribute = VehicleAttribute::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:vehicle_attributes,key,' . $attribute->id,
            'type' => 'required|in:text,number,boolean,select',
            'options' => 'nullable|string',
            'attribute_group' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'field_name_autoscout' => 'nullable|string|max:255',
            'field_name_mobile' => 'nullable|string|max:255',
        ]);

        $validated['options'] = $request->type === 'select'
            ? json_encode(array_map('trim', explode(',', $request->options)))
            : null;

        $attribute->update($validated);

        return redirect()->route('admin.v2.vehicle-attributes.index')
            ->with('success', 'Atributo atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $attribute = VehicleAttribute::findOrFail($id);
        $attribute->delete();

        return redirect()->route('admin.v2.vehicle-attributes.index')
            ->with('success', 'Atributo eliminado com sucesso!');
    }

    public function sort(Request $request)
    {
        $order = $request->input('order');
        $group = $request->input('group');

        foreach ($order as $index => $id) {
            VehicleAttribute::where('id', $id)
                ->update([
                    'order' => $index,
                    'attribute_group' => $group,
                ]);
        }

        return response()->json(['status' => 'success']);
    }
}
