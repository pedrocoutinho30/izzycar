<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Catálogo de marcas/modelos para os selects e a validação.
     *
     * @param  array<string, list<string>>  $catalog
     */
    protected function vehicleCatalog(array $catalog): void
    {
        foreach ($catalog as $brand => $models) {
            $brandModel = \App\Models\Brand::firstOrCreate(['name' => $brand]);
            foreach ($models as $model) {
                \App\Models\ModelCar::firstOrCreate(['brand_id' => $brandModel->id, 'name' => $model]);
            }
        }
    }

    /** Utilizador com perfil — sem perfil, o backoffice é recusado. */
    protected function backofficeUser(string $role = 'admin', array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['password' => 'secret', 'last_name' => 'Teste']);
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }
}
