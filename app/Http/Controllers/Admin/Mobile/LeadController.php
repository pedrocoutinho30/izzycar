<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CostSimulator;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Permissions\PermissionService;
use App\Services\LeadService;
use Illuminate\Http\Request;

/**
 * Leads na versão mobile. A lógica é a do desktop (LeadService); aqui só se
 * filtra pelo âmbito do utilizador ("todas" ou "próprias") registo a registo.
 */
class LeadController extends Controller
{
    public function __construct(private LeadService $leads, private PermissionService $permissions)
    {
    }

    public function index(Request $request)
    {
        // Leads perdidas ficam sempre no fim, como no desktop.
        $query = Client::where('is_lead', true)
            ->orderByRaw("lead_status = 'perdida' ASC")
            ->orderBy('created_at', 'desc')
            ->filtrarLeads($request->only(['search', 'lead_source', 'lead_status', 'owner_id']));

        $leads = $this->permissions->applyScope($query, $request->user(), 'leads')
            ->with('owner')
            ->paginate(15)
            ->withQueryString();

        return view('admin.mobile.leads.index', compact('leads'));
    }

    public function show(Request $request, $id)
    {
        $lead = Client::where('is_lead', true)->with([
            'proposals' => fn ($q) => $q->latest(),
            'convertedProposals' => fn ($q) => $q->latest(),
            'owner',
        ])->findOrFail($id);
        abort_unless($this->permissions->allows($request->user(), 'leads', 'view', $lead), 403);

        $formProposals = FormProposal::where('client_id', $lead->id)->withCount('opportunities')->orderBy('created_at', 'desc')->get();
        $simulators    = CostSimulator::where('client_id', $lead->id)->orderBy('created_at', 'desc')->get();
        $activities    = LeadActivity::where('client_id', $lead->id)->with('user')->orderBy('created_at', 'desc')->get();
        // Para marcar as cotações que já deram origem a uma convertida.
        $convertedByProposal = $lead->convertedProposals->whereNotNull('proposal_id')->keyBy('proposal_id');

        return view('admin.mobile.leads.show', [
            'lead' => $lead,
            'formProposals' => $formProposals,
            'simulators' => $simulators,
            'activities' => $activities,
            'convertedByProposal' => $convertedByProposal,
            'statuses' => $this->allowedStatuses($request),
        ]);
    }

    public function status(Request $request, $id)
    {
        $lead = Client::where('is_lead', true)->findOrFail($id);
        abort_unless($this->permissions->allows($request->user(), 'leads', 'update', $lead), 403);

        $request->validate(['status' => 'required|in:' . implode(',', $this->allowedStatuses($request))]);

        $this->leads->changeStatus($lead, $request->status);

        return back(302, [], route('admin.mobile.leads.show', $lead->id))
            ->with('success', 'Estado da lead atualizado.');
    }

    /** "Qualificada" só a equipa atribui: quem edita só as suas (angariador) não pode. */
    private function allowedStatuses(Request $request): array
    {
        if ($this->permissions->scope($request->user(), 'leads', 'update') === 'all') {
            return LeadService::STATUSES;
        }

        return array_values(array_diff(LeadService::STATUSES, ['qualificada']));
    }
}
