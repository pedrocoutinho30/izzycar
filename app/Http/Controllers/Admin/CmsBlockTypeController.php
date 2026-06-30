<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsBlockType;
use Illuminate\Http\Request;

class CmsBlockTypeController extends Controller
{
    public function index()
    {
        $types = CmsBlockType::orderBy('order')->orderBy('label')->get();
        return view('admin.v2.cms.block-types.index', compact('types'));
    }

    public function create()
    {
        $layouts    = CmsBlockType::LAYOUTS;
        $fieldTypes = CmsBlockType::FIELD_TYPES;
        return view('admin.v2.cms.block-types.form', compact('layouts', 'fieldTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key'    => 'required|string|max:50|unique:cms_block_types,key|regex:/^[a-z0-9\-_]+$/',
            'label'  => 'required|string|max:255',
            'layout' => 'required|string|in:' . implode(',', array_keys(CmsBlockType::LAYOUTS)),
            'order'  => 'nullable|integer|min:0',
        ]);

        $validated['fields'] = $this->parseFields($request);
        $validated['active'] = true;
        $validated['system'] = false;
        $validated['order']  = $validated['order'] ?? 99;

        CmsBlockType::create($validated);

        return redirect()->route('admin.v2.cms.block-types.index')
            ->with('success', 'Tipo de bloco criado com sucesso.');
    }

    public function edit(CmsBlockType $blockType)
    {
        $layouts    = CmsBlockType::LAYOUTS;
        $fieldTypes = CmsBlockType::FIELD_TYPES;
        return view('admin.v2.cms.block-types.form', compact('blockType', 'layouts', 'fieldTypes'));
    }

    public function update(Request $request, CmsBlockType $blockType)
    {
        $validated = $request->validate([
            'label'  => 'required|string|max:255',
            'layout' => 'required|string|in:' . implode(',', array_keys(CmsBlockType::LAYOUTS)),
            'order'  => 'nullable|integer|min:0',
        ]);

        if (!$blockType->system) {
            $request->validate([
                'key' => 'required|string|max:50|regex:/^[a-z0-9\-_]+$/|unique:cms_block_types,key,' . $blockType->id,
            ]);
            $validated['key'] = $request->input('key');
        }

        $validated['fields'] = $this->parseFields($request);
        $validated['active'] = $request->boolean('active');
        $validated['order']  = $validated['order'] ?? 99;

        $blockType->update($validated);

        return redirect()->route('admin.v2.cms.block-types.index')
            ->with('success', 'Tipo de bloco atualizado.');
    }

    public function destroy(CmsBlockType $blockType)
    {
        if ($blockType->system) {
            return back()->with('error', 'Tipos de sistema não podem ser eliminados.');
        }
        $blockType->delete();
        return redirect()->route('admin.v2.cms.block-types.index')
            ->with('success', 'Tipo de bloco eliminado.');
    }

    private function parseFields(Request $request): array
    {
        $keys   = $request->input('field_key', []);
        $labels = $request->input('field_label', []);
        $types  = $request->input('field_type', []);

        $fields = [];
        foreach ($keys as $i => $key) {
            $key = trim($key);
            if ($key === '') continue;
            $fields[] = [
                'key'   => $key,
                'label' => trim($labels[$i] ?? $key),
                'type'  => $types[$i] ?? 'text',
            ];
        }
        return $fields;
    }
}
