<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->foreignId('generated_from_inspection_id')
                ->nullable()
                ->after('asking_price')
                ->constrained('vehicle_inspections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('v3_vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('generated_from_inspection_id');
        });
    }
};
