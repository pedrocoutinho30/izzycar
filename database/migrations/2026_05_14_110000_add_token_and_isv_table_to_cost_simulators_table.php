<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cost_simulators', function (Blueprint $table) {
            $table->string('token', 64)->nullable()->unique()->after('id');
            $table->longText('isv_table')->nullable()->after('isv_cost');
        });
    }

    public function down(): void
    {
        Schema::table('cost_simulators', function (Blueprint $table) {
            $table->dropColumn(['token', 'isv_table']);
        });
    }
};
