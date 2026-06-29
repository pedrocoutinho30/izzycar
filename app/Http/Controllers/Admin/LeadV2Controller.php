<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\CostSimulator;
use Illuminate\Http\Request;

class LeadV2Controller extends Controller
{
    // Leads activas = nova + em_contacto (excluindo fria e perdida)
    private static function activeLeadsQuery()
    {
        return Client::where('is_lead', true)
            ->whereNotIn('lead_status', ['fria', 'perdida']);
    }

    public function index(Request $request)
    {
        $query = Client::where('is_lead', true)->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('lead_source')) {
            $query->where('lead_source', $request->lead_source);
        }

        if ($request->filled('lead_status')) {
            $query->where('lead_status', $request->lead_status);
        }

        $leads = $query->paginate(15)->withQueryString();

        $activeBase = Client::where('is_lead', true)->whereNotIn('lead_status', ['fria', 'perdida']);

        $stats = [
            ['title' => 'A Tratar', 'value' => (clone $activeBase)->count(), 'icon' => 'bi-funnel', 'color' => 'primary'],
            ['title' => 'Novas este Mês', 'value' => Client::where('is_lead', true)->whereMonth('created_at', now()->month)->count(), 'icon' => 'bi-calendar-plus', 'color' => 'success'],
            ['title' => 'Frias / Perdidas', 'value' => Client::where('is_lead', true)->whereIn('lead_status', ['fria', 'perdida'])->count(), 'icon' => 'bi-snow', 'color' => 'secondary'],
            ['title' => 'Via Formulário', 'value' => Client::where('is_lead', true)->where('lead_source', 'importacao')->count(), 'icon' => 'bi-envelope', 'color' => 'warning'],
        ];

        return view('admin.v2.leads.index', compact('leads', 'stats'));
    }

    public function show($id)
    {
        $lead = Client::where('is_lead', true)->with([
            'costSimulators',
            'proposals',
        ])->findOrFail($id);

        $formProposals = FormProposal::where('client_id', $id)->orderBy('created_at', 'desc')->get();
        $simulators = CostSimulator::where('client_id', $id)->orderBy('created_at', 'desc')->get();

        return view('admin.v2.leads.show', compact('lead', 'formProposals', 'simulators'));
    }

    public function updateStatus(Request $request, $id)
    {
        $lead = Client::where('is_lead', true)->findOrFail($id);
        $request->validate(['status' => 'required|in:nova,em_contacto,fria,perdida']);
        $lead->update(['lead_status' => $request->status]);

        return back()->with('success', 'Estado da lead atualizado.');
    }

    public function convert($id)
    {
        $lead = Client::where('is_lead', true)->findOrFail($id);
        $lead->convertToClient();

        return redirect()->route('admin.v2.clients.edit', $id)
            ->with('success', 'Lead convertido em cliente! Complete os dados em falta.');
    }

    public function destroy($id)
    {
        $lead = Client::where('is_lead', true)->findOrFail($id);
        $lead->delete();

        return redirect()->route('admin.v2.leads.index')
            ->with('success', 'Lead eliminado.');
    }
}
