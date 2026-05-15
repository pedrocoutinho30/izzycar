<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_movements', function (Blueprint $table) {
            $table->id();
            $table->morphs('movable'); // movable_type + movable_id
            $table->enum('type', ['income', 'expense']);
            $table->string('category'); // e.g. Venda, Compra Veículo, Despesa
            $table->string('description');
            $table->decimal('amount_gross', 12, 2); // valor bruto (com IVA)
            $table->decimal('vat_rate', 5, 2)->nullable();   // % IVA (ex: 23.00)
            $table->decimal('vat_amount', 12, 2)->default(0); // valor de IVA
            $table->decimal('amount_net', 12, 2);             // valor líquido (sem IVA)
            $table->date('movement_date');
            $table->timestamps();

            $table->index(['movement_date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_movements');
    }
};
