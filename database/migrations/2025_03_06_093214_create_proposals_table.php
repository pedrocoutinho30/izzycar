<?php
// Migration to create the proposals table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateProposalsTable extends Migration
{
    public function up()
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('brand');
            $table->string('model');
            $table->year('year');
            $table->integer('mileage');
            $table->float('engine_capacity');
            $table->float('co2');
            // $table->string('fuel');
            $table->enum('fuel', ['', 'Gasolina', 'Diesel', 'Híbrido Plug-in/Gasolina', 'Híbrido Plug-in/Diesel', 'Elétrico'])->nullable();
            $table->text('notes')->nullable();
            $table->float('value');
            $table->float('transport_cost');
            $table->float('ipo_cost')->nullable();
            $table->float('imt_cost');
            $table->float('registration_cost');
            $table->float('isv_cost');
            $table->float('license_plate_cost');
            $table->float('inspection_commission_cost');
            $table->float('commission_cost');
            $table->integer('proposed_car_mileage');
            $table->string('proposed_car_year_month');
            $table->float('proposed_car_value');
            $table->text('proposed_car_notes')->nullable();
            $table->text('proposed_car_features')->nullable();
            $table->json('images')->nullable(); // To store multiple images
            $table->string('url')->nullable(); // To store the URL of the proposal
            $table->enum('status', ['', 'Pendente', 'Enviada', 'Aprovada', 'Reprovada', 'Sem resposta'])->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('proposals');
    }
}
