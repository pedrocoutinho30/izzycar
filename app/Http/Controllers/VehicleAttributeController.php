<?php

namespace App\Http\Controllers;

use App\Models\VehicleAttribute;
use Illuminate\Http\Request;

class VehicleAttributeController extends Controller
{
    public function index()
    {
        $attributes = VehicleAttribute::orderBy('attribute_group')
            ->orderBy('order')
            ->get()
            ->groupBy('attribute_group');

        return view('vehicle_attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('vehicle_attributes.form');
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
        ]);

        $validated['options'] = $request->type === 'select'
            ? array_map('trim', explode(',', $request->options))
            : null;

        VehicleAttribute::create($validated);

        return redirect()->route('vehicle-attributes.index')->with('success', 'Atributo criado com sucesso.');
    }

    public function edit(VehicleAttribute $vehicleAttribute)
    {
        return view('vehicle_attributes.form', compact('vehicleAttribute'));
    }

    public function update(Request $request, VehicleAttribute $vehicleAttribute)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:vehicle_attributes,key,' . $vehicleAttribute->id,
            'type' => 'required|in:text,number,boolean,select',
            'options' => 'nullable|string',
            'attribute_group' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
        ]);

        $validated['options'] = $request->type === 'select' ? json_encode(explode(',', $request->options)) : null;

        $vehicleAttribute->update($validated);

        return redirect()->route('vehicle-attributes.index')->with('success', 'Atributo atualizado com sucesso.');
    }
    public function sort(Request $request)
    {
        $order = $request->input('order'); // array de IDs na nova ordem
        $group = $request->input('group'); // grupo atual

        foreach ($order as $index => $id) {
            VehicleAttribute::where('id', $id)
                ->update([
                    'order' => $index,
                    'attribute_group' => $group, // opcional, se quiseres garantir
                ]);
        }

        return response()->json(['status' => 'success']);
    }
    public function destroy(VehicleAttribute $vehicleAttribute)
    {
        $vehicleAttribute->delete();
        return redirect()->route('vehicle-attributes.index')->with('success', 'Atributo removido com sucesso.');
    }

    public function updateGroup(Request $request, $id)
    {
        // Validar a entrada
        $request->validate([
            'attribute_group' => 'required|string|max:255',
        ]);

        // Buscar o atributo pelo ID
        $attribute = VehicleAttribute::findOrFail($id);

        // Atualizar o grupo de atributos
        $attribute->attribute_group = $request->input('attribute_group');
        $attribute->save();

        // Redirecionar de volta para a lista com mensagem de sucesso (opcional)
        return redirect()->back()->with('success', 'Grupo de atributo atualizado com sucesso.');
    }
}
