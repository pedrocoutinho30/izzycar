<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colunas que existem em produção mas que nenhuma migration cria — ambientes
 * novos (e os testes) ficavam sem elas:
 * - clients.owner_id: angariador dono da lead, base do âmbito "próprios";
 * - users.commission_fixed_value: comissão fixa do angariador.
 * Em produção não faz nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('clients', 'owner_id')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('users', 'commission_fixed_value')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('commission_fixed_value', 10, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        // Não remover: em produção as colunas são anteriores a esta migration.
    }
};
