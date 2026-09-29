<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Histórico de contactos com o vendedor de uma Oportunidade.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_opportunity_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_opportunity_id')->constrained()->cascadeOnDelete();
            $table->dateTime('contacted_at');
            $table->string('method', 30);
            $table->string('type', 30);
            $table->text('message')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            // Nome explícito: o gerado passa o limite de 64 caracteres do MySQL.
            $table->index(['import_opportunity_id', 'contacted_at'], 'import_opp_contacts_opportunity_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_opportunity_contacts');
    }
};
