<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('v3_vehicle_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('v3_vehicle_id')->constrained('v3_vehicles')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('order_position')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();
        });

        Schema::create('v3_vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('v3_vehicle_id')->constrained('v3_vehicles')->cascadeOnDelete();
            $table->string('tipo', 50)->nullable();    // predefined key from Legalization::DOCUMENTOS
            $table->string('titulo', 255)->nullable(); // custom label for non-predefined docs
            $table->string('nome_original');
            $table->string('caminho');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('v3_vehicle_documents');
        Schema::dropIfExists('v3_vehicle_photos');
    }
};
