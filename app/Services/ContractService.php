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
            'veiculo' => $vehicle !== '' ? ['descricao' => $vehicle, 'ano' => $convertedProposal->year] : null,
            'cotacao' => $convertedProposal?->proposal?->proposal_code,
            'dataEmissao' => now()->format('d/m/Y'),
            'signaturePath' => $src,
        ];
        return Pdf::loadView('pdf.contract_service', $data)->output();
    }
}
