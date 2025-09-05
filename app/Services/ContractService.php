<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;
use App\Models\Client;

class ContractService
{
    public static function generateContractPdf($client)
    {

        $settings = Setting::all()->pluck('value', 'label')->toArray();

        $path = storage_path('app/public/' . ($settings['assinatura_prestador'] ?? ''));

        if (file_exists($path)) {
            $signature = base64_encode(file_get_contents($path));
            $src = "data:image/png;base64," . $signature;
        }
        $data = [
            'cliente' => [
                'nome' => $client->name,
                'morada' => $client->address,
                'nif' => $client->vat_number,
            ],
            'prestador' => [
                'nome' => $settings['name'],
                'nif' => $settings['vat_number'],
                'morada' => $settings['address'],
            ],
            'iban' => $settings['iban'],
            'mbway' => $settings['phone'],
            'signaturePath' => $src ?? null,
        ];
        return Pdf::loadView('pdf.contract_service', $data)->output();
    }
}
