<?php

namespace App\Http\Controllers;

use App\Models\AttributeGroup;
use Illuminate\Http\Request;

class AttributeGroupController extends Controller
{
    public function index()
    {
        $groups = AttributeGroup::orderBy('order')->get();
        return view('attribute-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('attribute-groups.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:attribute_groups|max:255',
            'order' => 'required|integer',
        ]);

        AttributeGroup::create($request->only('name', 'order'));

        return redirect()->route('attribute-groups.index')->with('success', 'Grupo criado com sucesso!');
    }

    public function show(AttributeGroup $attributeGroup)
    {
        return view('attribute-groups.show', compact('attributeGroup'));
    }

    public function edit(AttributeGroup $attributeGroup)
    {
        return view('attribute-groups.form', compact('attributeGroup'));
    }

    public function update(Request $request, AttributeGroup $attributeGroup)
    {
        $request->validate([
            'name' => 'required|max:255|unique:attribute_groups,name,' . $attributeGroup->id,
            'order' => 'required|integer',
        ]);

        $attributeGroup->update($request->only('name', 'order'));

        return redirect()->route('attribute-groups.index')->with('success', 'Grupo atualizado com sucesso!');
    }

    public function destroy(AttributeGroup $attributeGroup)
    {
        $attributeGroup->delete();
        return redirect()->route('attribute-groups.index')->with('success', 'Grupo eliminado com sucesso!');
    }
}
