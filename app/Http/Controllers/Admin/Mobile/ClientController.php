<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\CostSimulator;
use App\Models\FormProposal;
use App\Models\LeadActivity;
use App\Models\Proposal;
use App\Permissions\PermissionService;
use App\Services\ClientService;
use App\Services\ConvertedProposalService;
use App\Services\LeadService;
use Illuminate\Http\Request;

/**
 * Clientes na versão mobile. A lógica é a do desktop (ClientService; o
 * follow-up e as atividades são os das leads, LeadService). Aqui só se filtra
 * pelo âmbito do utilizador ("todos" ou "próprios") registo a registo, para as
 * regras valerem mesmo com PERMISSIONS_ENFORCE desligado.
 *
 * Como no desktop, as rotas com {id} também abrem leads: a ficha e a edição
 * de uma lead levam à ficha de lead mobile; editar, follow-up e atividade
 * funcionam, mas exigem também poder editar a lead.
 */
class ClientController extends Controller
{
    public function __construct(
        private ClientService $clients,
        private LeadService $leads,
        private PermissionService $permissions,
    ) {
    }

    public function index(Request $request)
    {
        abort_unless($this->permissions->allows($request->user(), 'clients', 'view'), 403);

        $query = Client::where('is_lead', false)
            ->orderBy('created_at', 'desc')
            ->filtrarClientes($request->only(['search', 'client_type']));

        $clients = $this->permissions->applyScope($query, $request->user(), 'clients')
            ->paginate(15)
            ->withQueryString();

        return view('admin.mobile.clients.index', [
            'clients' => $clients,
            'canCreate' => $this->canCreate($request),
        ]);
    }

