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
        Schema::create('transport_quotes', function (Blueprint $table) {
            $table->id();
            
            // Dados do veículo
            $table->string('brand');
            $table->string('model');
            
            // Origem
            $table->string('origin_city');
            $table->string('origin_country');
            $table->string('origin_postal_code')->nullable();
            $table->decimal('origin_latitude', 10, 7)->nullable();
            $table->decimal('origin_longitude', 10, 7)->nullable();
            
            // Destino (fixo mas guardado para histórico)
            $table->string('destination_city')->default('Oliveira de Azeméis');
            $table->string('destination_country')->default('Portugal');
            $table->decimal('destination_latitude', 10, 7)->nullable();
            $table->decimal('destination_longitude', 10, 7)->nullable();
            
            // Transportadora
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            
            // Valores
            $table->decimal('price', 10, 2);
            
            // Dados adicionais
            $table->date('quote_date')->default(now());
            $table->integer('estimated_delivery_days')->nullable();
            $table->text('observations')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_quotes');
    }
};
