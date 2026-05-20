<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('v3_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique()->nullable();

            // ── Informação Geral ───────────────────────────────────
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('version', 100)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('fuel', 50)->nullable();
            $table->unsignedInteger('kilometers')->nullable();
            $table->unsignedSmallInteger('power')->nullable();           // CV
            $table->unsignedSmallInteger('cylinder_capacity')->nullable(); // CC
            $table->string('color', 50)->nullable();
            $table->string('vin', 17)->nullable();
            $table->string('registration', 20)->nullable();
            $table->date('manufacture_date')->nullable();
            $table->date('register_date')->nullable();
            $table->date('available_to_sell_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('show_online')->default(false);
            $table->string('status', 20)->default('em_stock'); // em_stock | vendido | reservado

            // ── Dados Compra ───────────────────────────────────────
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('purchase_type', 20)->nullable(); // Geral | Margem | Sem Iva
            $table->decimal('purchase_vat_rate', 5, 2)->nullable();
            $table->decimal('purchase_vat_paid', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('v3_vehicles');
    }
};
