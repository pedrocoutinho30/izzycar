<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /** Utilizador com perfil — sem perfil, o backoffice é recusado. */
    protected function backofficeUser(string $role = 'admin', array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['password' => 'secret', 'last_name' => 'Teste']);
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }
}
