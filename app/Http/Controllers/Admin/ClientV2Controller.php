<?php

/**
 * ==================================================================
 * CLIENTS CONTROLLER V2
 * ==================================================================
 * 
 * Controller moderno para gestão de clientes
 * 
 * @author Izzycar Team
 * @version 2.0
 * ==================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientV2Controller extends Controller
{
    /**
     * INDEX - Listagem de clientes
     */
    public function index(Request $request)
    {
        $query = Client::orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->client_type);
        }

        $clients = $query->paginate(10)->withQueryString();

        // Stats
        $stats = [
            ['title' => 'Total Clientes', 'value' => Client::count(), 'icon' => 'people', 'color' => 'primary'],
            ['title' => 'Novos Este Mês', 'value' => Client::whereMonth('created_at', now()->month)->count(), 'icon' => 'person-plus', 'color' => 'success'],
            ['title' => 'Particulares', 'value' => Client::where('client_type', 'Particular')->count(), 'icon' => 'person', 'color' => 'info'],
            ['title' => 'Empresas', 'value' => Client::where('client_type', 'Empresa')->count(), 'icon' => 'building', 'color' => 'warning'],
        ];

        return view('admin.v2.clients.index', compact('clients', 'stats'));
    }

    /**
     * CREATE - Form de criação
     */
    public function create()
    {
        return view('admin.v2.clients.form');
    }

    /**
     * STORE - Guardar novo cliente
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable',
            'identification_number' => 'nullable|string|max:50',
            'validate_identification_number' => 'nullable|date',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'client_type' => 'nullable',
            'origin' => 'nullable|string|max:100',
            'observation' => 'nullable|string',
            'data_processing_consent' => 'nullable|boolean',
            'newsletter_consent' => 'nullable|boolean',
        ]);

        Client::create($validated);

        return redirect()->route('admin.v2.clients.index')
            ->with('success', 'Cliente criado com sucesso!');
    }

    /**
     * EDIT - Form de edição
     */
    public function edit($id)
    {
        $client = Client::findOrFail($id);
        return view('admin.v2.clients.form', compact('client'));
    }

    /**
     * UPDATE - Atualizar cliente
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable',
            'identification_number' => 'nullable|string|max:50',
            'validate_identification_number' => 'nullable|date',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'client_type' => 'nullable',
            'origin' => 'nullable|string|max:100',
            'observation' => 'nullable|string',
            'data_processing_consent' => 'nullable|boolean',
            'newsletter_consent' => 'nullable|boolean',
        ]);

        $client->update($validated);

        return redirect()->route('admin.v2.clients.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    /**
     * DESTROY - Eliminar cliente
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return redirect()->route('admin.v2.clients.index')
            ->with('success', 'Cliente eliminado com sucesso!');
    }
}
