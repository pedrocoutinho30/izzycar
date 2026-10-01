<?php

use App\Permissions\PermissionRegistry;
use App\Permissions\PermissionSynchronizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Pedidos de importação passam a viver dentro da lead/cliente e podem ser
 * criados à mão (ex. um cliente antigo que quer um segundo carro e fala
 * diretamente connosco) — servem para agrupar as oportunidades.
 *
 * - origin: "site" (formulário público) ou "manual" (backoffice);
 * - title: nome curto opcional (ex. "Segundo carro");
 * - created_by: quem o criou no backoffice.
 *
 * Também cria a permissão "form-proposals.create" e dá-a ao Importador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_proposals', function (Blueprint $table) {
            $table->string('origin', 20)->default('site')->after('status');
            $table->string('title')->nullable()->after('origin');
            $table->foreignId('created_by')->nullable()->after('title')->constrained('users')->nullOnDelete();
        });

        (new PermissionSynchronizer(new PermissionRegistry(config('permissions'))))->syncCatalog();
        Role::where('name', 'Importador')->first()?->givePermissionTo('form-proposals.create');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::table('form_proposals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['origin', 'title']);
        });
    }
};
