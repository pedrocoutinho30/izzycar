<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Utilizadores sem nenhum perfil passam a ser bloqueados no backoffice. Os
 * que já existiam (criados antes de o perfil ser obrigatório) ficam com o
 * perfil Importador, para não perderem o acesso.
 */
return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('roles')->where('name', 'Importador')->where('guard_name', 'web')->value('id')
            ?? DB::table('roles')->insertGetId([
                'name' => 'Importador',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $userIds = DB::table('users')
            ->whereNotIn('id', DB::table('model_has_roles')->where('model_type', 'App\\Models\\User')->select('model_id'))
            ->pluck('id');

        DB::table('model_has_roles')->insert($userIds->map(fn ($id) => [
            'role_id' => $roleId,
            'model_type' => 'App\\Models\\User',
            'model_id' => $id,
        ])->all());

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Não reversível de forma segura: não se sabe quais eram os utilizadores sem perfil.
    }
};
