<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suporte ao Carmine.pt como segunda fonte de anúncios portugueses (junto do
 * Standvirtual, na mesma tabela "Portugal"), com deteção de anúncios
 * duplicados entre as duas origens (mesmo carro anunciado nos dois sites).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radar_searches', function (Blueprint $table) {
            $table->text('carmine_base_url')->nullable()->after('standvirtual_base_url');
        });

        Schema::table('radar_listings', function (Blueprint $table) {
            $table->foreignId('duplicate_of_listing_id')->nullable()->after('include_in_average')
                ->constrained('radar_listings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('radar_listings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('duplicate_of_listing_id');
        });

        Schema::table('radar_searches', function (Blueprint $table) {
            $table->dropColumn('carmine_base_url');
        });
    }
};
