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
            $table->string('brand')->nullable()->after('client_id');
            $table->string('model')->nullable()->after('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cost_simulators', function (Blueprint $table) {
            $table->dropColumn('brand');
            $table->dropColumn('model');
        });
    }
};
