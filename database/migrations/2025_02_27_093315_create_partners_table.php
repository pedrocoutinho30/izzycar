<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_partners_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');  // Nome do parceiro
            $table->string('phone')->nullable();  // Telemóvel
            $table->text('address')->nullable();  // Morada
            $table->string('postal_code')->nullable();  // Código Postal
            $table->string('city')->nullable();  // Cidade
            $table->string('country')->nullable();  // País
            $table->string('email')->nullable();  // Email
            $table->string('vat')->nullable();  // NIF
            $table->string('contact_name')->nullable();  // Nome de contacto
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('partners');
    }
}
