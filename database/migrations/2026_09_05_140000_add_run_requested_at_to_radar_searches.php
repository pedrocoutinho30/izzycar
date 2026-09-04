<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radar_searches', function (Blueprint $table) {
            // Em alojamentos onde o PHP não pode lançar processos (exec()/
            // proc_open() desativados, ex.: Hostinger) não há forma de disparar a
            // recolha diretamente a partir de um pedido web - fica só marcado aqui,
            // e um cron frequente (scraper.cli run-requested, chamado diretamente,
            // sem passar pelo PHP) apanha o pedido e corre a recolha a sério. Ver
            // App\Services\AutoscoutScraperRunner::syncAndRun().
            $table->timestamp('run_requested_at')->nullable()->after('new_listings_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('radar_searches', function (Blueprint $table) {
            $table->dropColumn('run_requested_at');
        });
    }
};
