<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radar_searches', function (Blueprint $table) {
            // Só pesquisas ativas entram na atualização automática periódica (ver
            // scraper/cli.py::cmd_run_active + App\Console\Commands\RefreshActiveRadarSearches).
            $table->boolean('is_active')->default(true)->after('import_cost_eur');

            // Quando cada anúncio ativo do momento em que a pesquisa foi vista pela
            // última vez - "novo" = radar_listings.first_seen_at posterior a isto
            // (ver RadarController::show()). useCurrent() faz o MySQL preencher as
            // pesquisas já existentes com "agora" (não queremos que os anúncios que
            // já lá estavam apareçam de repente como "novos").
            $table->timestamp('new_listings_seen_at')->nullable()->useCurrent()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('radar_searches', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'new_listings_seen_at']);
        });
    }
};
