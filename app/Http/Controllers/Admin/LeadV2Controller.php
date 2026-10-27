<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\CostSimulator;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadV2Controller extends Controller
{
    public function __construct(private LeadService $leads)
    {
    }

    // Leads activas = nova + em_contacto + qualificada (excluindo fria e perdida)
    private static function activeLeadsQuery()
    {
        return Client::where('is_lead', true)
            ->whereNotIn('lead_status', ['fria', 'perdida']);
    }

    public function create()
    {
        $angariadores = User::role('angariador')->orderBy('name')->get();

        return view('admin.v2.leads.create', compact('angariadores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->leads->rules());

        if ($warning = app(\App\Services\ClientMatcher::class)->duplicateWarning($data['email'] ?? null, $data['phone'] ?? null, $request->boolean('confirm_duplicate'))) {
            return back()->withInput()->withErrors(['duplicate' => $warning['message']])->with('duplicate_url', $warning['url']);
        }

        $lead = $this->leads->create($data, auth()->user());

        return redirect()->route('admin.v2.leads.show', $lead->id)
            ->with('success', 'Lead criado com sucesso.');
    }

    public function index(Request $request)
    {
        // Leads perdidas ficam sempre no fim da listagem, independentemente da ordenação por data.
        $query = Client::where('is_lead', true)
            ->orderByRaw("lead_status = 'perdida' ASC")
            ->orderBy('created_at', 'desc');

        // Filtros (partilhados com a exportação XLSX)
        $query->filtrarLeads($request->only(['search', 'lead_source', 'lead_status', 'owner_id']));

        $query->with('owner');

        $leads = $query->paginate(15)->withQueryString();
        $angariadores = User::role('angariador')->orderBy('name')->get();

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

        return view('admin.v2.leads.index', compact('leads', 'stats', 'angariadores'));
    }

    public function assignOwner(Request $request, $id)
    {
        $lead = Client::findOrFail($id);
        $request->validate(['owner_id' => 'nullable|exists:users,id']);

        $this->leads->assignOwner($lead, $request->owner_id ? (int) $request->owner_id : null);

        return back()->with('success', 'Proprietário da lead actualizado.');
    }

    public function kanban()
    {
        $columns = [
            'nova'        => ['label' => 'Nova',        'color' => 'success',   'icon' => 'bi-circle-fill'],
            'em_contacto' => ['label' => 'Em Contacto', 'color' => 'info',      'icon' => 'bi-telephone-fill'],
            'qualificada' => ['label' => 'Qualificada', 'color' => 'primary',   'icon' => 'bi-patch-check-fill'],
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
            'convertedProposals',
            'owner',
        ])->findOrFail($id);

        $formProposals = FormProposal::where('client_id', $id)->withCount('opportunities')->orderBy('created_at', 'desc')->get();
        $simulators    = CostSimulator::where('client_id', $id)->orderBy('created_at', 'desc')->get();
        $activities    = \App\Models\LeadActivity::where('client_id', $id)->with('user')->orderBy('created_at', 'desc')->get();
        $angariadores  = User::role('angariador')->orderBy('name')->get();

        return view('admin.v2.leads.show', compact('lead', 'formProposals', 'simulators', 'activities', 'angariadores'));
    }

    public function updateStatus(Request $request, $id)
    {
        $lead = Client::where('is_lead', true)->findOrFail($id);
        $request->validate(['status' => 'required|in:' . implode(',', LeadService::STATUSES)]);

        $this->leads->changeStatus($lead, $request->status);

        // Suporta tanto pedidos AJAX (kanban) como form POST normal
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Estado da lead atualizado.');
    }

    public function convert($id)
    {
        $lead = Client::where('is_lead', true)->findOrFail($id);
        $this->leads->convert($lead, auth()->user());

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

        $this->leads->scheduleFollowup($lead, $request->next_followup_at, $request->followup_note);

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

        $this->leads->logActivity($lead, $request->type, $request->title, $request->body);

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
