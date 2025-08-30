<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdListing extends Model
{
    protected $fillable = [
        'ad_search_id',
        'external_id',
        'title',
        'price',
        'year',
        'mileage',
        'gearbox',
        'location',
        'published_time',
        'url',

        'teto_de_abrir',
        'teto_panoramico',
        'camara_marcha_atras',
        'camara_360',
        'sensor_estacionamento_dianteiro',
        'sensor_estacionamento_traseiro',
        'assistente_estacionamento',
        'sistema_estacionamento_autonomo',
        'apple_carplay',
        'android_auto',
        'bluetooth',
        'porta_usb',
        'carregador_wireless',
        'sistema_navegacao',
        'estofos_alcantara',
        'estofos_pele',
        'estofos_tecido',
        'banco_condutor_aquecido',
        'banco_passageiro_aquecido',
        'banco_condutor_regulacao_eletrica',
        'bancos_memoria',
        'fecho_central_sem_chave',
        'arranque_sem_chave',
        'cruise_control',
        'cruise_control_adaptativo',
        'cruise_control_predictivo',
        'suspensao_desportiva',
        'retrovisores_retrateis',
        'assistente_angulo_morto',
        'assistente_mudanca_faixa',
        'controlo_proximidade',
        'conducao_autonoma_basica'
    ];

    protected $casts = [
        'teto_de_abrir' => 'boolean',
        'teto_panoramico' => 'boolean',
        'camara_marcha_atras' => 'boolean',
        'camara_360' => 'boolean',
        'sensor_estacionamento_dianteiro' => 'boolean',
        'sensor_estacionamento_traseiro' => 'boolean',
        'assistente_estacionamento' => 'boolean',
        'sistema_estacionamento_autonomo' => 'boolean',
        'apple_carplay' => 'boolean',
        'android_auto' => 'boolean',
        'bluetooth' => 'boolean',
        'porta_usb' => 'boolean',
        'carregador_wireless' => 'boolean',
        'sistema_navegacao' => 'boolean',
        'estofos_alcantara' => 'boolean',
        'estofos_pele' => 'boolean',
        'estofos_tecido' => 'boolean',
        'banco_condutor_aquecido' => 'boolean',
        'banco_passageiro_aquecido' => 'boolean',
        'banco_condutor_regulacao_eletrica' => 'boolean',
        'bancos_memoria' => 'boolean',
        'fecho_central_sem_chave' => 'boolean',
        'arranque_sem_chave' => 'boolean',
        'cruise_control' => 'boolean',
        'cruise_control_adaptativo' => 'boolean',
        'cruise_control_predictivo' => 'boolean',
        'suspensao_desportiva' => 'boolean',
        'retrovisores_retrateis' => 'boolean',
        'assistente_angulo_morto' => 'boolean',
        'assistente_mudanca_faixa' => 'boolean',
        'controlo_proximidade' => 'boolean',
        'conducao_autonoma_basica' => 'boolean',
    ];


    public function search()
    {
        return $this->belongsTo(AdSearch::class, 'ad_search_id');
    }
}
