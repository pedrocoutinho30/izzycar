<?php

namespace Database\Seeders;

use App\Permissions\PermissionSynchronizer;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Perfis e permissões a partir do catálogo (config/permissions.php). O admin
 * tem acesso total pelo Gate, sem lista de permissões.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(PermissionSynchronizer $synchronizer)
    {
        Role::findOrCreate('admin', 'web');

        $synchronizer->syncCatalog();
        $synchronizer->applyProfiles();
    }
}