    public function show(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        if ($client->is_lead) {
            return redirect()->route('admin.mobile.leads.show', $client->id);
        }
        abort_unless($this->permissions->allows($request->user(), 'clients', 'view', $client), 403);

        $client->load([
            'proposals' => fn ($q) => $q->latest(),
            'convertedProposals' => fn ($q) => $q->latest(),
            'carCandidates',
            'vehicles.legalization',
            'sale.v3Vehicle.legalization',
            'owner',
        ]);

        $user = $request->user();
        $canUpdate = $this->canUpdate($request, $client);
        $canOpenSimulators = $this->permissions->allows($user, 'cost-simulators', 'view');

        return view('admin.mobile.clients.show', [
            'client' => $client,
            'formProposals' => FormProposal::where('client_id', $client->id)->withCount('opportunities')->orderBy('created_at', 'desc')->get(),
            'activities' => LeadActivity::where('client_id', $client->id)->with('user')->orderBy('created_at', 'desc')->get(),
            // Para marcar as cotações que já deram origem a uma convertida.
            'convertedByProposal' => $client->convertedProposals->whereNotNull('proposal_id')->keyBy('proposal_id'),
            'canUpdate' => $canUpdate,
            // Os pedidos, cotações e convertidas são todos deste cliente: o dono confere-se uma vez.
            'canOpenRequests' => $this->permissions->allows($user, 'form-proposals', 'view', (new FormProposal)->setRelation('client', $client)),
            'canCreateRequest' => $this->permissions->allows($user, 'form-proposals', 'create'),
            'canOpenProposals' => $this->permissions->allows($user, 'proposals', 'view', (new Proposal)->setRelation('client', $client)),
            'canCreateProposal' => $this->permissions->allows($user, 'proposals', 'create'),
            'canOpenConverted' => app(ConvertedProposalService::class)->canView($user, (new ConvertedProposal)->setRelation('client', $client)),
            // Simulações de custos do cliente: sem âmbito "próprios", basta poder ver o objeto.
            'simulators' => $canOpenSimulators ? CostSimulator::where('client_id', $client->id)->orderBy('created_at', 'desc')->get() : collect(),
            'canOpenSimulators' => $canOpenSimulators,
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($this->canCreate($request), 403);

        return view('admin.mobile.clients.form', ['client' => new Client(['language' => 'pt'])]);
    }

    public function store(Request $request)
    {
        abort_unless($this->canCreate($request), 403);

        $data = $request->validate($this->clients->rules());

        if ($erro = $this->emailTaken($request, $data)) {
            return back()->withInput()->withErrors(['email' => $erro]);
        }

        // Mesmo aviso de duplicados do desktop (aqui só pode ser o telefone), confirmável.
        if ($warning = $this->clients->duplicateWarning($data, $request->boolean('confirm_duplicate'))) {
            return back()->withInput()->withErrors(['duplicate' => $this->clients->phoneWarningMessage($warning, $this->ownScope($request))]);
        }

        // Como no desktop e nas leads, o dono fica por atribuir.
        $client = $this->clients->create($data);

        // Quem só vê os seus clientes pode não poder abrir o que acabou de criar: vai para a lista.
        $destino = $this->permissions->allows($request->user(), 'clients', 'view', $client)
            ? route('admin.mobile.clients.show', $client->id)
            : route('admin.mobile.clients.index');

        return redirect($destino)->with('success', 'Cliente criado com sucesso!');
    }

    public function edit(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        if ($client->is_lead) {
            return redirect()->route('admin.mobile.leads.show', $client->id);
        }
        $this->authorizeUpdate($request, $client);

        return view('admin.mobile.clients.form', ['client' => $client]);
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeUpdate($request, $client);

        $data = $request->validate($this->clients->rules());

        if ($erro = $this->emailTaken($request, $data, $client->id)) {
            return back()->withInput()->withErrors(['email' => $erro]);
        }

        // Como no desktop: sem aviso de duplicados por telefone na edição.
        $this->clients->update($client, $data);

        return redirect()->route('admin.mobile.clients.show', $client->id)
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function followup(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeUpdate($request, $client);

        $request->validate([
            'next_followup_at' => 'required|date|after:now',
            'followup_note'    => 'nullable|string|max:255',
        ]);

        $this->leads->scheduleFollowup($client, $request->next_followup_at, $request->followup_note);

        return back(302, [], route('admin.mobile.clients.show', $client->id))->with('success', 'Follow-up agendado.');
    }

    public function activity(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        $this->authorizeUpdate($request, $client);

        $request->validate([
            'type'  => 'required|in:note,call,email,whatsapp,facebook,meeting',
            'title' => 'required|string|max:255',
            'body'  => 'nullable|string|max:2000',
        ]);

        $this->leads->logActivity($client, $request->type, $request->title, $request->body);

        return back(302, [], route('admin.mobile.clients.show', $client->id))->with('success', 'Atividade registada.');
    }

    /** clients.email é único na BD: no mobile, um email repetido é um erro de validação (nunca um 500). */
    private function emailTaken(Request $request, array $data, ?int $ignoreId = null): ?string
    {
        $existente = $this->clients->emailOwner($data['email'] ?? null, $ignoreId);
        if (!$existente) {
            return null;
        }

        // Quem só vê os seus clientes não fica a saber o nome de um cliente alheio.
        return $this->clients->emailTakenMessage($existente, $this->ownScope($request));
    }

    private function ownScope(Request $request): bool
    {
        return $this->permissions->scope($request->user(), 'clients', 'view') === 'own';
    }

    private function canCreate(Request $request): bool
    {
        return $this->permissions->allows($request->user(), 'clients', 'create');
    }

    /** Editar o cliente (próprios: só os seus); uma lead exige também poder editar a lead. */
    private function canUpdate(Request $request, Client $client): bool
    {
        $user = $request->user();

        return $this->permissions->allows($user, 'clients', 'update', $client)
            && (!$client->is_lead || $this->permissions->allows($user, 'leads', 'update', $client));
    }

    private function authorizeUpdate(Request $request, Client $client): void
    {
        abort_unless($this->canUpdate($request, $client), 403);
    }
}
