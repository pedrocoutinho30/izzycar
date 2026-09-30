<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vendedores: empresas/stands a quem a IzzyCar compra (ou tenta comprar)
 * carros. As pessoas ficam em seller_contacts — um vendedor, vários contactos.
 * Sem soft deletes (padrão do projeto): um vendedor com histórico é
 * desativado, não eliminado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('website')->nullable();
            $table->char('country', 2)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
