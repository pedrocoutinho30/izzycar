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
use App\Models\LeadActivity;
use Illuminate\Http\Request;

class ClientV2Controller extends Controller
{
    /**
     * INDEX - Listagem de clientes
     */
    public function index(Request $request)
    {
        $query = Client::where('is_lead', false)->orderBy('created_at', 'desc');

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
            ['title' => 'Total Clientes', 'value' => Client::where('is_lead', false)->count(), 'icon' => 'people', 'color' => 'primary'],
            ['title' => 'Convertidos este Mês', 'value' => Client::where('is_lead', false)->whereMonth('converted_at', now()->month)->count(), 'icon' => 'person-check', 'color' => 'success'],
            ['title' => 'Particulares', 'value' => Client::where('is_lead', false)->where('client_type', 'Particular')->count(), 'icon' => 'person', 'color' => 'info'],
            ['title' => 'Empresas', 'value' => Client::where('is_lead', false)->where('client_type', 'Empresa')->count(), 'icon' => 'building', 'color' => 'warning'],
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
     * SHOW - Detalhe do cliente
     */
    public function show($id)
    {
        $client = Client::with([
            'proposals',
            'sale.v3Vehicle',
            'costSimulators',
            'activities.user',
        ])->findOrFail($id);

        $activities = $client->activities;

        return view('admin.v2.clients.show', compact('client', 'activities'));
    }

    public function saveFollowup(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $request->validate([
            'next_followup_at' => 'required|date|after:now',
            'followup_note'    => 'nullable|string|max:255',
        ]);

        $client->update([
            'next_followup_at' => $request->next_followup_at,
            'followup_note'    => $request->followup_note,
        ]);

        LeadActivity::log(
            $client->id,
            'Follow-up agendado para ' . \Carbon\Carbon::parse($request->next_followup_at)->format('d/m/Y H:i'),
            $request->followup_note ?? '',
            'bi-alarm-fill',
            'warning'
        );

        return back()->with('success', 'Follow-up agendado.');
    }

    public function storeActivity(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $request->validate([
            'type'  => 'required|in:note,call,email,whatsapp,facebook,meeting',
            'title' => 'required|string|max:255',
            'body'  => 'nullable|string|max:2000',
        ]);

        LeadActivity::logManual($client->id, $request->type, $request->title, $request->body ?? '');

        return back()->with('success', 'Atividade registada.');
    }

    /**
     * EDIT - Form de edição
     */
    public function edit($id)
    {
        $client = Client::with(['proposals', 'sale', 'costSimulators'])->findOrFail($id);
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

        return redirect()->route('admin.v2.clients.show', $client->id)
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
