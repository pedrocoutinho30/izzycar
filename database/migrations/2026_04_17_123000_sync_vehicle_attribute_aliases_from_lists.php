<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $updates = [
            'Cockpit Digital' => ['alemao' => 'Digitales Kombiinstrument'],
            'Sistema de som' => ['alemao' => 'Soundsystem'],
            'Tela sensivel ao toque' => ['field_name_autoscout' => 'Touch screen', 'alemao' => 'Touchscreen'],
            'Porta USB' => ['alemao' => 'USB-Schnittstelle'],
            'Carregador de smartphone wireless' => ['field_name_autoscout' => 'Induction charging for smartphones', 'alemao' => 'Kabelloses Laden fur Handys'],
            'Sistema de navegacao' => ['alemao' => 'Navigationssystem'],
            'Sistema de maos-livres' => ['alemao' => 'Bluetooth-Freisprecheinrichtung'],
            'Porta mala eletrica' => ['alemao' => 'EASY-PACK Heckklappe'],
            'Barras de Tejadilho' => ['field_name_autoscout' => 'Roof rack', 'alemao' => 'Dachreling'],
            'Bola de reboque' => ['field_name_autoscout' => 'Trailer hitch', 'alemao' => 'Anhangerkupplung'],
            'Vidros traseiros escurecidos' => ['alemao' => 'Getonte Scheiben'],
            'Teto panoramico' => ['field_name_autoscout' => 'Panorama roof', 'alemao' => 'Panorama-Schiebedach'],
            'Teto de abrir electrico' => ['field_name_autoscout' => 'Sunroof', 'alemao' => 'Schiebedach'],
            'Bancos eletricamente ajustaveis' => ['alemao' => 'Fahrer-/Beifahrersitz elektrisch'],
            'Bancos com memoria' => ['alemao' => 'Memoryfunktion Fahrer-/Beifahrersitz'],
            'Bancos com apoio lombar' => ['alemao' => 'Lordosenstutze Fahrer/Beifahrer'],
            'Bancos desportivos' => ['alemao' => 'Sportsitze'],
            'Volante aquecido' => ['alemao' => 'Lenkradheizung'],
            'Volante Em Pele' => ['alemao' => 'Lederlenkrad'],
            'Volante multifuncoes' => ['alemao' => 'Lenkrad mit Multifunktion'],
            'Apoio de braco dianteiro' => ['alemao' => 'Armlehne'],
            'AC automatico' => ['alemao' => 'Klimaautomatik'],
            'Fecho central sem chave' => ['alemao' => 'Keyless'],
            'Arranque sem chave' => ['alemao' => 'KEYLESS-GO Start-Funktion'],
            'Sistema detecao de fadiga' => ['field_name_autoscout' => 'Driver drowsiness detection', 'alemao' => 'Aufmerksamkeits-Assistent'],
            'Reconhecimento de sinais de transito' => ['alemao' => 'Verkehrszeichenerkennung'],
            'Aviso de angulo morto' => ['alemao' => 'Totwinkel-Assistent'],
            'SOS' => ['alemao' => 'Notrufsystem'],
            'Farois Diurnos' => ['alemao' => 'Tagfahrlicht'],
            'Farois LED' => ['alemao' => 'LED-Scheinwerfer'],
            'Sistema de Controlo de Traccao' => ['alemao' => 'Antriebs-Schlupfregelung (ASR)'],
            'Sistema Ajuda ao Arranque em Inclinacao' => ['field_name_autoscout' => 'Hill Holder', 'alemao' => 'Berganfahrhilfe'],
            'Retrovisores exteriores eletricamente retrateis' => ['alemao' => 'Aussenspiegel elekt. anklappbar'],
            'Assistente de mudanca de faixa' => ['alemao' => 'Spurhalteassistent'],
            'Controlo de proximidade' => ['alemao' => 'Kollisionswarnung'],
            'Sensor de estacionamento traseiro' => ['alemao' => 'Parksensoren hinten'],
            'Sensor de estacionamento dianteiro' => ['alemao' => 'Parksensoren vorn'],
            'Cruise Control' => ['alemao' => 'Tempomat'],
            'Camara 360o' => ['field_name_autoscout' => '360° camera', 'alemao' => '360°-Kamera'],
            'Camara de marcha-atras' => ['alemao' => 'Ruckfahrkamera'],
            'Cruise Control adaptativo' => ['alemao' => 'DISTRONIC'],
            'Assistente de estacionamento' => ['alemao' => 'Einparkhilfe'],
            'Sensores de Chuva' => ['alemao' => 'Regensensor'],
            'Controlo de estabilidade eletronico' => ['alemao' => 'Elektr. Stabilitatsprogramm ESP'],
            'Direcao hidraulica' => ['alemao' => 'Servolenkung'],
            'Sistema de monotorizacao da pressao dos pneus' => ['alemao' => 'Reifendruckkontrolle'],
            'ABS' => ['alemao' => 'Antiblockiersystem ABS'],
            'Airbag do Condutor' => ['alemao' => 'Fahrerairbag'],
            'Airbag de Passageiros' => ['alemao' => 'Beifahrerairbag'],
            'Airbags Laterais' => ['alemao' => 'Seitenairbag'],
            'ISOFIX' => ['alemao' => 'Isofix'],
            'Suspensao desportiva' => ['alemao' => 'Sportfahrwerk'],
            'Estofos em meia-pele' => ['field_name_autoscout' => 'Part leather', 'alemao' => 'Teilleder'],
            'Estofos em tecido' => ['alemao' => 'Stoff'],
        ];

        foreach ($updates as $name => $payload) {
            $query = DB::table('vehicle_attributes');

            if ($query->where('name', $name)->exists()) {
                $query->where('name', $name)->update($payload);
                continue;
            }

            $normalizedName = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name) ?: $name;
            DB::table('vehicle_attributes')
                ->get(['id', 'name'])
                ->first(function ($attribute) use ($normalizedName, $payload) {
                    $attributeName = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $attribute->name) ?: $attribute->name;

                    if ($attributeName !== $normalizedName) {
                        return false;
                    }

                    DB::table('vehicle_attributes')->where('id', $attribute->id)->update($payload);
                    return true;
                });
        }

        $maxOrder = (int) DB::table('vehicle_attributes')->max('order');

        $newAttributes = [
            [
                'name' => 'Vidros eletricos',
                'alemao' => 'Fensterheber elektrisch',
                'key' => 'vidros_eletricos',
                'type' => 'boolean',
                'attribute_group' => 'Conforto e Outros Equipamentos',
                'field_name_autoscout' => 'Power windows',
            ],
            [
                'name' => 'Sensor de luz',
                'alemao' => 'Lichtsensor',
                'key' => 'sensor_luz',
                'type' => 'boolean',
                'attribute_group' => 'Electrónica e Assistência à Condução',
                'field_name_autoscout' => 'Light sensor',
            ],
            [
                'name' => 'Radio',
                'alemao' => 'Radio',
                'key' => 'radio',
                'type' => 'boolean',
                'attribute_group' => 'Áudio e Multimédia',
                'field_name_autoscout' => 'Radio',
            ],
            [
                'name' => 'Radio digital',
                'alemao' => 'Digitaler Radioempfang DAB',
                'key' => 'radio_digital',
                'type' => 'boolean',
                'attribute_group' => 'Áudio e Multimédia',
                'field_name_autoscout' => 'Digital radio',
            ],
            [
                'name' => 'Imobilizador',
                'alemao' => 'Wegfahrsperre',
                'key' => 'imobilizador',
                'type' => 'boolean',
                'attribute_group' => 'Segurança',
                'field_name_autoscout' => 'Immobilizer',
            ],
            [
                'name' => 'Jantes de liga leve',
                'alemao' => 'Leichtmetallfelgen',
                'key' => 'jantes_liga_leve',
                'type' => 'boolean',
                'attribute_group' => 'Conforto e Outros Equipamentos',
                'field_name_autoscout' => 'Alloy wheels',
            ],
            [
                'name' => 'Pneus para todas as estacoes',
                'alemao' => 'Allwetterreifen',
                'key' => 'pneus_todas_estacoes',
                'type' => 'boolean',
                'attribute_group' => 'Conforto e Outros Equipamentos',
                'field_name_autoscout' => 'All season tyres',
            ],
            [
                'name' => 'Espelho interior anti-encandeamento automatico',
                'alemao' => 'Automatisch abblendender Innenspiegel',
                'key' => 'espelho_interior_auto_escurecimento',
                'type' => 'boolean',
                'attribute_group' => 'Conforto e Outros Equipamentos',
                'field_name_autoscout' => 'Automatically dimming interior mirror',
            ],
            [
                'name' => 'Controlo por voz',
                'alemao' => 'Spracheingabesystem',
                'key' => 'controlo_por_voz',
                'type' => 'boolean',
                'attribute_group' => 'Áudio e Multimédia',
                'field_name_autoscout' => 'Voice Control',
            ],
            [
                'name' => 'Kit reparacao de pneus',
                'alemao' => 'Reifenpannenset',
                'key' => 'kit_reparacao_pneus',
                'type' => 'boolean',
                'attribute_group' => 'Conforto e Outros Equipamentos',
                'field_name_autoscout' => 'Emergency tyre repair kit',
            ],
        ];

        foreach ($newAttributes as $index => $attribute) {
            DB::table('vehicle_attributes')->updateOrInsert(
                ['key' => $attribute['key']],
                array_merge($attribute, [
                    'options' => null,
                    'order' => $maxOrder + $index + 1,
                    'field_name_mobile' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        // No-op: this migration enriches data and adds a small curated attribute set.
    }
};