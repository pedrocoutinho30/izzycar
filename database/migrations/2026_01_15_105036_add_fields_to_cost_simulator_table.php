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
            $table->string('fuel')->nullable();
            $table->string('year')->nullable();
            $table->string('cc')->nullable();
            $table->string('co2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cost_simulators', function (Blueprint $table) {
            $table->dropColumn(['fuel', 'year', 'cc', 'co2']);
        });
    }
};
