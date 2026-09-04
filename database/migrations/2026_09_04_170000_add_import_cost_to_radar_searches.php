<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radar_searches', function (Blueprint $table) {
            $table->decimal('import_cost_eur', 10, 2)->nullable()->after('carmine_base_url');
        });
    }

    public function down(): void
    {
        Schema::table('radar_searches', function (Blueprint $table) {
            $table->dropColumn('import_cost_eur');
        });
    }
};
