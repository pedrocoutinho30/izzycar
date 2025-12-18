<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionV2Controller extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::query();

        // Filtro de pesquisa
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $permissions = $query->orderBy('name')->paginate(20)->withQueryString();

        // Estatísticas
        $stats = [
            [
                'title' => 'Total de Permissões',
                'value' => Permission::count(),
                'color' => 'primary',
                'icon' => 'bi-key'
            ],
            [
                'title' => 'Perfis',
                'value' => \Spatie\Permission\Models\Role::count(),
                'color' => 'info',
                'icon' => 'bi-person-badge'
            ]
        ];

        return view('admin.v2.permissions.index', compact('permissions', 'stats'));
    }

    public function create()
    {
        return view('admin.v2.permissions.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:permissions|max:255'
        ]);

        Permission::create(['name' => $validated['name']]);

        return redirect()->route('admin.v2.permissions.index')
            ->with('success', 'Permissão criada com sucesso!');
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('admin.v2.permissions.form', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255|unique:permissions,name,' . $permission->id
        ]);

        $permission->update(['name' => $validated['name']]);

        return redirect()->route('admin.v2.permissions.index')
            ->with('success', 'Permissão atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('admin.v2.permissions.index')
            ->with('success', 'Permissão eliminada com sucesso!');
    }
}
