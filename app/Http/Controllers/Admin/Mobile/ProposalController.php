<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\Proposal;
use App\Models\User;
use App\Permissions\PermissionService;
use App\Services\ProposalAcceptanceException;
use App\Services\ProposalService;
use App\Services\ProposalStatusNotAppliedException;
use Illuminate\Http\Request;

/**
 * Cotações na versão mobile. A lógica é a do desktop (ProposalService); ver
 * filtra pelo dono do cliente registo a registo. Criar exige "criar" e editar,
 * mudar o estado ou aceitar exigem editar todas, como as rotas do desktop.
 * As verificações ficam aqui para não dependerem de PERMISSIONS_ENFORCE.
 */
class ProposalController extends Controller
{
    public function __construct(private ProposalService $proposals, private PermissionService $permissions)
    {
    }

    public function index(Request $request)
    {
        $query = Proposal::with('client')->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // A mesma pesquisa do desktop (marca, modelo, versão, cliente, código).
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('version', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhere('proposal_code', 'like', "%{$search}%");
            });
        }

        $proposals = $this->permissions->applyScope($query, $request->user(), 'proposals')
            ->paginate(15)
            ->withQueryString();

        return view('admin.mobile.proposals.index', [
            'proposals' => $proposals,
            'statuses' => ProposalService::STATUSES,
        ]);
    }

    public function show(Request $request, $id)
    {
        $proposal = Proposal::with('client')->findOrFail($id);
        abort_unless($this->permissions->allows($request->user(), 'proposals', 'view', $proposal), 403);

        return view('admin.mobile.proposals.show', [
            'proposal' => $proposal,
            'converted' => ConvertedProposal::where('proposal_id', $proposal->id)->first(),
            'canUpdate' => $this->canUpdate($request->user(), $proposal),
            'canViewClient' => $proposal->client && $this->canSeeClient($request->user(), $proposal->client),
            'statuses' => ProposalService::STATUSES,
        ]);
    }

    /** ?client_id=, ?form_proposal_id= ou ?opportunity_id= — o cliente vem do pedido/oportunidade. */
    public function create(Request $request)
    {
        $this->authorizeCreate($request);
        $request->validate([
            'client_id' => 'nullable|integer|exists:clients,id',
            'form_proposal_id' => 'nullable|integer|exists:form_proposals,id',
            'opportunity_id' => 'nullable|integer|exists:import_opportunities,id',
        ]);

        $opportunity = $request->filled('opportunity_id') ? ImportOpportunity::with('formProposal')->find($request->integer('opportunity_id')) : null;
        $formProposal = $request->filled('form_proposal_id') ? FormProposal::find($request->integer('form_proposal_id')) : $opportunity?->formProposal;
        $clientId = $request->integer('client_id') ?: ($formProposal?->client_id ?? $opportunity?->formProposal?->client_id);
        $client = $clientId ? Client::find($clientId) : null;

        if (! $client) {
            return redirect()->route('admin.mobile.proposals.index')
                ->with('error', 'A cotação cria-se a partir de uma lead, cliente ou pedido com cliente.');
        }
        $this->authorizeClient($request, $client);

        // O pedido e a oportunidade têm de ser deste cliente.
        [$formProposal, $opportunity] = $this->proposals->linksFor($client, $formProposal?->id, $opportunity?->id);

        // Uma cotação por oportunidade — se já existir, abre-a.
        if ($existing = $this->proposals->existingForOpportunity($opportunity)) {
            return redirect()->route('admin.mobile.proposals.show', $existing->id)
                ->with('info', 'Esta oportunidade já tem cotação.');
        }

        $catalogWarning = null;
        if ($opportunity) {
            [$brand, $model] = $this->proposals->catalogMatch($opportunity, $this->proposals->catalog());
            $catalogWarning = ! $brand || ! $model
                ? "\"{$opportunity->brand} {$opportunity->model}\" não corresponde a uma marca/modelo do catálogo — selecione manualmente."
                : null;
        }

        return view('admin.mobile.proposals.form', [
            'proposal' => (object) ($this->proposals->prefillFor($client, $formProposal, $opportunity) + ProposalService::DEFAULT_COSTS + ['status' => 'Pendente']),
            'client' => $client,
            'formProposal' => $formProposal,
            'importOpportunity' => $opportunity,
            'catalogWarning' => $catalogWarning,
            'statuses' => ProposalService::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeCreate($request);
        $data = $this->withoutEmptyLinks($request->validate($this->proposals->rules()));

        $client = Client::findOrFail($data['client_id']);
        $this->authorizeClient($request, $client);

        // O pedido e a oportunidade têm de ser deste cliente.
        [$formProposal, $opportunity] = $this->proposals->linksFor(
            $client,
            isset($data['form_proposal_id']) ? (int) $data['form_proposal_id'] : null,
            isset($data['import_opportunity_id']) ? (int) $data['import_opportunity_id'] : null,
        );

        // Uma cotação por oportunidade (protege contra duplo submit).
        if ($existing = $this->proposals->existingForOpportunity($opportunity)) {
            return redirect()->route('admin.mobile.proposals.show', $existing->id)
                ->with('info', 'Esta oportunidade já tem cotação.');
        }

        try {
            $proposal = $this->proposals->create($data, $request->file('image'), $formProposal?->id, $opportunity?->id, null, $request->user());
        } catch (ProposalStatusNotAppliedException $e) {
            return redirect()->route('admin.mobile.proposals.show', $e->proposal->id)->with('error', $e->getMessage());
        }

        return redirect()->route('admin.mobile.proposals.show', $proposal->id)
            ->with('success', 'Cotação criada com sucesso!');
    }

    public function edit(Request $request, $id)
    {
        $proposal = Proposal::with('client')->findOrFail($id);
        $this->authorizeUpdate($request, $proposal);

        return view('admin.mobile.proposals.form', [
            'proposal' => $proposal,
            'client' => $proposal->client,
            'formProposal' => null,
            'importOpportunity' => null,
            'catalogWarning' => null,
            'statuses' => ProposalService::STATUSES,
            'accepted' => $proposal->isAccepted(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);
        $this->authorizeUpdate($request, $proposal);

        $data = $this->withoutEmptyLinks($request->validate($this->proposals->updateRules()));
        if ((int) $data['client_id'] !== (int) $proposal->client_id) {
            $this->authorizeClient($request, Client::findOrFail($data['client_id']));
        }

        try {
            // Sem atributos no formulário mobile: mantêm-se os que a cotação tem.
            $this->proposals->update($proposal, $data, $request->file('image'), null);
        } catch (ProposalStatusNotAppliedException $e) {
            return redirect()->route('admin.mobile.proposals.show', $proposal->id)->with('error', $e->getMessage());
        } catch (ProposalAcceptanceException $e) {
            return back()->withInput()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()->route('admin.mobile.proposals.show', $proposal->id)
            ->with('success', 'Cotação atualizada com sucesso!');
    }

    public function status(Request $request, $id)
    {
        $proposal = Proposal::with('client')->findOrFail($id);
        $this->authorizeUpdate($request, $proposal);

        $request->validate($this->proposals->statusRules());

        try {
            $notified = $this->proposals->changeStatus($proposal, $request->status, $request->boolean('notify', true));
        } catch (ProposalAcceptanceException $e) {
            return redirect()->route('admin.mobile.proposals.show', $proposal->id)->with('error', $e->getMessage());
        }

        return redirect()->route('admin.mobile.proposals.show', $proposal->id)
            ->with('success', "Estado: {$proposal->fresh()->status}." . ($notified ? ' O cliente foi avisado por email.' : ''));
    }

    public function accept(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);
        $this->authorizeUpdate($request, $proposal);

        try {
            $converted = $this->proposals->accept($proposal);
        } catch (ProposalAcceptanceException $e) {
            return redirect()->route('admin.mobile.proposals.show', $proposal->id)->with('error', $e->getMessage());
        }

        return redirect()->route('admin.mobile.proposals.show', $proposal->id)
            ->with('success', "Cotação aceite. Cotação convertida #{$converted->id} criada.");
    }

    /** O formulário tem campos de links fixos: os vazios não se gravam. */
    private function withoutEmptyLinks(array $data): array
    {
        if (array_key_exists('other_links', $data)) {
            $data['other_links'] = array_values(array_filter((array) $data['other_links'], 'filled')) ?: null;
        }

        return $data;
    }

    private function authorizeCreate(Request $request): void
    {
        abort_unless($this->permissions->allows($request->user(), 'proposals', 'create'), 403);
    }

    /** Editar, mudar o estado e aceitar exigem editar todas, como no desktop. */
    private function authorizeUpdate(Request $request, Proposal $proposal): void
    {
        abort_unless($this->canUpdate($request->user(), $proposal), 403);
    }

    private function canUpdate(User $user, Proposal $proposal): bool
    {
        return $this->permissions->allows($user, 'proposals', 'update', $proposal, 'all');
    }

    /** Só se cota para uma lead/cliente que o utilizador pode ver (um client_id forjado dá 403). */
    private function authorizeClient(Request $request, Client $client): void
    {
        abort_unless($this->canSeeClient($request->user(), $client), 403);
    }

    private function canSeeClient(User $user, Client $client): bool
    {
        return $this->permissions->allows($user, $client->is_lead ? 'leads' : 'clients', 'view', $client);
    }
}
