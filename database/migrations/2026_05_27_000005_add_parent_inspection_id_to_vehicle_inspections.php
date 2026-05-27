<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            $table->foreignId('parent_inspection_id')
                ->nullable()
                ->after('v3_vehicle_id')
                ->constrained('vehicle_inspections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_inspection_id');
        });
    }
};
