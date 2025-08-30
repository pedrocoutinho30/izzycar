<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $clients = Client::orderBy('created_at', 'desc')->paginate(10);
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.form');
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'Identification_number' => 'nullable|string|max:255',
            'validate_Identification_number' => 'nullable|date',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'client_type' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
        ]);

        Client::create($validated);
        return redirect()->route('clients.index')->with('success', 'Cliente criado com sucesso!');
    }

    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $sales = $client->sale()->get();
        return view('clients.form', compact('client', 'sales'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'Identification_number' => 'nullable|string|max:255',
            'validate_Identification_number' => 'nullable|date',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'client_type' => 'nullable|string|max:255',
            'origin' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index');
    }

    public function contractService($clientId)
    {
        $client = Client::findOrFail($clientId);

        $pdf = Pdf::loadView('clients.contract_service', [
            'cliente' => [
                'nome' => $client->name,
                'morada' => $client->address,
                'nif' => $client->vat_number,
            ],
            'prestador' => [
                'nome' => 'Pedro Coutinho – Izzycar',
                'nif' => '242414958',
                'morada' => 'Rua da Imprensa Portuguesa, 1 drt 10, Praia do Furadouro, Ovar'
            ],
            'iban' => 'PT50 0000 0000 0000 0000 0000 0',
            'mbway' => '912345678'
        ]);

        return $pdf->download('Contrato_Izzycar.pdf');
        // return view('clients.contract_service', compact('client'));
    }
}
