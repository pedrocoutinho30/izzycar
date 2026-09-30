<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Permissions\PermissionRegistry;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionV2Controller extends Controller
{
    /**
     * Catálogo de permissões (config/permissions.php), só de consulta: as
     * permissões atribuem-se nos perfis.
     */
    public function index(PermissionRegistry $registry)
    {
        $roles = Role::with('permissions:id,name')->orderBy('name')->get();
        $roleGrants = $roles->mapWithKeys(fn (Role $role) => [
            $role->name => $registry->permissionsToGrants($role->permissions->pluck('name')),
        ]);

        $stats = [
            [
                'title' => 'Permissões do catálogo',
                'value' => count($registry->permissionNames()),
                'color' => 'primary',
                'icon' => 'bi-key'
            ],
            [
                'title' => 'Objetos',
                'value' => count($registry->resources()),
                'color' => 'info',
                'icon' => 'bi-grid-3x3-gap'
            ],
            [
                'title' => 'Perfis',
                'value' => $roles->count(),
                'color' => 'success',
                'icon' => 'bi-person-badge'
            ]
        ];

        return view('admin.v2.permissions.index', [
            'modules' => $registry->modules(),
            'registry' => $registry,
            'roleGrants' => $roleGrants,
            'stats' => $stats,
        ]);
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
