<?php

// database/seeders/VehicleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleAttribute;
use Faker\Factory as Faker;

class VehicleAttributesTableSeeder extends Seeder
{
    public function run()
    {
        $attributes = [
            // Equipamento
            ['name' => 'Apple CarPlay', 'key' => 'apple_carplay', 'type' => 'boolean', 'options' => [], 'order' => 1, 'attribute_group' => 'Equipamento'],
            ['name' => 'Android Auto', 'key' => 'android_auto', 'type' => 'boolean', 'options' => [], 'order' => 2, 'attribute_group' => 'Equipamento'],
            ['name' => 'Bluetooth', 'key' => 'bluetooth', 'type' => 'boolean', 'options' => [], 'order' => 3, 'attribute_group' => 'Equipamento'],
            ['name' => 'Porta USB', 'key' => 'porta_usb', 'type' => 'boolean', 'options' => [], 'order' => 4, 'attribute_group' => 'Equipamento'],
            ['name' => 'Carregador de smartphone wireless', 'key' => 'carregador_smartphone_wireless', 'type' => 'boolean', 'options' => [], 'order' => 5, 'attribute_group' => 'Equipamento'],
            ['name' => 'Sistema de navegação', 'key' => 'sistema_navegacao', 'type' => 'boolean', 'options' => [], 'order' => 6, 'attribute_group' => 'Equipamento'],
            ['name' => 'Head-up display', 'key' => 'head_up_display', 'type' => 'boolean', 'options' => [], 'order' => 7, 'attribute_group' => 'Equipamento'],
            ['name' => 'Acesso Internet', 'key' => 'acesso_internet', 'type' => 'boolean', 'options' => [], 'order' => 8, 'attribute_group' => 'Equipamento'],
            ['name' => 'Tipo de Ar Condicionado', 'key' => 'tipo_ar_condicionado', 'type' => 'select', 'options' => ['AC automático', 'AC automático bi-zona', 'AC automático de 3 zonas', 'AC automático de 4 ou mais zonas', 'AC manual'], 'order' => 9, 'attribute_group' => 'Equipamento'],

            ['name' => 'Tecto panorâmico', 'key' => 'tecto_panoramico', 'type' => 'select', 'options' => ["Teto de abrir eléctrico", "Teto de abrir manual", "Teto panorâmico"], 'order' => 10, 'attribute_group' => 'Equipamento'],





            ['name' => 'Estofos', 'key' => 'estofos', 'type' => 'select', 'options' => ['Estofos em alcântara', 'Estofos em meia-pele', 'Estofos em pele', 'Estofos em tecido'], 'order' => 11, 'attribute_group' => 'Equipamento'],
            ['name' => 'Banco do condutor com regulação eléctrica', 'key' => 'banco_condutor_regulacao_electrica', 'type' => 'boolean', 'options' => [], 'order' => 12, 'attribute_group' => 'Equipamento'],
            ['name' => 'Banco do condutor aquecido', 'key' => 'banco_condutor_aquecido', 'type' => 'boolean', 'options' => [], 'order' => 13, 'attribute_group' => 'Equipamento'],
            ['name' => 'Banco do passageiro aquecido', 'key' => 'banco_passageiro_aquecido', 'type' => 'boolean', 'options' => [], 'order' => 14, 'attribute_group' => 'Equipamento'],
            ['name' => 'Bancos dianteiros ventilados', 'key' => 'bancos_dianteiros_ventilados', 'type' => 'boolean', 'options' => [], 'order' => 15, 'attribute_group' => 'Equipamento'],
            ['name' => 'Bancos com memória', 'key' => 'bancos_memoria', 'type' => 'boolean', 'options' => [], 'order' => 16, 'attribute_group' => 'Equipamento'],
            ['name' => 'Bancos traseiros ventilados', 'key' => 'bancos_traseiros_ventilados', 'type' => 'boolean', 'options' => [], 'order' => 17, 'attribute_group' => 'Equipamento'],
            ['name' => 'Apoio de braço dianteiro', 'key' => 'apoio_braco_dianteiro', 'type' => 'boolean', 'options' => [], 'order' => 18, 'attribute_group' => 'Equipamento'],
            ['name' => 'Volante desportivo', 'key' => 'volante_desportivo', 'type' => 'boolean', 'options' => [], 'order' => 19, 'attribute_group' => 'Equipamento'],
            ['name' => 'Volante com patilhas de velocidade', 'key' => 'volante_patilhas', 'type' => 'boolean', 'options' => [], 'order' => 20, 'attribute_group' => 'Equipamento'],
            ['name' => 'Fecho central sem chave', 'key' => 'fecho_central_sem_chave', 'type' => 'boolean', 'options' => [], 'order' => 24, 'attribute_group' => 'Equipamento'],
            ['name' => 'Arranque sem chave', 'key' => 'arranque_sem_chave', 'type' => 'boolean', 'options' => [], 'order' => 25, 'attribute_group' => 'Equipamento'],
            ['name' => 'Desembaciador de pára-brisas', 'key' => 'desembaciador_para_brisa', 'type' => 'boolean', 'options' => [], 'order' => 26, 'attribute_group' => 'Equipamento'],
            ['name' => 'Função de carregamento rápido', 'key' => 'carregamento_rapido', 'type' => 'boolean', 'options' => [], 'order' => 27, 'attribute_group' => 'Equipamento'],
            ['name' => 'Cabo de carregamento', 'key' => 'cabo_carregamento', 'type' => 'boolean', 'options' => [], 'order' => 28, 'attribute_group' => 'Equipamento'],
            ['name' => 'Tipo de Cruise Control', 'key' => 'tipo_cruise_control', 'type' => 'select', 'options' => ['Cruise Control', 'Cruise Control adaptativo', 'Cruise Control Predictivo'], 'order' => 29, 'attribute_group' => 'Equipamento'],
            ['name' => 'Tipo de Faróis', 'key' => 'tipo_farois', 'type' => 'select', 'options' => ['Faróis Bi-Xenon',   'Farol LED'], 'order' => 30, 'attribute_group' => 'Equipamento'],
            ['name' => 'Sensor de estacionamento dianteiro', 'key' => 'sensor_estacionamento_dianteiro', 'type' => 'boolean', 'options' => [], 'order' => 31, 'attribute_group' => 'Equipamento'],
            ['name' => 'Sensor de estacionamento traseiro', 'key' => 'sensor_estacionamento_traseiro', 'type' => 'boolean', 'options' => [], 'order' => 32, 'attribute_group' => 'Equipamento'],
            ['name' => 'Assistente de estacionamento', 'key' => 'assistente_estacionamento', 'type' => 'boolean', 'options' => [], 'order' => 33, 'attribute_group' => 'Equipamento'],
            ['name' => 'Sistema de estacionamento autónomo', 'key' => 'estacionamento_autonomo', 'type' => 'boolean', 'options' => [], 'order' => 34, 'attribute_group' => 'Equipamento'],
            ['name' => 'Câmara 360º', 'key' => 'camara_360', 'type' => 'boolean', 'options' => [], 'order' => 35, 'attribute_group' => 'Equipamento'],
            ['name' => 'Câmara de marcha-atrás', 'key' => 'camara_marcha_atras', 'type' => 'boolean', 'options' => [], 'order' => 36, 'attribute_group' => 'Equipamento'],
            ['name' => 'Retrovisores exteriores eletricamente retrateis', 'key' => 'retrovisores_retrateis', 'type' => 'boolean', 'options' => [], 'order' => 37, 'attribute_group' => 'Equipamento'],
            ['name' => 'Assistente de ângulo morto', 'key' => 'assistente_angulo_morto', 'type' => 'boolean', 'options' => [], 'order' => 38, 'attribute_group' => 'Equipamento'],
            ['name' => 'Assistente de mudança de faixa', 'key' => 'assistente_mudanca_faixa', 'type' => 'boolean', 'options' => [], 'order' => 39, 'attribute_group' => 'Equipamento'],
            ['name' => 'Controlo de proximidade', 'key' => 'controlo_proximidade', 'type' => 'boolean', 'options' => [], 'order' => 40, 'attribute_group' => 'Equipamento'],
            ['name' => 'Condução autónoma básica', 'key' => 'conducao_autonoma_basica', 'type' => 'boolean', 'options' => [], 'order' => 41, 'attribute_group' => 'Equipamento'],
            ['name' => 'Luzes direcionais dinâmicas', 'key' => 'luzes_direccionais_dinamicas', 'type' => 'boolean', 'options' => [], 'order' => 42, 'attribute_group' => 'Equipamento'],
            ['name' => 'Suspensão desportiva', 'key' => 'suspensao_desportiva', 'type' => 'boolean', 'options' => [], 'order' => 44, 'attribute_group' => 'Equipamento'],
            ['name' => 'Suspensão pneumática', 'key' => 'suspensao_pneumatica', 'type' => 'boolean', 'options' => [], 'order' => 45, 'attribute_group' => 'Equipamento'],
            ['name' => 'ISOFIX', 'key' => 'isofix', 'type' => 'boolean', 'options' => [], 'order' => 46, 'attribute_group' => 'Equipamento'],

            // Dados do Veículo
            ['name' => 'IVA dedutível', 'key' => 'iva_dedutivel', 'type' => 'boolean', 'options' => [], 'order' => 1, 'attribute_group' => 'Dados do Veículo'],
            ['name' => 'Classe do veículo', 'key' => 'classe_veiculo', 'type' => 'select', 'options' => ['Classe 1', 'Classe 2', 'Classe 3', 'Classe 4'], 'order' => 2, 'attribute_group' => 'Dados do Veículo'],
            ['name' => 'Bateria', 'key' => 'bateria', 'type' => 'select', 'options' => ['Aluguer', 'Própria'], 'order' => 3, 'attribute_group' => 'Dados do Veículo'],
            ['name' => 'Registo(s)', 'key' => 'registos', 'type' => 'select', 'options' => ['0', '1', '2', '3', '4', '5', '6', 'Mais de 6'], 'order' => 4, 'attribute_group' => 'Dados do Veículo'],
            ['name' => 'Origem', 'key' => 'origem', 'type' => 'text', 'options' => [], 'order' => 5, 'attribute_group' => 'Dados do Veículo'],
            ['name' => 'Livro de Revisões completo', 'key' => 'livro_revisoes', 'type' => 'boolean', 'options' => [], 'order' => 6, 'attribute_group' => 'Dados do Veículo'],
            ['name' => 'Não fumador', 'key' => 'nao_fumador', 'type' => 'boolean', 'options' => [], 'order' => 7, 'attribute_group' => 'Dados do Veículo'],
            ['name' => '2ª Chave', 'key' => 'segunda_chave', 'type' => 'boolean', 'options' => [], 'order' => 8, 'attribute_group' => 'Dados do Veículo'],
            ['name' => 'Clássico', 'key' => 'classico', 'type' => 'boolean', 'options' => [], 'order' => 9, 'attribute_group' => 'Dados do Veículo'],


            //características tecnicas
            ['name' => 'Cilindrada', 'key' => 'cilindrada', 'type' => 'number', 'options' => [], 'order' => 1, 'attribute_group' => 'Características Técnicas'],
            ['name' => 'Potência', 'key' => 'potencia', 'type' => 'number', 'options' => [], 'order' => 4, 'attribute_group' => 'Características Técnicas'],
            ['name' => 'Cor', 'key' => 'cor', 'type' => 'text', 'options' => [], 'order' => 7, 'attribute_group' => 'Características Técnicas'],
            ['name' => 'Tipo de cor', 'key' => 'tipo_cor', 'type' => 'select', 'options' => ['Mate', 'Metalizado', 'Pérola'], 'order' => 8, 'attribute_group' => 'Características Técnicas'],
            ['name' => 'Tipo de Caixa', 'key' => 'tipo_caixa', 'type' => 'select', 'options' => ['Caixa Automática', 'Manual'], 'order' => 9, 'attribute_group' => 'Características Técnicas'],
            ['name' => 'Nº de portas', 'key' => 'numero_portas', 'type' => 'select', 'options' => ['1', '2', '3', '4', '5', '6'], 'order' => 10, 'attribute_group' => 'Características Técnicas'],
            ['name' => 'Lotação', 'key' => 'lotacao', 'type' => 'select', 'options' => ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'], 'order' => 11, 'attribute_group' => 'Características Técnicas'],
            ['name' => 'Tracção', 'key' => 'traccao', 'type' => 'select', 'options' => ['Integral', 'Tracção dianteira', 'Tracção traseira'], 'order' => 12, 'attribute_group' => 'Características Técnicas'],
            ['name' => 'Filtro de partículas', 'key' => 'filtro_particulas', 'type' => 'boolean', 'options' => [], 'order' => 13, 'attribute_group' => 'Características Técnicas']



        ];

        foreach ($attributes as $attr) {
            VehicleAttribute::updateOrCreate(
                ['key' => $attr['key']],
                $attr
            );
        }
    }
}
