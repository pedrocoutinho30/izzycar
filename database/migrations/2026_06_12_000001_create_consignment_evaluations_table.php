<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('consignment_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            // Veículo
            $table->string('brand');
            $table->string('model');
            $table->string('version')->nullable();
            $table->smallInteger('year');
            $table->unsignedInteger('kilometers');
            $table->string('plate', 20);
            $table->string('fuel', 60);
            $table->string('gearbox', 30);
            $table->unsignedSmallInteger('power')->nullable();
            $table->unsignedSmallInteger('displacement')->nullable();
            $table->string('color', 60)->nullable();
            // Estado
            $table->string('condition', 30);
            $table->text('description')->nullable();
            $table->boolean('has_service_book')->default(false);
            $table->boolean('has_2nd_key')->default(false);
            $table->boolean('has_iuc')->default(false);
            $table->boolean('has_inspection')->default(false);
            // Fotos (paths JSON)
            $table->json('photos')->nullable();
            // Contacto
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email');
            $table->string('location', 100)->nullable();
            $table->unsignedInteger('price_expectation')->nullable();
            // Gestão
            $table->enum('status', ['novo', 'contactado', 'avaliado', 'em_consignacao', 'vendido', 'cancelado'])->default('novo');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consignment_evaluations');
    }
};
