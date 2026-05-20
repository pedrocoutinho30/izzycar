<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('v3_vehicles', function (Blueprint $table) {
            // Submodelo
            $table->string('sub_model', 150)->nullable()->after('model');
            // Month + day for year (year field already exists as unsignedSmallInteger)
            $table->unsignedTinyInteger('month')->nullable()->after('year');
            $table->unsignedTinyInteger('day')->nullable()->after('month');
            // Importado
            $table->boolean('is_imported')->default(false)->after('show_online');
        });

        Schema::table('legalizations', function (Blueprint $table) {
            $table->unsignedBigInteger('v3_vehicle_id')->nullable()->after('vehicle_id');
            $table->foreign('v3_vehicle_id')->references('id')->on('v3_vehicles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('legalizations', function (Blueprint $table) {
            $table->dropForeign(['v3_vehicle_id']);
            $table->dropColumn('v3_vehicle_id');
        });

        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->dropColumn(['sub_model', 'month', 'day', 'is_imported']);
        });
    }
};
