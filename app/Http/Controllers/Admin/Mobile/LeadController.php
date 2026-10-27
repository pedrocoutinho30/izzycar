<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\CostSimulator;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\User;
use App\Permissions\PermissionService;
use App\Services\ClientMatcher;
use App\Services\LeadService;
use Illuminate\Support\Facades\Route;
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

        $canCreate = $this->permissions->allows($request->user(), 'leads', 'create');

        return view('admin.mobile.leads.index', compact('leads', 'canCreate'));
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
            'canUpdate' => $this->permissions->allows($request->user(), 'leads', 'update', $lead),
            'canAssignOwner' => $this->canAssignOwner($request),
            'canDelete' => $this->permissions->allows($request->user(), 'leads', 'delete'),
            'owners' => $this->canAssignOwner($request) ? $this->owners() : collect(),
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.mobile.leads.create', [
            'owners' => $this->canAssignOwner($request) ? $this->owners() : collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->leads->rules());

        // Quem não pode atribuir donos não escolhe o dono da lead.
        if (!$this->canAssignOwner($request)) {
            unset($data['owner_id']);
        }

        // Mesmo aviso de duplicados do desktop (LeadV2Controller::store).
        if ($warning = app(ClientMatcher::class)->duplicateWarning($data['email'] ?? null, $data['phone'] ?? null, $request->boolean('confirm_duplicate'))) {
            return back()->withInput()->withErrors(['duplicate' => $warning['message']]);
        }

        $lead = $this->leads->create($data, $request->user());

        return redirect()->route('admin.mobile.leads.show', $lead->id)
            ->with('success', 'Lead criado com sucesso.');
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

    public function convert(Request $request, $id)
    {
        $lead = Client::where('is_lead', true)->findOrFail($id);
        $this->authorizeUpdate($request, $lead);

        $this->leads->convert($lead, $request->user());

        $destino = Route::has('admin.mobile.clients.show')
            ? route('admin.mobile.clients.show', $id)
            : route('admin.mobile.leads.show', $id);

        return redirect($destino)->with('success', 'Lead convertido em cliente! Complete os dados em falta.');
    }

    public function followup(Request $request, $id)
    {
        $lead = Client::findOrFail($id);
        $this->authorizeUpdate($request, $lead);

        $request->validate([
            'next_followup_at' => 'required|date|after:now',
            'followup_note'    => 'nullable|string|max:255',
        ]);

        $this->leads->scheduleFollowup($lead, $request->next_followup_at, $request->followup_note);

        return back()->with('success', 'Follow-up agendado.');
    }

    public function activity(Request $request, $id)
    {
        $lead = Client::findOrFail($id);
        $this->authorizeUpdate($request, $lead);

        $request->validate([
            'type'  => 'required|in:note,call,email,whatsapp,facebook,meeting',
            'title' => 'required|string|max:255',
            'body'  => 'nullable|string|max:2000',
        ]);

        $this->leads->logActivity($lead, $request->type, $request->title, $request->body);

        return back()->with('success', 'Actividade registada.');
    }

    public function owner(Request $request, $id)
    {
        $lead = Client::findOrFail($id);
        abort_unless($this->canAssignOwner($request), 403);

        $request->validate(['owner_id' => 'nullable|exists:users,id']);

        $this->leads->assignOwner($lead, $request->owner_id ? (int) $request->owner_id : null);

        return back()->with('success', 'Proprietário da lead actualizado.');
    }

    public function destroy(Request $request, $id)
    {
        $lead = Client::where('is_lead', true)->findOrFail($id);
        abort_unless($this->permissions->allows($request->user(), 'leads', 'delete'), 403);

        $lead->delete();

        return redirect()->route('admin.mobile.leads.index')->with('success', 'Lead eliminado.');
    }

    /** Rotas "próprias": o dono confere-se por registo, como na ficha. */
    private function authorizeUpdate(Request $request, Client $lead): void
    {
        abort_unless($this->permissions->allows($request->user(), 'leads', 'update', $lead), 403);
    }

    /** Atribuir dono exige editar todas as leads (update.all), como no desktop. */
    private function canAssignOwner(Request $request): bool
    {
        return $this->permissions->allows($request->user(), 'leads', 'update', null, 'all');
    }

    private function owners()
    {
        return User::role('angariador')->orderBy('name')->get();
    }
}
