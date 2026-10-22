<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;
use App\Models\Client;
use Illuminate\Support\Facades\Storage;
use App\Models\ConvertedProposal;

class ContractService
{
    public static function generateContractPdf($client, ?ConvertedProposal $convertedProposal = null)
    {
        return Pdf::loadView('pdf.contract_service', self::viewData($client, $convertedProposal))->output();
    }

    /** Dados da vista do contrato (separados para se poderem testar sem gerar o PDF). */
    public static function viewData($client, ?ConvertedProposal $convertedProposal = null): array
    {

        $settings = Setting::all()->pluck('value', 'label')->toArray();

        // Assinatura digital do prestador (Definições → assinatura_prestador, disco "public").
        $signatureFile = $settings['assinatura_prestador'] ?? null;
        $src = null;
        if (!empty($signatureFile) && Storage::disk('public')->exists($signatureFile)) {
            $path = Storage::disk('public')->path($signatureFile);
            $mime = mime_content_type($path) ?: 'image/png';
            $src = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
        }

        $logoPath = public_path('img/logo-contrato.png');
        $logo = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $vehicle = $convertedProposal
            ? trim(implode(' ', array_filter([$convertedProposal->brand, $convertedProposal->modelCar, $convertedProposal->version])))
            : '';

        $v3 = $convertedProposal?->v3Vehicle;

        $data = [
            'cliente' => [
                'nome' => $client->name,
                'morada' => $client->address,
                'nif' => $client->vat_number,
                'email' => $client->email,
                'telefone' => $client->phone,
            ],
            'prestador' => [
                'nome' => $settings['name'],
                'nif' => $settings['vat_number'],
                'morada' => 'Oliveira de Azeméis',
                'telefone' => $settings['phone'] ?? null,
                'email' => $settings['email'] ?? null,
            ],
            'iban' => $settings['iban'],
            'pagamentoInicial' => $settings['init_payment'] ?? null,
            'pagamentoFinal' => $settings['final_payment'] ?? null,
            'logo' => $logo,
            'veiculo' => $vehicle !== '' ? [
                'descricao' => $vehicle,
                'ano' => $convertedProposal->year,
                'vin' => $convertedProposal->vin ?: $v3?->vin,
                'cor' => $v3?->color,
                'km' => $convertedProposal->km,
                'combustivel' => $convertedProposal->proposal?->fuel,
                'matricula_origem' => $convertedProposal->matricula_origem,
            ] : null,
            'cotacaoAceite' => $convertedProposal ? self::cotacaoAceite($convertedProposal, $settings) : null,
            'cotacao' => $convertedProposal?->proposal?->proposal_code,
            'dataEmissao' => now()->format('d/m/Y'),
            'signaturePath' => $src,
        ];
        return $data;
    }

    /**
     * Valor a pagar à Izzycar e tranches, como na página da cotação que o cliente vê:
     * total = custos do processo + comissão (sem ISV nem valor do carro), dividido
     * pelas percentagens das Definições (init_payment / final_payment, 50/50 por
     * omissão). As tranches gravadas na cotação convertida não entram aqui.
     * O ISV (estimado) e o valor do carro vão só como dados informativos.
     *
     * @param  array<string, mixed>  $settings
     * @return array{totalIzzycar: float, tranche1: float, tranche2: float, pct1: float, pct2: float, isv: ?float, valorCarro: ?float}
     */
    public static function cotacaoAceite(ConvertedProposal $converted, array $settings = []): array
    {
        $total = $converted->serviceTotal();
        $tranches = ServiceTranches::split($total, $settings);

        return [
            'totalIzzycar' => $total,
            'tranche1' => $tranches['tranche1'],
            'tranche2' => $tranches['tranche2'],
            'pct1' => $tranches['pct1'],
            'pct2' => $tranches['pct2'],
            'isv' => (float) $converted->isv > 0 ? (float) $converted->isv : null,
            'valorCarro' => (float) $converted->valor_carro > 0 ? (float) $converted->valor_carro : null,
        ];
    }
}
