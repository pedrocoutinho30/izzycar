<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contrato de angariação assinado, carregado pelo admin no perfil do
 * angariador (disco local, privado) e visível ao próprio angariador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('contrato_assinado_path')->nullable();
            $table->timestamp('contrato_assinado_em')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['contrato_assinado_path', 'contrato_assinado_em']);
        });
    }
};
