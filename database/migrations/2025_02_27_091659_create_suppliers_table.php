<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('suppliers', function (Blueprint $table) {
        $table->id();
        $table->enum('type', ['', 'Particular', 'Empresa']);
        $table->string('company_name')->nullable();
        $table->string('contact_name');
        $table->text('address')->nullable();
        $table->string('postal_code')->nullable();
        $table->string('city')->nullable();
        $table->string('country')->nullable();
        $table->string('email')->unique();
        $table->string('phone')->nullable();
        $table->string('vat')->nullable();
        $table->string('identification_number')->nullable();
        $table->date('identification_number_validity')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('suppliers');
}

};
