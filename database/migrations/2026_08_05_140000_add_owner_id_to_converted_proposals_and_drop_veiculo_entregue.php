<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A comissão do angariador vive na cotação convertida (não na venda de
 * veículos, que é um conceito diferente e não relacionado). `owner_id` e
 * `comissao_paga`/`comissao_paga_em` já existiam na tabela (de uma tentativa
 * anterior) — apenas removemos `veiculo_entregue`/`veiculo_entregue_em`,
 * substituídos pelo estado "Entrega" do próprio pipeline (via histórico
 * de estados), que já existe e já é usado pela equipa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->dropColumn(['veiculo_entregue', 'veiculo_entregue_em']);
        });
    }

    public function down(): void
    {
        Schema::table('converted_proposals', function (Blueprint $table) {
            $table->boolean('veiculo_entregue')->default(false);
            $table->date('veiculo_entregue_em')->nullable();
        });
    }
};
