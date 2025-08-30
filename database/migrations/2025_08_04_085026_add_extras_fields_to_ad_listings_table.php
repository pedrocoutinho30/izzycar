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
       Schema::table('ad_listings', function (Blueprint $table) {
        $table->boolean('teto_de_abrir')->nullable();
        $table->boolean('teto_panoramico')->nullable();
        $table->boolean('camara_marcha_atras')->nullable();
        $table->boolean('camara_360')->nullable();
        $table->boolean('sensor_estacionamento_dianteiro')->nullable();
        $table->boolean('sensor_estacionamento_traseiro')->nullable();
        $table->boolean('assistente_estacionamento')->nullable();
        $table->boolean('sistema_estacionamento_autonomo')->nullable();
        $table->boolean('apple_carplay')->nullable();
        $table->boolean('android_auto')->nullable();
        $table->boolean('bluetooth')->nullable();
        $table->boolean('porta_usb')->nullable();
        $table->boolean('carregador_wireless')->nullable();
        $table->boolean('sistema_navegacao')->nullable();
        $table->boolean('estofos_alcantara')->nullable();
        $table->boolean('estofos_pele')->nullable();
        $table->boolean('estofos_tecido')->nullable();
        $table->boolean('banco_condutor_aquecido')->nullable();
        $table->boolean('banco_passageiro_aquecido')->nullable();
        $table->boolean('banco_condutor_regulacao_eletrica')->nullable();
        $table->boolean('bancos_memoria')->nullable();
        $table->boolean('fecho_central_sem_chave')->nullable();
        $table->boolean('arranque_sem_chave')->nullable();
        $table->boolean('cruise_control')->nullable();
        $table->boolean('cruise_control_adaptativo')->nullable();
        $table->boolean('cruise_control_predictivo')->nullable();
        $table->boolean('suspensao_desportiva')->nullable();
        $table->boolean('retrovisores_retrateis')->nullable();
        $table->boolean('assistente_angulo_morto')->nullable();
        $table->boolean('assistente_mudanca_faixa')->nullable();
        $table->boolean('controlo_proximidade')->nullable();
        $table->boolean('conducao_autonoma_basica')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_listings', function (Blueprint $table) {
            $table->dropColumn([
            'teto_de_abrir', 'teto_panoramico', 'camara_marcha_atras', 'camara_360',
            'sensor_estacionamento_dianteiro', 'sensor_estacionamento_traseiro',
            'assistente_estacionamento', 'sistema_estacionamento_autonomo',
            'apple_carplay', 'android_auto', 'bluetooth', 'porta_usb',
            'carregador_wireless', 'sistema_navegacao', 'estofos_alcantara',
            'estofos_pele', 'estofos_tecido', 'banco_condutor_aquecido',
            'banco_passageiro_aquecido', 'banco_condutor_regulacao_eletrica',
            'bancos_memoria', 'fecho_central_sem_chave', 'arranque_sem_chave',
            'cruise_control', 'cruise_control_adaptativo', 'cruise_control_predictivo',
            'suspensao_desportiva', 'retrovisores_retrateis', 'assistente_angulo_morto',
            'assistente_mudanca_faixa', 'controlo_proximidade', 'conducao_autonoma_basica'
        ]);
        });
    }
};
