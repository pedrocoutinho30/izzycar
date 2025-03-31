<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();
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
        return view('clients.form', compact('client'));
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
}
