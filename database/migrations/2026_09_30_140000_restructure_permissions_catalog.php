<?php

use App\Permissions\PermissionRegistry;
use App\Permissions\PermissionSynchronizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Novo sistema de permissões (config/permissions.php):
 * - cria as permissões do catálogo (objeto.ação[.âmbito]);
 * - aplica os perfis Importador, angariador e cms;
 * - elimina o perfil "gestor" (sem utilizadores).
 *
 * As permissões antigas ("gerir …", "ver leads proprias", …) não são
 * apagadas: ficam associadas aos perfis, sem efeito, até decisão em contrário.
 * O admin não precisa de permissões — tem acesso total pelo Gate.
 */
return new class extends Migration
{
    public function up(): void
    {
        $synchronizer = new PermissionSynchronizer(new PermissionRegistry(config('permissions')));

        $synchronizer->syncCatalog();
        $synchronizer->applyProfiles();

        $gestor = Role::where('name', 'gestor')->where('guard_name', 'web')->first();
        if ($gestor && !DB::table('model_has_roles')->where('role_id', $gestor->id)->exists()) {
            $gestor->delete();
        }

        $synchronizer->forgetCache();
    }

    public function down(): void
    {
        $registry = new PermissionRegistry(config('permissions'));

        DB::table('permissions')->whereIn('name', $registry->permissionNames())->delete();

        Role::findOrCreate('gestor', 'web');

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
