<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('vat_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['', 'Homem', 'Mulher', 'Outro'])->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('identification_number')->nullable();
            $table->date('validate_identification_number')->nullable();
            $table->text('address')->nullable();
            $table->text('postal_code')->nullable();
            $table->text('city')->nullable();
            $table->enum('client_type', ['Particular', 'empresa'])->nullable();
            $table->enum('origin', ['', 'Olx', 'StandVirtual', 'Facebook', 'Instagram', 'Amigo', 'Outro'])->nullable();
            $table->text('observation')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
