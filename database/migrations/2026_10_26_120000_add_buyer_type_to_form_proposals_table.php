<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tipo de comprador no pedido de cotação: particular ou empresa
 * (Preferências de Compra). Nulo nos pedidos antigos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_proposals', function (Blueprint $table) {
            $table->string('buyer_type', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('form_proposals', function (Blueprint $table) {
            $table->dropColumn('buyer_type');
        });
    }
};
