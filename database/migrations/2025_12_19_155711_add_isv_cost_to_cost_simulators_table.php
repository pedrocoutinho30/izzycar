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
        Schema::table('cost_simulators', function (Blueprint $table) {
            $table->decimal('isv_cost', 10, 2)->default(0)->after('car_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cost_simulators', function (Blueprint $table) {
            $table->dropColumn('isv_cost');
        });
    }
};
