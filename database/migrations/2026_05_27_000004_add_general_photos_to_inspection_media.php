<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: add the new nullable vehicle_inspection_id column
        Schema::table('vehicle_inspection_media', function (Blueprint $table) {
            $table->foreignId('vehicle_inspection_id')
                ->nullable()
                ->after('id')
                ->constrained('vehicle_inspections')
                ->cascadeOnDelete();
        });

        // Step 2: drop existing FK, make entry_id nullable, re-add FK
        Schema::table('vehicle_inspection_media', function (Blueprint $table) {
            $table->dropForeign(['vehicle_inspection_entry_id']);
            $table->unsignedBigInteger('vehicle_inspection_entry_id')->nullable()->change();
            $table->foreign('vehicle_inspection_entry_id')
                ->references('id')
                ->on('vehicle_inspection_entries')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_inspection_media', function (Blueprint $table) {
            $table->dropForeign(['vehicle_inspection_entry_id']);
            $table->unsignedBigInteger('vehicle_inspection_entry_id')->nullable(false)->change();
            $table->foreign('vehicle_inspection_entry_id')
                ->references('id')
                ->on('vehicle_inspection_entries')
                ->cascadeOnDelete();

            $table->dropConstrainedForeignId('vehicle_inspection_id');
        });
    }
};
