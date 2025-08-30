<?php
// database/migrations/xxxx_xx_xx_create_vehicles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->enum('business_type', ['', 'Novo', 'Usado', 'Semi-novo'])->nullable();
            $table->string('brand');
            $table->string('model');
            $table->string('version')->nullable();
            $table->string('year')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->date('purchase_date')->nullable();
            $table->enum('purchase_type', ['', 'Margem', 'Geral', 'Sem Iva'])->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('sell_price', 10, 2)->nullable();
            $table->integer('kilometers')->nullable();
            $table->integer('power')->nullable();
            $table->integer('cylinder_capacity')->nullable();
            $table->boolean('consigned_vehicle')->default(false);
            $table->enum('fuel', ['', 'Diesel', 'Gasolina', 'Elétrico', 'Hibrido-plug-in/Gasolina', 'Hibrido-plug-in/Diesel', 'Outro'])->nullable();
            $table->boolean('sold')->default(false);
            $table->date('sale_date')->nullable();
            $table->string('vin')->unique();
            $table->date('manufacture_date')->nullable();
            $table->date('register_date')->nullable();
            $table->date('available_to_sell_date')->nullable();
           
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}
