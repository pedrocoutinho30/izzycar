<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function contractService($clientId)
    {
        $client = Client::findOrFail($clientId);

        $settings = Setting::all()->pluck('value', 'label')->toArray();

        $pdf = Pdf::loadView('clients.contract_service', [
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
        ]);

        // return $pdf->download('Contrato_Izzycar_' . $client->name . '.pdf');
        return view('clients.contract_service', compact('client'));
    }
}
