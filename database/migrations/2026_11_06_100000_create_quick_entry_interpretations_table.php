<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Histórico da inserção rápida: a frase, a proposta original (antes de qualquer
 * edição), os dados finais guardados e o registo criado. Não guarda áudio.
 * Serve para melhorar as regras com exemplos reais; não altera regras sozinho.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_entry_interpretations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('original_text');
            $table->string('module', 40)->nullable();
            $table->string('action', 40)->nullable();
            $table->string('interpreter_version', 20);
            // interpretada | confirmada | cancelada
            $table->string('status', 20)->default('interpretada');
            // Proposta tal como o interpretador a devolveu (campos, avisos, opções).
            $table->json('proposal');
            // Campos efetivamente guardados na confirmação.
            $table->json('final_data')->nullable();
            $table->string('record_type', 100)->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'qei_user_created_idx');
            $table->index('status', 'qei_status_idx');
            $table->foreign('user_id', 'qei_user_fk')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_entry_interpretations');
    }
};
