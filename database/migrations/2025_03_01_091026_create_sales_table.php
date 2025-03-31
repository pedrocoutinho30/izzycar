<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->date('sale_date');
            $table->decimal('sale_price', 10, 2);
            $table->enum('vat_rate', ['Sem iva', '23%']);
            $table->enum('payment_method', ['Financiamento', 'Pronto pagamento']);
            $table->text('observation')->nullable();
            $table->decimal('gross_margin', 10, 2)->nullable();
            $table->integer('gross_profitability')->nullable();
            $table->decimal('net_margin', 10, 2)->nullable();
            $table->integer('net_profitability')->nullable();
            $table->decimal('vat_paid', 10, 2)->nullable();
            $table->decimal('vat_deducible_purchase', 10, 2)->nullable();
            $table->decimal('vat_settle_sale', 10, 2)->nullable();
            $table->decimal('totalCost', 10, 2)->nullable();
            $table->decimal('totalExpenses', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
