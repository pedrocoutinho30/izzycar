<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('v3_vehicle_id')
                ->nullable()
                ->after('vehicle_id');
            $table->foreign('v3_vehicle_id')
                ->references('id')
                ->on('v3_vehicles')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['v3_vehicle_id']);
            $table->dropColumn('v3_vehicle_id');
        });
    }
};
