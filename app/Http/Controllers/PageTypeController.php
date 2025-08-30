<?php

namespace App\Http\Controllers;

use App\Models\PageType;
use App\Models\PageTypeField;
use Illuminate\Http\Request;

class PageTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $pageTypes = PageType::paginate(10);
        return view('page-types.index', compact('pageTypes'));
    }

    public function create()
    {
        $allPageTypes = PageType::all();

        return view('page-types.form', compact('allPageTypes'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|string|in:text,textarea,image,gallery,boolean,select,radio,page,date,datetime',
            'fields.*.order' => 'nullable|integer',
            'fields.*.is_required' => 'nullable|boolean',
            'fields.*.options' => 'nullable|string',
        ]);

        // Cria o tipo de página
        $pageType = PageType::create([
            'name' => $request->name,
        ]);

        foreach ($request->fields as $field) {
            PageTypeField::create([
                'page_type_id' => $pageType->id,
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'order' => $field['order'] ?? 0,
                'is_required' => isset($field['is_required']) ? true : false,
                'options' => $field['options'] ?? null,
            ]);
        }
        return redirect()->route('page-types.index')->with('success', 'Tipo de página criado com sucesso.');
    }

    public function edit(PageType $pageType)
    {
        $pageType->load('fields');
        $allPageTypes = PageType::all();

        return view('page-types.form', compact('pageType', 'allPageTypes'));
    }


    public function update(Request $request, PageType $pageType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fields' => 'nullable|array',
            'fields.*.name' => 'required|string|max:255',
            'fields.*.type' => 'required|string',
        ]);

        $pageType->update([
            'name' => $request->name,
        ]);

        $existingFieldIds = $pageType->fields()->pluck('id')->toArray();
        $submittedFieldIds = [];

        foreach ($request->fields ?? [] as $fieldData) {
            $fieldData['is_required'] = isset($fieldData['is_required']);
            $fieldData['options'] = $fieldData['options'] ?? null;

            if (isset($fieldData['id'])) {
                // Update
                $pageType->fields()->where('id', $fieldData['id'])->update($fieldData);
                $submittedFieldIds[] = $fieldData['id'];
            } else {
                // Create
                $pageType->fields()->create($fieldData);
            }
        }

        // Delete fields not submitted
        $fieldsToDelete = array_diff($existingFieldIds, $submittedFieldIds);
        if (!empty($fieldsToDelete)) {
            $pageType->fields()->whereIn('id', $fieldsToDelete)->delete();
        }

        return redirect()->route('page-types.index')->with('success', 'Tipo de página atualizado com sucesso!');
    }

    public function destroy(PageType $pageType)
    {
        $pageType->delete();
        // Delete associated fields
        PageTypeField::where('page_type_id', $pageType->id)->delete();
        return redirect()->route('page-types.index')->with('success', 'Page Type deleted successfully.');
    }

    public function duplicate(PageType $pageType)
    {
        // Duplicate the PageType
        $newPageType = $pageType->replicate();
        $newPageType->name = $pageType->name . ' (Copy)';
        $newPageType->save();

        // Duplicate associated fields
        foreach ($pageType->fields as $field) {
            $newField = $field->replicate();
            $newField->page_type_id = $newPageType->id;
            $newField->save();
        }

        return redirect()->route('page-types.index')->with('success', 'Tipo de página duplicado com sucesso!');
    }
}
