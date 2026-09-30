<?php

namespace App\Permissions;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Leva o catálogo (config/permissions.php) para as tabelas do Spatie. Só
 * cria o que falta — nunca apaga permissões (as antigas "gerir …" ficam,
 * sem efeito, até decisão em contrário).
 */
class PermissionSynchronizer
{
    public function __construct(private PermissionRegistry $registry)
    {
    }

    /** @return list<string> permissões criadas */
    public function syncCatalog(): array
    {
        $existing = Permission::where('guard_name', 'web')->pluck('name')->all();
        $created = [];

        foreach ($this->registry->permissionNames() as $name) {
            if (!in_array($name, $existing, true)) {
                Permission::create(['name' => $name, 'guard_name' => 'web']);
                $created[] = $name;
            }
        }

        $this->forgetCache();

        return $created;
    }

    /**
     * Aplica os perfis de config("permissions.profiles"): cada perfil fica
     * com exatamente as permissões do catálogo definidas lá (mantém as
     * permissões antigas, fora do catálogo).
     */
    public function applyProfiles(): void
    {
        foreach ($this->registry->profileNames() as $profile) {
            $role = Role::findOrCreate($profile, 'web');
            $this->syncRolePermissions($role, $this->registry->profilePermissions($profile));
        }
    }

    /**
     * Substitui as permissões do catálogo de um perfil, preservando as que
     * estão fora do catálogo.
     *
     * @param  list<string>  $catalogPermissions
     * @return array{before: list<string>, after: list<string>}
     */
    public function syncRolePermissions(Role $role, array $catalogPermissions): array
    {
        $before = $role->permissions()->pluck('name')->sort()->values()->all();
        $legacy = array_values(array_filter($before, fn ($name) => !$this->registry->isCatalogPermission($name)));

        $role->syncPermissions(array_merge($legacy, $catalogPermissions));
        $this->forgetCache();

        $after = $role->permissions()->pluck('name')->sort()->values()->all();

        return ['before' => $before, 'after' => $after];
    }

    public function forgetCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
