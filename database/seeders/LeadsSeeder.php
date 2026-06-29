<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\CostSimulator;
use Illuminate\Support\Str;

class LeadsSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. LEADS VIA FORMULÁRIO DE IMPORTAÇÃO ─────────────────────────────

        // Lead completo — sabe o que quer, tem anúncio
        $c1 = Client::create([
            'name' => 'Miguel Ferreira',
            'email' => 'miguel.ferreira@email.pt',
            'phone' => '912345001',
            'origin' => 'Facebook',
            'is_lead' => true,
            'lead_source' => 'importacao',
        ]);
        FormProposal::create([
            'client_id' => $c1->id,
            'name' => $c1->name,
            'email' => $c1->email,
            'phone' => $c1->phone,
            'source' => 'Facebook',
            'message' => 'Tenho interesse em importar um BMW Série 3 que encontrei num site alemão. Gostaria de saber os custos totais.',
            'ad_option' => 'sim',
            'ad_links' => 'https://www.mobile.de/annonce/12345',
            'brand' => 'BMW',
            'model' => 'Série 3',
            'version' => '318d',
            'fuel' => 'Diesel',
            'gearbox' => 'Automática',
            'year_min' => 2021,
            'km_max' => 60000,
            'color' => 'Preto',
            'budget' => 28000,
            'payment_type' => 'Pronto pagamento',
            'estimated_purchase_date' => now()->addMonths(1),
            'status' => 'novo',
        ]);

        // Lead sem anúncio — quer que encontremos o carro
        $c2 = Client::create([
            'name' => 'Ana Rodrigues',
            'email' => 'ana.rodrigues@gmail.com',
            'phone' => '963456002',
            'origin' => 'Instagram',
            'is_lead' => true,
            'lead_source' => 'importacao',
        ]);
        FormProposal::create([
            'client_id' => $c2->id,
            'name' => $c2->name,
            'email' => $c2->email,
            'phone' => $c2->phone,
            'source' => 'Instagram',
            'message' => 'Quero importar um carro elétrico mas ainda não encontrei nenhum. Podem ajudar-me a encontrar?',
            'ad_option' => 'nao_sei',
            'brand' => 'Tesla',
            'model' => 'Model 3',
            'fuel' => 'Elétrico',
            'gearbox' => 'Automática',
            'year_min' => 2022,
            'km_max' => 40000,
            'budget' => 32000,
            'payment_type' => 'Financiamento',
            'estimated_purchase_date' => now()->addMonths(2),
            'extras' => 'Teto de abrir, jantes 19", piloto automático',
            'status' => 'em_analise',
        ]);

        // Lead mínimo — só preencheu o básico
        $c3 = Client::create([
            'name' => 'João Silva',
            'email' => 'joao.silva@hotmail.com',
            'phone' => '917890003',
            'origin' => 'Outro',
            'is_lead' => true,
            'lead_source' => 'importacao',
        ]);
        FormProposal::create([
            'client_id' => $c3->id,
            'name' => $c3->name,
            'email' => $c3->email,
            'phone' => $c3->phone,
            'source' => 'Google',
            'message' => 'Gostava de receber informação sobre importação.',
            'ad_option' => 'nao_nao',
            'status' => 'novo',
        ]);

        // Lead empresa
        $c4 = Client::create([
            'name' => 'Transportes Mota Lda',
            'email' => 'geral@motald.pt',
            'phone' => '222111004',
            'origin' => 'Olx',
            'client_type' => 'empresa',
            'is_lead' => true,
            'lead_source' => 'importacao',
        ]);
        FormProposal::create([
            'client_id' => $c4->id,
            'name' => $c4->name,
            'email' => $c4->email,
            'phone' => $c4->phone,
            'source' => 'Olx',
            'message' => 'Somos uma empresa de transportes e precisamos de importar 2 carrinhas Mercedes Sprinter. Qual o processo?',
            'ad_option' => 'nao_sei',
            'brand' => 'Mercedes-Benz',
            'model' => 'Sprinter',
            'fuel' => 'Diesel',
            'year_min' => 2020,
            'km_max' => 80000,
            'budget' => 50000,
            'payment_type' => 'Financiamento',
            'estimated_purchase_date' => now()->addMonths(3),
            'status' => 'novo',
        ]);

        // ── 2. LEADS VIA SIMULADOR DE CUSTOS ──────────────────────────────────

        // Simulador com gasolina
        $c5 = Client::create([
            'name' => 'Carlos Mendes',
            'email' => 'carlos.mendes@sapo.pt',
            'phone' => '936789005',
            'origin' => 'Simulador de Custos',
            'is_lead' => true,
            'lead_source' => 'simulador',
        ]);
        CostSimulator::create([
            'client_id' => $c5->id,
            'token' => Str::random(32),
            'brand' => 'Volkswagen',
            'model' => 'Golf',
            'fuel' => 'Gasolina',
            'year' => 2021,
            'cc' => 1500,
            'co2' => 132,
            'pais_matricula' => 'Alemanha',
            'car_value' => 22000,
            'transport' => 850,
            'ipo_cost' => 55,
            'isv_cost' => 3200,
            'imt_cost' => 480,
            'registration_cost' => 55,
            'plates_cost' => 60,
            'commission_cost' => 1200,
            'inspection_commission_cost' => 350,
            'total_cost' => 28250,
            'read' => false,
        ]);

        // Simulador com elétrico
        $c6 = Client::create([
            'name' => 'Mariana Costa',
            'email' => 'mariana.costa@gmail.com',
            'phone' => '911234006',
            'origin' => 'Simulador de Custos',
            'is_lead' => true,
            'lead_source' => 'simulador',
        ]);
        CostSimulator::create([
            'client_id' => $c6->id,
            'token' => Str::random(32),
            'brand' => 'Audi',
            'model' => 'Q4 e-tron',
            'fuel' => 'Elétrico',
            'year' => 2022,
            'autonomia' => 520,
            'pais_matricula' => 'Alemanha',
            'car_value' => 38000,
            'transport' => 950,
            'ipo_cost' => 55,
            'isv_cost' => 0,
            'imt_cost' => 0,
            'registration_cost' => 55,
            'plates_cost' => 60,
            'commission_cost' => 1500,
            'inspection_commission_cost' => 350,
            'total_cost' => 40970,
            'read' => false,
        ]);

        // Simulador com diesel — já lido pelo admin
        $c7 = Client::create([
            'name' => 'Rui Teixeira',
            'email' => 'rui.teixeira@outlook.com',
            'phone' => '965432007',
            'origin' => 'Simulador de Custos',
            'is_lead' => true,
            'lead_source' => 'simulador',
        ]);
        CostSimulator::create([
            'client_id' => $c7->id,
            'token' => Str::random(32),
            'brand' => 'Mercedes-Benz',
            'model' => 'Classe C',
            'fuel' => 'Diesel',
            'year' => 2020,
            'cc' => 1950,
            'co2' => 118,
            'pais_matricula' => 'França',
            'car_value' => 31000,
            'transport' => 1100,
            'ipo_cost' => 55,
            'isv_cost' => 2800,
            'imt_cost' => 620,
            'registration_cost' => 55,
            'plates_cost' => 60,
            'commission_cost' => 1400,
            'inspection_commission_cost' => 350,
            'total_cost' => 37440,
            'read' => true,
        ]);

        // ── 3. LEADS MANUAIS ──────────────────────────────────────────────────

        Client::create([
            'name' => 'Sofia Oliveira',
            'email' => 'sofia.oliveira@gmail.com',
            'phone' => '927654008',
            'origin' => 'Amigo',
            'is_lead' => true,
            'lead_source' => 'manual',
            'observation' => 'Contactou por telefone. Quer um SUV compacto, preferência híbrido. Disponível para reunião na próxima semana.',
        ]);

        Client::create([
            'name' => 'Pedro Nunes',
            'email' => 'pedro.nunes@empresa.pt',
            'phone' => '913456009',
            'origin' => 'StandVirtual',
            'client_type' => 'empresa',
            'is_lead' => true,
            'lead_source' => 'manual',
            'observation' => 'Diretor financeiro. Frota de 5 viaturas prevista para Q1 2027. Seguimento em outubro.',
        ]);

        // ── 4. LEAD VIA RETOMA ────────────────────────────────────────────────

        Client::create([
            'name' => 'Luís Pereira',
            'email' => 'luis.pereira@netcabo.pt',
            'phone' => '934567010',
            'origin' => 'Retomas',
            'is_lead' => true,
            'lead_source' => 'retoma',
            'observation' => 'Quer vender Peugeot 308 SW 2019, 95.000km, Diesel. Pede 14.500€. Fotos recebidas por WhatsApp.',
        ]);

        $this->command->info('✓ 10 leads criados (4 via formulário, 3 via simulador, 2 manuais, 1 retoma)');
    }
}
