<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\V3Vehicle;
use App\Services\ContractService;
use App\Models\Setting;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ConvertedProposalContractTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_backoffice_can_view_the_contract_as_pdf(): void
    {
        foreach (['name' => 'Izzycar', 'vat_number' => '999999999', 'address' => 'Lisboa', 'iban' => 'PT50', 'phone' => '928459346'] as $label => $value) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }
        $client = Client::create(['name' => 'Maria', 'is_lead' => false, 'lead_status' => 'nova']);
        $converted = ConvertedProposal::forceCreate(['client_id' => $client->id, 'brand' => 'Audi', 'modelCar' => 'A4']);

        $response = $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v2.converted-proposals.contract', $converted->id))
            ->assertOk();

        $this->assertStringStartsWith('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    private function settings(): void
    {
        foreach (['name' => 'Izzycar', 'vat_number' => '999999999', 'address' => 'Lisboa', 'iban' => 'PT50', 'phone' => '928459346'] as $label => $value) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }
    }

    private function converted(array $extra = []): ConvertedProposal
    {
        $client = Client::create(['name' => 'Maria', 'is_lead' => false, 'lead_status' => 'nova']);

        return ConvertedProposal::forceCreate($extra + [
            'client_id' => $client->id, 'brand' => 'Opel', 'modelCar' => 'Astra', 'year' => '2024', 'status' => 'Iniciada',
            'custo_transporte' => 700, 'custo_ipo' => 50, 'custo_imt' => 100, 'custo_matricula' => 40, 'custo_registo_automovel' => 60,
            'valor_comissao' => 1050, 'isv' => 3500, 'valor_carro' => 24700,
            'valor_primeira_tranche' => 1000, 'valor_segunda_tranche' => 1000,
        ]);
    }

    public function test_total_is_the_service_cost_split_by_the_settings_percentages(): void
    {
        // As tranches gravadas (50/50, com 1.272,50 €) não contam para o contrato.
        $converted = $this->converted(['valor_primeira_tranche' => 1272.5, 'valor_segunda_tranche' => 1272.5]);

        $cotacao = ContractService::cotacaoAceite($converted, ['init_payment' => '60%', 'final_payment' => '40%']);

        $this->assertSame(2000.0, $cotacao['totalIzzycar']);
        $this->assertSame(1200.0, $cotacao['tranche1']);
        $this->assertSame(800.0, $cotacao['tranche2']);
        $this->assertSame(60.0, $cotacao['pct1']);
        $this->assertSame(40.0, $cotacao['pct2']);
        $this->assertSame(3500.0, $cotacao['isv']);
        $this->assertSame(24700.0, $cotacao['valorCarro']);
    }

    public function test_without_percentage_settings_the_split_is_fifty_fifty(): void
    {
        $cotacao = ContractService::cotacaoAceite($this->converted());

        $this->assertSame(1000.0, $cotacao['tranche1']);
        $this->assertSame(1000.0, $cotacao['tranche2']);
    }

    public function test_contract_shows_vin_total_tranches_isv_and_car_value(): void
    {
        $this->settings();
        foreach (['init_payment' => '60%', 'final_payment' => '40%'] as $label => $value) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }
        $converted = $this->converted();
        $vehicle = V3Vehicle::forceCreate(['brand' => 'Opel', 'model' => 'Astra', 'vin' => 'VXKFRHPY9R1051642', 'color' => 'Cinzento']);
        $converted->update(['v3_vehicle_id' => $vehicle->id]);

        $html = view('pdf.contract_service', ContractService::viewData($converted->client, $converted->refresh()))->render();

        $this->assertStringContainsString('VXKFRHPY9R1051642', $html);
        $this->assertStringContainsString('Cinzento', $html);
        $this->assertStringContainsString('Total a pagar à Izzycar', $html);
        $this->assertStringContainsString('1.ª tranche: 1.200,00 €</b> (60%)', $html);
        $this->assertStringContainsString('2.ª tranche: 800,00 €</b> (40%)', $html);
        $this->assertStringContainsString('2.000,00 € em duas tranches', $html);

        // ISV estimado e valor do carro: dados informativos, cada um uma só vez e só na secção 6.
        $this->assertSame(1, substr_count($html, '3.500,00 €'));
        $this->assertStringContainsString('pago diretamente às Finanças', $html);
        $this->assertSame(1, substr_count($html, '24.700,00 €'));
        $this->assertStringContainsString('Valor de aquisição do automóvel', $html);
        $this->assertStringNotContainsString('com o valor de aquisição de', $html);

        // Sem a lista de custos.
        foreach (['Homologação (IMT)', 'Comissão de serviço Izzycar'] as $detalhe) {
            $this->assertStringNotContainsString($detalhe, $html);
        }
    }

    public function test_contract_hides_the_vin_when_it_is_not_filled_in(): void
    {
        $this->settings();
        $converted = $this->converted();

        $html = view('pdf.contract_service', ContractService::viewData($converted->client, $converted))->render();

        $this->assertStringNotContainsString('com o VIN', $html);
        $this->assertMatchesRegularExpression('/Opel Astra \(2024\)\./', preg_replace('/\s+/', ' ', strip_tags($html)));
    }

    public function test_the_vin_filled_in_on_the_converted_quote_is_used_and_normalised(): void
    {
        $this->settings();
        $converted = $this->converted(['vin' => ' vxkfrhpy 9r1051642 ']);
        $vehicle = V3Vehicle::forceCreate(['brand' => 'Opel', 'model' => 'Astra', 'vin' => 'OUTROVINDAVIATURA1']);
        $converted->update(['v3_vehicle_id' => $vehicle->id]);

        $this->assertSame('VXKFRHPY9R1051642', $converted->refresh()->vin);

        $html = view('pdf.contract_service', ContractService::viewData($converted->client, $converted))->render();

        $this->assertStringContainsString('com o VIN <b>VXKFRHPY9R1051642</b>', $html);
        $this->assertStringNotContainsString('OUTROVIN', $html);
    }

    public function test_an_empty_vin_is_stored_as_null(): void
    {
        $this->assertNull($this->converted(['vin' => '   '])->refresh()->vin);
    }

    public function test_the_vin_can_be_saved_from_the_backoffice_form(): void
    {
        $converted = $this->converted();

        $this->actingAs($this->backofficeUser('admin'))
            ->put(route('admin.v2.converted-proposals.update', $converted->id), [
                'client_id' => $converted->client_id, 'brand' => 'Opel', 'modelCar' => 'Astra', 'vin' => 'vxkfrhpy9r1051642',
            ])
            ->assertRedirect();

        $this->assertSame('VXKFRHPY9R1051642', $converted->refresh()->vin);
    }
}
