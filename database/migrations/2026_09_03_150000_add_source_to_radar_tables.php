<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suporte a múltiplas origens por pesquisa (AutoScout24/Alemanha +
 * Standvirtual/Portugal, para comparar preços) - cada anúncio e cada execução
 * passam a saber de onde vieram, e uma pesquisa pode ter um URL base próprio
 * por origem.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radar_searches', function (Blueprint $table) {
            $table->text('standvirtual_base_url')->nullable()->after('base_url');
        });

        Schema::table('radar_listings', function (Blueprint $table) {
            $table->string('source')->default('autoscout24')->after('external_id');
        });
        Schema::table('radar_listings', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
            $table->unique(['source', 'external_id']);
        });

        Schema::table('radar_search_runs', function (Blueprint $table) {
            $table->string('source')->default('autoscout24')->after('radar_search_id');
        });
    }

    public function down(): void
    {
        Schema::table('radar_listings', function (Blueprint $table) {
            $table->dropUnique(['source', 'external_id']);
            $table->unique(['external_id']);
        });
        Schema::table('radar_listings', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        Schema::table('radar_search_runs', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        Schema::table('radar_searches', function (Blueprint $table) {
            $table->dropColumn('standvirtual_base_url');
        });
    }
};
