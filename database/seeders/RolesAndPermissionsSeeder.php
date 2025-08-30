<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Limpar cache de permissões
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ----------------------
        // Permissões ADMIN
        // ----------------------
        Permission::create(['name' => 'gerir marcas']);
        Permission::create(['name' => 'gerir atributos veiculos']);
        Permission::create(['name' => 'gerir tipos de pagina']);
        Permission::create(['name' => 'gerir paginas']);
        Permission::create(['name' => 'gerir menus']);
        Permission::create(['name' => 'gerir utilizadores']);
        // ----------------------
        // Permissões CMS
        // ----------------------
        Permission::create(['name' => 'gerir categoria de noticias']);
        Permission::create(['name' => 'gerir noticias']);
        Permission::create(['name' => 'gerir importacoes']);
        Permission::create(['name' => 'gerir legalizacoes']);
        Permission::create(['name' => 'gerir pagina de venda']);



        // ----------------------
        // Permissões Gestão Stand
        // ----------------------
        Permission::create(['name' => 'gerir clientes']);
        Permission::create(['name' => 'gerir propostas']);
        Permission::create(['name' => 'gerir veiculos']);
        Permission::create(['name' => 'gerir despesas']);
        Permission::create(['name' => 'gerir vendas']);
        Permission::create(['name' => 'gerir fornecedores']);
        Permission::create(['name' => 'gerir parceiros']);
        Permission::create(['name' => 'analisar mercado']);

        // ----------------------
        // Roles
        // ----------------------
        $admin = Role::create(['name' => 'admin']);
        $cms = Role::create(['name' => 'cms']);
        $gestor = Role::create(['name' => 'gestor']);

        // ----------------------
        // Atribuir permissões às roles
        // ----------------------
        $admin->givePermissionTo(Permission::all()); // admin tem tudo

        $cms->givePermissionTo([
            'gerir categoria de noticias',
            'gerir noticias',
            'gerir importacoes',
            'gerir legalizacoes',
            'gerir pagina de venda',
            'gerir menus'

        ]);

      

        $gestor->givePermissionTo([
            'gerir marcas',
            'gerir atributos veiculos',
            'gerir clientes',
            'gerir propostas',
            'gerir vendas',
            'gerir veiculos',
            'gerir fornecedores',
            'gerir parceiros',
            'gerir despesas',
            'analisar mercado',
        ]);
    }
}
