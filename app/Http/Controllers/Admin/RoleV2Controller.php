<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleV2Controller extends Controller
{
    public function index(Request $request)
    {
        $query = Role::withCount('permissions', 'users');

        // Filtro de pesquisa
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $roles = $query->orderBy('name')->paginate(15)->withQueryString();

        // Estatísticas
        $stats = [
            [
                'title' => 'Total de Perfis',
                'value' => Role::count(),
                'color' => 'primary',
                'icon' => 'bi-person-badge'
            ],
            [
                'title' => 'Permissões Totais',
                'value' => Permission::count(),
                'color' => 'info',
                'icon' => 'bi-key'
            ],
            [
                'title' => 'Utilizadores com Perfis',
                'value' => \App\Models\User::has('roles')->count(),
                'color' => 'success',
                'icon' => 'bi-people'
            ]
        ];

        return view('admin.v2.roles.index', compact('roles', 'stats'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        return view('admin.v2.roles.form', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::create(['name' => $validated['name']]);
        
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->input('permissions', []));
        }

        return redirect()->route('admin.v2.roles.index')
            ->with('success', 'Perfil criado com sucesso!');
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        return view('admin.v2.roles.form', compact('role', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->update(['name' => $validated['name']]);
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('admin.v2.roles.index')
            ->with('success', 'Perfil atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Verificar se há utilizadores com este perfil
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.v2.roles.index')
                ->with('error', 'Não é possível eliminar um perfil com utilizadores associados!');
        }

        $role->delete();

        return redirect()->route('admin.v2.roles.index')
            ->with('success', 'Perfil eliminado com sucesso!');
    }
}
