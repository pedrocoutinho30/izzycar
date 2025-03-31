<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpensesTable extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Empresa', 'Veiculo', 'Outros'])->nullable(false);  // tipo de despesa
            $table->unsignedBigInteger('vehicle_id')->nullable(); // id do veículo
            $table->string('title');  // título
            $table->decimal('amount', 10, 2);  // valor
            $table->enum('vat_rate', ['Sem iva', '6%', '19%', '23%', '25%']);  // taxa de IVA
            $table->date('expense_date');  // data da despesa
            $table->unsignedBigInteger('partner_id')->nullable(); // id do parceiro
            $table->text('observations')->nullable();  // observações
            $table->timestamps();

            // Definindo as chaves estrangeiras
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
}

