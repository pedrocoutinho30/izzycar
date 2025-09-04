<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('converted_proposals', function (Blueprint $table) {
            $table->id();

            // Estado
            $table->enum('status', [
                'Iniciada',
                'Negociação Carro',
                'Transporte',
                'IPO',
                'DAV',
                'ISV',
                'IMT',
                'Matriculação',
                'Entrega',
                'Registo automóvel',
                'Concluido',
                'Cancelado'
            ])->default('Iniciada');

            $table->string('url')->nullable();

            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('proposal_id')->nullable();

            // Dados do carro
            $table->string('brand')->nullable();
            $table->string('modelCar')->nullable();
            $table->string('version')->nullable();
            $table->string('year')->nullable();
            $table->integer('km')->nullable();
            $table->string('matricula_origem')->nullable();
            $table->string('matricula_destino')->nullable();

            // Custos e pagamentos
            $table->decimal('custo_inspecao_origem', 10, 2)->nullable();
            $table->boolean('inspecao_origem_pago')->default(false);

            $table->decimal('custo_transporte', 10, 2)->nullable();
            $table->boolean('transporte_pago')->default(false);

            $table->decimal('custo_ipo', 10, 2)->nullable();
            $table->boolean('ipo_pago')->default(false);

            $table->decimal('isv', 10, 2)->nullable();
            $table->boolean('isv_pago')->default(false);

            $table->decimal('custo_imt', 10, 2)->nullable();
            $table->boolean('imt_pago')->default(false);

            $table->decimal('custo_matricula', 10, 2)->nullable();
            $table->boolean('matricula_pago_impressa')->default(false);

            $table->decimal('custo_registo_automovel', 10, 2)->nullable();
            $table->boolean('registo_pago')->default(false);

            // Tranches
            $table->decimal('valor_primeira_tranche', 10, 2)->nullable();
            $table->decimal('valor_segunda_tranche', 10, 2)->nullable();
            $table->boolean('primeira_tranche_pago')->default(false);
            $table->boolean('segunda_tranche_pago')->default(false);

            // Valores do carro e comissão
            $table->decimal('valor_carro', 10, 2)->nullable();
            $table->boolean('carro_pago')->default(false);
            $table->decimal('valor_comissao', 10, 2)->nullable();
            $table->decimal('valor_comissao_final', 10, 2)->nullable();

            // Contatos e observações
            $table->text('contactos_stand')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('iva_dedutivel')->default(false);
            $table->timestamps();

            // Índices
            $table->index('client_id');
            $table->index('proposal_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('converted_proposals');
    }
};
