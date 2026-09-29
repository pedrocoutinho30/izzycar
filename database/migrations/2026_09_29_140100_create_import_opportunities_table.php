<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Oportunidades de um Pedido de Importação (form_proposals): cada linha é um
 * carro/anúncio em análise como possível solução para o pedido. Ao eliminar o
 * pedido, as oportunidades são eliminadas em cascata (a app não usa soft
 * deletes).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_proposal_id')->constrained()->cascadeOnDelete();

            // Veículo
            $table->string('brand');
            $table->string('model');
            $table->string('version')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('mileage')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->char('currency', 3)->default('EUR');
            $table->string('fuel', 50)->nullable();
            $table->text('listing_url')->nullable();
            $table->string('vin', 17)->nullable();
            $table->char('country', 2)->nullable();
            $table->string('photo_path')->nullable();
            $table->text('vehicle_notes')->nullable();

            // Vendedor
            $table->string('seller_name')->nullable();
            $table->string('seller_contact')->nullable();

            $table->string('status', 30)->default('por_contactar')->index();

            // Acompanhamento do contacto
            $table->string('contact_method', 30)->nullable();
            $table->string('contact_status', 30)->default('nao_contactado');
            $table->string('contact_used')->nullable();
            $table->dateTime('last_contacted_at')->nullable();
            $table->date('next_followup_at')->nullable();
            $table->text('contact_notes')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_opportunities');
    }
};
