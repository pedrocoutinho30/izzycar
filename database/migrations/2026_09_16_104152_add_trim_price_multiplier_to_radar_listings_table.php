<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('radar_listings', function (Blueprint $table) {
            // Quanto esta versão/trim costuma custar a mais (ou a menos) do que
            // a versão base do mesmo modelo, estimado por IA a partir do texto
            // do anúncio (ex.: "Turbo S" vs "4S") - usado para ajustar o preço
            // antes de calcular o score de oportunidade, para não penalizar uma
            // versão topo de gama só por ser naturalmente mais cara.
            $table->decimal('trim_price_multiplier', 6, 3)->nullable()->after('price_eur');
            $table->timestamp('trim_classified_at')->nullable()->after('trim_price_multiplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radar_listings', function (Blueprint $table) {
            $table->dropColumn(['trim_price_multiplier', 'trim_classified_at']);
        });
    }
};
