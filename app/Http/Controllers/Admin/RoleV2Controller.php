<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Permissions\PermissionRegistry;
use App\Permissions\PermissionSynchronizer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/**
 * Perfis (roles do Spatie). As permissões editam-se numa matriz
 * Categoria → Objeto → Ações → Âmbito gerada a partir do catálogo.
 */
class RoleV2Controller extends Controller
{
    /**
     * Perfis usados pelo nome no código: admin (acesso total) e angariador
     * (portal, comissões). Não podem ser renomeados nem eliminados.
     */
    public const PROTECTED_ROLES = ['admin', 'angariador'];

    public function __construct(private PermissionRegistry $registry, private PermissionSynchronizer $synchronizer)
    {
    }

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
                'title' => 'Permissões do catálogo',
                'value' => count($this->registry->permissionNames()),
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
        return view('admin.v2.roles.form', ['role' => null, 'grants' => [], ...$this->formData()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles|max:255',
            'grants' => 'nullable|array',
        ], ['name.unique' => 'Já existe um perfil com este nome.']);

        $this->synchronizer->syncCatalog();
        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $changes = $this->synchronizer->syncRolePermissions($role, $this->registry->grantsToPermissions($validated['grants'] ?? []));

        AuditLog::record('role_created', "Perfil criado: {$role->name}", $role, null, ['permissions' => $changes['after']]);

        return redirect()->route('admin.v2.roles.index')
            ->with('success', 'Perfil criado com sucesso!');
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return view('admin.v2.roles.form', [
            'role' => $role,
            'grants' => $this->registry->permissionsToGrants($role->permissions->pluck('name')),
            ...$this->formData(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $protected = in_array($role->name, self::PROTECTED_ROLES, true);

        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'grants' => 'nullable|array',
        ], ['name.unique' => 'Já existe um perfil com este nome.']);

        $oldName = $role->name;
        if (!$protected) {
            $role->update(['name' => $validated['name']]);
        }

        // O admin tem acesso total pelo Gate — as suas permissões não se editam.
        if ($role->name !== 'admin') {
            $this->synchronizer->syncCatalog();
            $changes = $this->synchronizer->syncRolePermissions($role, $this->registry->grantsToPermissions($validated['grants'] ?? []));

            $added = array_values(array_diff($changes['after'], $changes['before']));
            $removed = array_values(array_diff($changes['before'], $changes['after']));

            if ($added || $removed) {
                AuditLog::record(
                    'role_permissions_updated',
                    "Permissões do perfil {$role->name} alteradas (+" . count($added) . ' / -' . count($removed) . ')',
                    $role,
                    ['permissions' => $changes['before']],
                    ['permissions' => $changes['after'], 'added' => $added, 'removed' => $removed]
                );
            }
        }

        if ($oldName !== $role->name) {
            AuditLog::record('role_renamed', "Perfil renomeado: {$oldName} → {$role->name}", $role, ['name' => $oldName], ['name' => $role->name]);
        }

        return redirect()->route('admin.v2.roles.index')
            ->with('success', 'Perfil atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return redirect()->route('admin.v2.roles.index')
                ->with('error', 'Este perfil é usado pelo sistema e não pode ser eliminado.');
        }

        // Verificar se há utilizadores com este perfil
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.v2.roles.index')
                ->with('error', 'Não é possível eliminar um perfil com utilizadores associados!');
        }

        $permissions = $role->permissions()->pluck('name')->all();
        $role->delete();
        $this->synchronizer->forgetCache();

        AuditLog::record('role_deleted', "Perfil eliminado: {$role->name}", $role, ['permissions' => $permissions], null);

        return redirect()->route('admin.v2.roles.index')
            ->with('success', 'Perfil eliminado com sucesso!');
    }

    private function formData(): array
    {
        return [
            'modules' => $this->registry->modules(),
            'registry' => $this->registry,
            'protectedRoles' => self::PROTECTED_ROLES,
        ];
    }
}
