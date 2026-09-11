<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('converted_proposal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('converted_proposal_id')->constrained()->cascadeOnDelete();
            // 'gerado' - documento produzido e enviado ao cliente pelo sistema (ex: contrato);
            // 'assinado' - documento que o cliente devolveu assinado, carregado manualmente pelo BO.
            $table->enum('tipo', ['gerado', 'assinado']);
            $table->string('nome_original');
            $table->string('caminho');
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('converted_proposal_documents');
    }
};
