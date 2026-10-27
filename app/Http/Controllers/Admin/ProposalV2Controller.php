<?php

/**
 * ==================================================================
 * PROPOSAL CONTROLLER V2
 * ==================================================================
 * 
 * Controller moderno e bem estruturado para gestão de propostas
 * 
 * FUNCIONALIDADES:
 * - Listagem com filtros e paginação
 * - Criação de novas propostas
 * - Edição de propostas existentes
 * - Eliminação de propostas
 * - Alteração rápida de status
 * - Upload e gestão de imagens
 * 
 * ROTAS:
 * GET    /admin/v2/proposals         - Listagem
 * GET    /admin/v2/proposals/create  - Form de criação
 * POST   /admin/v2/proposals         - Guardar nova proposta
 * GET    /admin/v2/proposals/{id}    - Ver detalhes
 * GET    /admin/v2/proposals/{id}/edit - Form de edição
 * PUT    /admin/v2/proposals/{id}    - Atualizar proposta
 * DELETE /admin/v2/proposals/{id}    - Eliminar proposta
 * 
 * @author Izzycar Team
 * @version 2.0
 * ==================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormProposal;
use App\Services\ProposalAcceptanceException;
use App\Services\ProposalAcceptanceService;
use App\Services\ProposalService;
use App\Services\ProposalStatusNotAppliedException;
use App\Models\Proposal;
use App\Models\Client;
use App\Models\Brand;
use App\Models\VehicleAttribute;
use App\Models\AttributeGroup;
use App\Models\ImportOpportunity;
use App\Services\ImageOptimizerService;
use App\Services\VehicleListingImportService;
use Illuminate\Http\Request;

class ProposalV2Controller extends Controller
{
    /**
     * ==============================================================
     * INDEX - Listagem de propostas
     * ==============================================================
     * 
     * Exibe lista paginada de propostas com filtros
     * 
     * FILTROS DISPONÍVEIS:
     * - status: Estado da proposta
     * - client_id: Cliente específico
     * - search: Pesquisa por marca/modelo
     * - date_from: Data inicial
     * - date_to: Data final
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $staleStatuses = ['Pendente', 'Enviado'];
        $staleCutoff   = now()->subDays(30);

        // Iniciar query builder
        $query = Proposal::with('client')->orderBy('created_at', 'desc');

        // FILTRO: Cotações antigas sem resposta (>30 dias, Pendente ou Enviado)
        if ($request->boolean('stale')) {
            $query->whereIn('status', $staleStatuses)
                  ->where('created_at', '<', $staleCutoff);
        }

        // FILTRO: Status — por omissão mostra todas ("Todos" no filtro envia
        // status="", que não deve aplicar nenhum where).
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // FILTRO: Cliente
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // FILTRO: Pesquisa (marca ou modelo)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('version', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('proposal_code', 'like', "%{$search}%");
            });
        }

        // FILTRO: Data inicial
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // FILTRO: Data final
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Paginar resultados (15 por página)
        $proposals = $query->paginate(15)->withQueryString();

        // Contagem de cotações antigas sem resposta (para alerta)
        $staleCount = Proposal::whereIn('status', $staleStatuses)
                              ->where('created_at', '<', $staleCutoff)
                              ->count();

        // Obter lista de clientes para filtro
        $clients = Client::orderBy('name')->get();

        // Calcular estatísticas para os cards do topo
        $stats = [
            [
                'title' => 'Total Propostas',
                'value' => Proposal::count(),
                'icon' => 'file-earmark-text',
                'color' => 'primary'
            ],
            [
                'title' => 'Pendentes',
                'value' => Proposal::where('status', 'Pendente')->count(),
                'icon' => 'clock',
                'color' => 'warning'
            ],
            [
                'title' => 'Aprovadas',
                'value' => Proposal::where('status', 'Aprovada')->count(),
                'icon' => 'check-circle',
                'color' => 'success'
            ],
            [
                'title' => 'Este Mês',
                'value' => Proposal::whereMonth('created_at', now()->month)->count(),
                'icon' => 'calendar',
                'color' => 'info'
            ]
        ];

        // Retornar view com dados
        return view('admin.v2.proposals.index', compact('proposals', 'clients', 'stats', 'staleCount'));
    }

    /**
     * ==============================================================
     * CREATE - Form de criação
     * ==============================================================
     * 
     * Exibe formulário para criar nova proposta
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $clients = Client::where('is_lead', false)->orderBy('name')->get();
        $leads   = Client::where('is_lead', true)->orderBy('name')->get();

        // Obter marcas com modelos
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();

        // Obter atributos agrupados e ordenados
        $groupOrder = AttributeGroup::orderBy('order')->get()->pluck('name')->toArray();
        $attributes = VehicleAttribute::orderBy('order')->get()
            ->groupBy('attribute_group')
            ->sortBy(function ($group, $key) use ($groupOrder) {
                return array_search($key, $groupOrder);
            });

        // Valores default dos custos (podem vir de settings)
        $defaults = ProposalService::DEFAULT_COSTS;

        return view('admin.v2.proposals.form', compact('clients', 'leads', 'brands', 'attributes', 'defaults'));
    }

    public function createFromForm($formProposalId, ProposalService $proposals)
    {
        $formProposal = FormProposal::findOrFail($formProposalId);

        // Objeto pré-preenchido com dados do formulário
        $proposal = $this->prefilledProposal($proposals->prefillFor(null, $formProposal));

        return view('admin.v2.proposals.form', $this->formViewData() + compact('proposal', 'formProposal'));
    }

    /**
     * Cotação a partir de uma Oportunidade do Pedido de Importação: o mesmo
     * formulário, pré-preenchido com o carro encontrado. Uma cotação por
     * oportunidade — se já existir, abre-a.
     */
    public function createFromOpportunity(ImportOpportunity $opportunity, ProposalService $proposals)
    {
        if ($proposals->existingForOpportunity($opportunity)) {
            return redirect()->route('admin.v2.proposals.edit', $opportunity->proposal_id)
                ->with('info', 'Esta oportunidade já tem cotação.');
        }

        $formProposal = $opportunity->formProposal;
        $importOpportunity = $opportunity;
        $viewData = $this->formViewData();

        // Marca/modelo são selects do catálogo: na oportunidade são texto livre,
        // por isso procura-se o nome equivalente sem diferenciar maiúsculas.
        [$catalogBrand, $catalogModel] = $proposals->catalogMatch($opportunity, $viewData['brands']);
        $catalogWarning = ! $catalogBrand || ! $catalogModel
            ? "\"{$opportunity->brand} {$opportunity->model}\" não corresponde a uma marca/modelo do catálogo — selecione manualmente."
            : null;

        $proposal = $this->prefilledProposal($proposals->prefillFor(null, $formProposal, $opportunity, $viewData['brands']));

        return view('admin.v2.proposals.form', $viewData + compact('proposal', 'formProposal', 'importOpportunity', 'catalogWarning'));
    }

    /** Dados partilhados pelos formulários de cotação pré-preenchidos. */
    private function formViewData(): array
    {
        $groupOrder = AttributeGroup::orderBy('order')->get()->pluck('name')->toArray();

        return [
            'clients' => Client::where('is_lead', false)->orderBy('name')->get(),
            'leads' => Client::where('is_lead', true)->orderBy('name')->get(),
            'brands' => app(ProposalService::class)->catalog(),
            'attributes' => VehicleAttribute::orderBy('order')->get()
                ->groupBy('attribute_group')
                ->sortBy(fn ($group, $key) => array_search($key, $groupOrder)),
            'defaults' => ProposalService::DEFAULT_COSTS,
        ];
    }

    /** Objeto "proposta" para o formulário, com todos os campos que a view lê. */
    private function prefilledProposal(array $values): object
    {
        return (object) ($values + [
            'client_id'               => null,
            'brand'                   => null,
            'model'                   => null,
            'version'                 => null,
            'fuel'                    => null,
            'proposed_car_value'      => null,
            'proposed_car_mileage'    => null,
            'notes'                   => null,
            'status'                  => 'Pendente',
            'url'                     => null,
            'year'                    => null,
            'mileage'                 => null,
            'engine_capacity'         => null,
            'co2'                     => null,
            'value'                   => null,
            'proposed_car_year_month' => null,
            'proposed_car_notes'      => null,
            'proposed_car_features'   => null,
            'other_links'             => null,
            'images'                  => null,
        ]);
    }

    /**
     * Lê um anúncio (por agora, apenas AutoScout24) a partir do seu URL e
     * devolve os dados do veículo para pré-preencher o formulário de
     * cotação. Sites não suportados (ex.: mobile.de) devolvem um aviso,
     * sem dados.
     */
    public function importFromListing(Request $request)
    {
        $request->validate([
            'url' => 'required|url|max:1000',
        ]);

        $result = (new VehicleListingImportService())->import($request->input('url'));

        if (!$result['success']) {
            return response()->json(['message' => $result['message'], 'supported' => $result['supported']], 422);
        }

        return response()->json(['values' => $result['values']]);
    }

    /**
     * ==============================================================
     * STORE - Guardar nova proposta
     * ==============================================================
     * 
     * Processa e guarda nova proposta na base de dados
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, ProposalService $proposals)
    {
        // Validar dados do formulário
        $validated = $request->validate($proposals->rules());

        $formProposalId = $validated['form_proposal_id'] ?? null;
        $opportunityId = $validated['import_opportunity_id'] ?? null;

        // Uma cotação por oportunidade (protege contra duplo submit).
        $opportunity = $opportunityId ? ImportOpportunity::find($opportunityId) : null;
        if ($proposals->existingForOpportunity($opportunity)) {
            return redirect()->route('admin.v2.proposals.edit', $opportunity->proposal_id)
                ->with('info', 'Esta oportunidade já tem cotação.');
        }

        try {
            $proposals->create(
                $validated,
                $request->hasFile('image') ? $request->file('image') : null,
                $formProposalId ? (int) $formProposalId : null,
                $opportunityId ? (int) $opportunityId : null,
                $request->has('attributes') ? $request->input('attributes', []) : null,
                $request->user()
            );
        } catch (ProposalStatusNotAppliedException $e) {
            return redirect()->route('admin.v2.proposals.edit', $e->proposal->id)->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.v2.proposals.index')
            ->with('success', 'Proposta criada com sucesso!');
    }

    /**
     * ==============================================================
     * EDIT - Form de edição
     * ==============================================================
     * 
     * Exibe formulário para editar proposta existente
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $proposal = Proposal::with('attributeValues')->findOrFail($id);

        $clients = Client::where('is_lead', false)->orderBy('name')->get();
        $leads   = Client::where('is_lead', true)->orderBy('name')->get();

        // Obter marcas com modelos
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();

        // Obter atributos agrupados e ordenados
        $groupOrder = AttributeGroup::orderBy('order')->get()->pluck('name')->toArray();
        $attributes = VehicleAttribute::orderBy('order')->get()
            ->groupBy('attribute_group')
            ->sortBy(function ($group, $key) use ($groupOrder) {
                return array_search($key, $groupOrder);
            });

        // Mapear valores de atributos por ID para fácil acesso
        $attributeValues = $proposal->attributeValues->keyBy('attribute_id');

        // Obter imagens da proposta
        $images = $proposal->images ?? [];

        // Valores default (mesmo na edição, para fallback)
        $defaults = [
            'transport_cost' => 1350,
            'ipo_cost' => 100,
            'imt_cost' => 45,
            'registration_cost' => 65,
            'isv_cost' => 0,
            'license_plate_cost' => 20,
            'inspection_commission_cost' => 350,
            'commission_cost' => 615,
        ];

        return view('admin.v2.proposals.form', compact('proposal', 'clients', 'leads', 'brands', 'attributes', 'attributeValues', 'images', 'defaults'));
    }

    /**
     * ==============================================================
     * UPDATE - Atualizar proposta
     * ==============================================================
     * 
     * Atualiza proposta existente na base de dados
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id, ProposalService $proposals)
    {
        // Obter proposta
        $proposal = Proposal::findOrFail($id);

        // Validar dados (mesmas regras do store)
        $validated = $request->validate($proposals->updateRules());

        try {
            // Os atributos são todos recriados (um formulário sem atributos apaga-os).
            $proposals->update(
                $proposal,
                $validated,
                $request->hasFile('image') ? $request->file('image') : null,
                $request->input('attributes', [])
            );
        } catch (ProposalStatusNotAppliedException $e) {
            return redirect()->route('admin.v2.proposals.edit', $proposal->id)->with('error', $e->getMessage());
        } catch (ProposalAcceptanceException $e) {
            return back()->withInput()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.v2.proposals.index')
            ->with('success', 'Proposta atualizada com sucesso!');
    }

    /**
     * ==============================================================
     * DESTROY - Eliminar proposta
     * ==============================================================
     * 
     * Remove proposta da base de dados
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Obter proposta
        $proposal = Proposal::findOrFail($id);

        // Eliminar imagem do storage (todas as variantes)
        if ($proposal->images) {
            $existing = is_array($proposal->images) ? $proposal->images[0] : $proposal->images;
            (new ImageOptimizerService())->deleteAllVariants($existing);
        }

        // Eliminar proposta (cascade irá remover attributeValues automaticamente)
        $proposal->delete();

        // Redirect com mensagem
        return redirect()
            ->route('admin.v2.proposals.index')
            ->with('success', 'Proposta eliminada com sucesso!');
    }

    /**
     * ==============================================================
     * MÉTODOS AUXILIARES
     * ==============================================================
     */

    /**
     * Aceita uma cotação diretamente a partir da listagem: marca-a como
     * Aprovada e cria a Cotação Convertida correspondente — o mesmo que
     * ProposalController::accept() faz no lado do cliente, mas disparado
     * pelo BO, sem os dados de contacto do formulário público (o cliente já
     * é conhecido) e sem o email/PDF de contrato automático (esse fluxo
     * fica reservado para quando é o próprio cliente a aceitar online).
     */
    public function accept($id, ProposalService $proposals)
    {
        $proposal = Proposal::findOrFail($id);

        try {
            // No backoffice pode aceitar-se mesmo expirada/reprovada (ex. cliente
            // que aceitou por telefone); não envia emails nem contrato.
            $convertedProposal = $proposals->accept($proposal);
        } catch (ProposalAcceptanceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'status' => $proposal->fresh()->status,
            'converted_proposal_id' => $convertedProposal->id,
            'redirect' => route('admin.v2.converted-proposals.edit', $convertedProposal->id),
        ]);
    }

    /**
     * Pipeline kanban. Pendente e Enviado mostram tudo (trabalho em aberto);
     * Aprovada, Reprovada e Sem resposta só as mais recentes, porque crescem
     * sem parar (a lista tem o resto).
     */
    public function kanban()
    {
        $open = ['Pendente', 'Enviado'];
        $closedLimit = 30;
        $columns = [];

        foreach (['Pendente', 'Enviado', 'Aprovada', 'Reprovada', 'Sem resposta'] as $status) {
            $query = Proposal::with('client:id,name,email')->where('status', $status);
            $total = (clone $query)->count();
            $query = in_array($status, $open, true)
                ? $query->orderByDesc('created_at')
                : $query->orderByDesc('updated_at')->limit($closedLimit);

            $columns[$status] = ['items' => $query->get(), 'total' => $total];
        }

        return view('admin.v2.proposals.kanban', compact('columns', 'closedLimit'));
    }

    public function updateStatus(Request $request, $id, ProposalService $proposals)
    {
        $request->validate($proposals->statusRules());

        $proposal = Proposal::with('client')->findOrFail($id);

        try {
            $notified = $proposals->changeStatus($proposal, $request->status, $request->boolean('notify', true));
        } catch (ProposalAcceptanceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'status' => $proposal->fresh()->status,
            'notified' => $notified,
        ]);
    }

    /**
     * Em massa, cotação a cotação pelo serviço (e não com um UPDATE direto),
     * para "Aprovada" criar a cotação convertida e o observer correr.
     */
    public function bulkUpdateStatus(Request $request, ProposalAcceptanceService $statuses)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:proposals,id',
            'status' => 'required|string|in:Pendente,Aprovada,Reprovada,Enviado,Sem resposta',
        ]);

        return response()->json($this->applyStatus(Proposal::whereIn('id', $data['ids'])->get(), $data['status'], $statuses));
    }

    public function bulkReject(Request $request, ProposalAcceptanceService $statuses)
    {
        $ids = $request->input('ids', []);

        $proposals = empty($ids)
            ? Proposal::whereIn('status', ['Pendente', 'Enviado'])->where('created_at', '<', now()->subDays(30))->get()
            : Proposal::whereIn('id', $ids)->get();

        return response()->json($this->applyStatus($proposals, 'Reprovada', $statuses));
    }

    /** @return array{success: bool, count: int, skipped: list<string>} */
    private function applyStatus($proposals, string $status, ProposalAcceptanceService $statuses): array
    {
        $count = 0;
        $skipped = [];

        foreach ($proposals as $proposal) {
            try {
                if ($proposal->status !== $status) {
                    $statuses->changeStatus($proposal, $status);
                    $count++;
                }
            } catch (ProposalAcceptanceException $e) {
                $skipped[] = $e->getMessage();
            }
        }

        return ['success' => true, 'count' => $count, 'skipped' => $skipped];
    }
}
