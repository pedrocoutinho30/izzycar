<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite excluir manualmente um anúncio do cálculo do preço médio - útil
 * quando o mesmo modelo/motorização engloba versões muito diferentes (ex.:
 * baterias/autonomias distintas num elétrico) que distorceriam a média.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radar_listings', function (Blueprint $table) {
            $table->boolean('include_in_average')->default(true)->after('price_eur');
        });
    }

    public function down(): void
    {
        Schema::table('radar_listings', function (Blueprint $table) {
            $table->dropColumn('include_in_average');
        });
    }
};
