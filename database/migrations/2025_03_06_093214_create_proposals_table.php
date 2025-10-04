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
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('version')->nullable();
            $table->year('year')->nullable();
            $table->integer('mileage')->nullable();
            $table->float('engine_capacity')->nullable();
            $table->float('co2')->nullable();
            // $table->string('fuel');
            $table->enum('fuel', ['', 'Gasolina', 'Diesel', 'Híbrido Plug-in/Gasolina', 'Híbrido Plug-in/Diesel', 'Elétrico'])->nullable();
            $table->text('notes')->nullable();
            $table->float('value')->nullable();
            $table->float('transport_cost');
            $table->float('ipo_cost')->nullable();
            $table->float('imt_cost')->nullable();
            $table->float('registration_cost')->nullable();
            $table->float('isv_cost')->nullable();
            $table->float('license_plate_cost')->nullable();
            $table->float('inspection_commission_cost')->nullable();
            $table->float('commission_cost')->nullable();
            $table->integer('proposed_car_mileage')->nullable();
            $table->string('proposed_car_year_month')->nullable();
            $table->float('proposed_car_value')->nullable();
            $table->text('proposed_car_notes')->nullable();
            $table->text('proposed_car_features')->nullable();
            $table->json('images')->nullable(); // To store multiple images
            $table->string('url')->nullable(); // To store the URL of the proposal
            $table->enum('status', ['', 'Pendente', 'Enviada', 'Aprovada', 'Reprovada', 'Sem resposta'])->nullable();
            $table->string('other_links')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('proposals');
    }
}
