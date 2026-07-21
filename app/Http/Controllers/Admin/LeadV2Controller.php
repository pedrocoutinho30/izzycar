<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\CostSimulator;
use App\Models\LeadActivity;
use Illuminate\Http\Request;

class LeadV2Controller extends Controller
{
    // Leads activas = nova + em_contacto (excluindo fria e perdida)
    private static function activeLeadsQuery()
    {
        return Client::where('is_lead', true)
            ->whereNotIn('lead_status', ['fria', 'perdida']);
    }

    public function create()
    {
        return view('admin.v2.leads.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'origin'      => 'nullable|string|max:255',
            'lead_source' => 'nullable|string|max:255',
            'lead_status' => 'nullable|in:nova,em_contacto,fria,perdida',
            'observation' => 'nullable|string|max:2000',
        ]);

        $lead = Client::create([
            ...$data,
            'is_lead'     => true,
            'lead_source' => $data['lead_source'] ?? 'manual',
            'lead_status' => $data['lead_status'] ?? 'nova',
            'origin'      => $data['origin'] ?? 'Manual BO',
        ]);

        LeadActivity::log(
            $lead->id,
            'Lead criado manualmente',
            'Criado por ' . (auth()->user()->name ?? '—') . '.',
            'bi-plus-circle-fill',
            'success'
        );

        return redirect()->route('admin.v2.leads.show', $lead->id)
            ->with('success', 'Lead criado com sucesso.');
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

        $activeBase   = Client::where('is_lead', true)->whereNotIn('lead_status', ['fria', 'perdida']);
        $followupsHoje = Client::where('is_lead', true)
            ->whereNotNull('next_followup_at')
            ->whereDate('next_followup_at', today())
            ->count();
        $followupsAtraso = Client::where('is_lead', true)
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<', now()->startOfDay())
            ->count();

        $stats = [
            ['title' => 'A Tratar', 'value' => (clone $activeBase)->count(), 'icon' => 'bi-funnel', 'color' => 'primary'],
            ['title' => 'Follow-ups Hoje', 'value' => $followupsHoje, 'icon' => 'bi-alarm', 'color' => 'warning'],
            ['title' => 'Follow-ups em Atraso', 'value' => $followupsAtraso, 'icon' => 'bi-exclamation-circle', 'color' => 'danger'],
            ['title' => 'Frias / Perdidas', 'value' => Client::where('is_lead', true)->whereIn('lead_status', ['fria', 'perdida'])->count(), 'icon' => 'bi-snow', 'color' => 'secondary'],
        ];

        return view('admin.v2.leads.index', compact('leads', 'stats'));
    }

    public function kanban()
    {
        $columns = [
            'nova'        => ['label' => 'Nova',        'color' => 'success',   'icon' => 'bi-circle-fill'],
            'em_contacto' => ['label' => 'Em Contacto', 'color' => 'info',      'icon' => 'bi-telephone-fill'],
            'fria'        => ['label' => 'Fria',        'color' => 'secondary', 'icon' => 'bi-snow'],
            'perdida'     => ['label' => 'Perdida',     'color' => 'danger',    'icon' => 'bi-x-circle-fill'],
        ];

        // Carrega todas as leads agrupadas por estado, com a última actividade
        $leads = Client::where('is_lead', true)
            ->with(['activities' => fn($q) => $q->latest()->limit(1)])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn($l) => $l->lead_status ?? 'nova');

        return view('admin.v2.leads.kanban', compact('columns', 'leads'));
    }

    public function show($id)
    {
        $lead = Client::where('is_lead', true)->with([
            'costSimulators',
            'proposals',
        ])->findOrFail($id);

        $formProposals = FormProposal::where('client_id', $id)->orderBy('created_at', 'desc')->get();
        $simulators    = CostSimulator::where('client_id', $id)->orderBy('created_at', 'desc')->get();
        $activities    = \App\Models\LeadActivity::where('client_id', $id)->with('user')->orderBy('created_at', 'desc')->get();

        return view('admin.v2.leads.show', compact('lead', 'formProposals', 'simulators', 'activities'));
    }

    public function updateStatus(Request $request, $id)
    {
        $lead = Client::where('is_lead', true)->findOrFail($id);
        $request->validate(['status' => 'required|in:nova,em_contacto,fria,perdida']);

        $oldStatus = $lead->lead_status ?? 'nova';
        $lead->update(['lead_status' => $request->status]);

        $statusLabels = [
            'nova'        => 'Nova',
            'em_contacto' => 'Em Contacto',
            'fria'        => 'Fria',
            'perdida'     => 'Perdida',
        ];
        $statusColors = ['nova' => 'success', 'em_contacto' => 'info', 'fria' => 'secondary', 'perdida' => 'danger'];

        LeadActivity::log(
            $lead->id,
            'Estado alterado: ' . ($statusLabels[$oldStatus] ?? $oldStatus) . ' → ' . ($statusLabels[$request->status] ?? $request->status),
            '',
            'bi-arrow-repeat',
            $statusColors[$request->status] ?? 'secondary'
        );

        // Suporta tanto pedidos AJAX (kanban) como form POST normal
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Estado da lead atualizado.');
    }

    public function convert($id)
    {
        $lead = Client::where('is_lead', true)->findOrFail($id);
        $lead->convertToClient();

        LeadActivity::log(
            $lead->id,
            'Lead convertido em cliente',
            'Convertido manualmente pelo utilizador ' . (auth()->user()->name ?? '—') . '.',
            'bi-person-check-fill',
            'success'
        );

        return redirect()->route('admin.v2.clients.edit', $id)
            ->with('success', 'Lead convertido em cliente! Complete os dados em falta.');
    }

    public function saveFollowup(Request $request, $id)
    {
        $lead = Client::findOrFail($id);
        $request->validate([
            'next_followup_at' => 'required|date|after:now',
            'followup_note'    => 'nullable|string|max:255',
        ]);

        $lead->update([
            'next_followup_at' => $request->next_followup_at,
            'followup_note'    => $request->followup_note,
        ]);

        LeadActivity::log(
            $lead->id,
            'Follow-up agendado para ' . \Carbon\Carbon::parse($request->next_followup_at)->format('d/m/Y H:i'),
            $request->followup_note ?? '',
            'bi-alarm-fill',
            'warning'
        );

        return back()->with('success', 'Follow-up agendado.');
    }

    public function storeActivity(Request $request, $id)
    {
        $lead = Client::findOrFail($id);
        $request->validate([
            'type'  => 'required|in:note,call,email,whatsapp,facebook,meeting',
            'title' => 'required|string|max:255',
            'body'  => 'nullable|string|max:2000',
        ]);

        LeadActivity::logManual($lead->id, $request->type, $request->title, $request->body ?? '');

        return back()->with('success', 'Actividade registada.');
    }

    public function destroy($id)
    {
        $lead = Client::where('is_lead', true)->findOrFail($id);
        $lead->delete();

        return redirect()->route('admin.v2.leads.index')
            ->with('success', 'Lead eliminado.');
    }
}
