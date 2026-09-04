<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // SQL direto em vez de Schema::table(...)->change() porque isso precisa do
    // pacote doctrine/dbal, que não está instalado neste projeto.
    public function up(): void
    {
        // Permite pesquisas sem marca (ex.: "todos os elétricos até X km/€") -
        // confirmado que a AutoScout24 aceita /lst sem segmento de marca, ver
        // scraper/filters.py::build_base_url.
        DB::statement('ALTER TABLE radar_searches MODIFY make VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE radar_searches SET make = '' WHERE make IS NULL");
        DB::statement('ALTER TABLE radar_searches MODIFY make VARCHAR(255) NOT NULL');
    }
};
