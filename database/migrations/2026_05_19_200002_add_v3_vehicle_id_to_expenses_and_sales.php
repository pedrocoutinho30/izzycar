<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('v3_vehicle_id')
                ->nullable()
                ->after('vehicle_id')
                ->constrained('v3_vehicles')
                ->nullOnDelete();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('v3_vehicle_id')
                ->nullable()
                ->after('vehicle_id')
                ->constrained('v3_vehicles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('v3_vehicle_id');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('v3_vehicle_id');
        });
    }
};
