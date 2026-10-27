<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FormProposal;
use App\Permissions\PermissionService;
use App\Services\FormProposalService;
use App\Services\ImportOpportunityService;
use Illuminate\Http\Request;

/**
 * Pedidos de formulário (pedidos de importação) na versão mobile. A lógica é a
 * do desktop (FormProposalService); ver filtra pelo dono do cliente registo a
 * registo, criar e mudar o estado exigem as mesmas permissões do desktop.
 */
class FormProposalController extends Controller
{
    public function __construct(private FormProposalService $formProposals, private PermissionService $permissions)
    {
    }

    public function index(Request $request)
    {
        $query = $this->formProposals->query($request->only(['status', 'search']));

        $formProposals = $this->permissions->applyScope($query, $request->user(), 'form-proposals')
            ->with('client')
            ->paginate(15)
            ->withQueryString();

        return view('admin.mobile.form-proposals.index', [
            'formProposals' => $formProposals,
            'statuses' => $this->statusLabels(),
        ]);
    }

    public function show(Request $request, $id, ImportOpportunityService $opportunities)
    {
        $formProposal = FormProposal::with(['client', 'creator'])->findOrFail($id);
        abort_unless($this->permissions->allows($request->user(), 'form-proposals', 'view', $formProposal), 403);

        // As oportunidades (vendedor, preço de compra) só para quem vê todos os pedidos, como no desktop.
        $canViewOpportunities = $this->canViewOpportunities($request);
        if ($canViewOpportunities) {
            $formProposal->load(['opportunities.checklistEntries', 'opportunities.seller']);
        }

        $canUpdate = $this->permissions->allows($request->user(), 'form-proposals', 'update');

        return view('admin.mobile.form-proposals.show', [
            'formProposal' => $formProposal,
            'statuses' => $this->statusLabels(),
            'manualStatuses' => FormProposalService::MANUAL_STATUSES,
            'canUpdate' => $canUpdate,
            // Ligação ao nome do cliente: só se puder abrir a ficha (lead ou cliente).
            'canViewClient' => $formProposal->client !== null
                && $this->permissions->allows($request->user(), $formProposal->client->is_lead ? 'leads' : 'clients', 'view', $formProposal->client),
            'canViewOpportunities' => $canViewOpportunities,
            // Criar cotação a partir do pedido: só com cliente e quem pode criar cotações.
            'canCreateProposal' => $formProposal->client_id !== null && $this->permissions->allows($request->user(), 'proposals', 'create'),
            'opportunityProgress' => $canViewOpportunities
                ? $formProposal->opportunities->mapWithKeys(fn ($opportunity) => [$opportunity->id => $opportunities->progress($opportunity)])
                : collect(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeAction($request, 'create');
        $request->validate(['client_id' => 'required|integer|exists:clients,id']);

        return view('admin.mobile.form-proposals.create', [
            'client' => Client::findOrFail($request->integer('client_id')),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAction($request, 'create');
        $data = $request->validate(
            $this->formProposals->manualRules($request->input('brand')),
            $this->formProposals->vehicleMessages()
        );

        $formProposal = $this->formProposals->createManual($data, $request->user());

        return redirect()->route('admin.mobile.form-proposals.show', $formProposal->id)
            ->with('success', 'Pedido criado. Já pode adicionar oportunidades.');
    }

    public function status(Request $request, $id)
    {
        $formProposal = FormProposal::findOrFail($id);
        $this->authorizeAction($request, 'update');

        $request->validate($this->formProposals->statusRules(), $this->formProposals->statusMessages());

        $this->formProposals->changeStatus($formProposal, $request->status);

        return back(302, [], route('admin.mobile.form-proposals.show', $formProposal->id))
            ->with('success', 'Estado atualizado!');
    }

    /**
     * Criar e editar não têm âmbito: verifica-se aqui também, para o mobile não
     * depender de PERMISSIONS_ENFORCE (o middleware por omissão só regista).
     */
    private function authorizeAction(Request $request, string $action): void
    {
        abort_unless($this->permissions->allows($request->user(), 'form-proposals', $action), 403);
    }

    private function canViewOpportunities(Request $request): bool
    {
        return $this->permissions->allows($request->user(), 'form-proposals', 'view', null, 'all');
    }

    /** Rótulos dos estados (os do desktop). */
    private function statusLabels(): array
    {
        return ['novo' => 'Novo', 'em_analise' => 'Em análise', 'convertido' => 'Convertido', 'rejeitado' => 'Rejeitado', 'arquivado' => 'Arquivado'];
    }
}
