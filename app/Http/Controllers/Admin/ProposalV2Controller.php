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
use App\Models\Proposal;
use App\Models\Client;
use App\Models\LeadActivity;
use App\Models\Brand;
use App\Models\VehicleAttribute;
use App\Models\ProposalAttributeValue;
use App\Models\AttributeGroup;
use App\Models\ImportOpportunity;
use App\Services\ImageOptimizerService;
use App\Services\ImportOpportunityService;
use App\Services\VehicleListingImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        $defaults = [
            'transport_cost' => 1350,
            'ipo_cost' => 100,
            'imt_cost' => 45,
            'registration_cost' => 65,
            'isv_cost' => 0,
            'license_plate_cost' => 20,
            'inspection_commission_cost' => 350,
            'commission_cost' => 615,
            'iuc_cost' => 0,
        ];

        return view('admin.v2.proposals.form', compact('clients', 'leads', 'brands', 'attributes', 'defaults'));
    }

    public function createFromForm($formProposalId)
    {
        $formProposal = FormProposal::findOrFail($formProposalId);

        // Objeto pré-preenchido com dados do formulário
        $proposal = $this->prefilledProposal([
            'client_id'           => $formProposal->client_id,
            'brand'               => $formProposal->brand,
            'model'               => $formProposal->model,
            'version'             => $formProposal->version,
            'fuel'                => $formProposal->fuel,
            'proposed_car_value'  => $formProposal->budget,
            'proposed_car_mileage'=> $formProposal->km_max,
            'notes'               => $formProposal->message,
        ]);

        return view('admin.v2.proposals.form', $this->formViewData() + compact('proposal', 'formProposal'));
    }

    /**
     * Cotação a partir de uma Oportunidade do Pedido de Importação: o mesmo
     * formulário, pré-preenchido com o carro encontrado. Uma cotação por
     * oportunidade — se já existir, abre-a.
     */
    public function createFromOpportunity(ImportOpportunity $opportunity)
    {
        if ($opportunity->proposal_id && $opportunity->proposal) {
            return redirect()->route('admin.v2.proposals.edit', $opportunity->proposal_id)
                ->with('info', 'Esta oportunidade já tem cotação.');
        }

        $formProposal = $opportunity->formProposal;
        $importOpportunity = $opportunity;
        $viewData = $this->formViewData();

        // Marca/modelo são selects do catálogo: na oportunidade são texto livre,
        // por isso procura-se o nome equivalente sem diferenciar maiúsculas.
        $sameName = fn (string $a, ?string $b) => mb_strtolower(trim($a)) === mb_strtolower(trim((string) $b));
        $catalogBrand = $viewData['brands']->first(fn ($brand) => $sameName($brand->name, $opportunity->brand));
        $catalogModel = $catalogBrand?->models->first(fn ($model) => $sameName($model->name, $opportunity->model));
        $catalogWarning = ! $catalogBrand || ! $catalogModel
            ? "\"{$opportunity->brand} {$opportunity->model}\" não corresponde a uma marca/modelo do catálogo — selecione manualmente."
            : null;

        $proposal = $this->prefilledProposal([
            'client_id'               => $formProposal->client_id,
            'brand'                   => $catalogBrand?->name ?? $opportunity->brand,
            'model'                   => $catalogModel?->name ?? $opportunity->model,
            'version'                 => $opportunity->version,
            'fuel'                    => $opportunity->fuel?->proposalLabel(),
            'url'                     => $opportunity->listing_url,
            'proposed_car_year_month' => $opportunity->year,
            'proposed_car_mileage'    => $opportunity->mileage,
            'proposed_car_value'      => $opportunity->price,
            // Notas internas (não aparecem na página pública da cotação).
            'notes'                   => implode("\n\n", array_filter([$opportunity->vehicle_notes, $formProposal->message])) ?: null,
        ]);

        return view('admin.v2.proposals.form', $viewData + compact('proposal', 'formProposal', 'importOpportunity', 'catalogWarning'));
    }

    /** Dados partilhados pelos formulários de cotação pré-preenchidos. */
    private function formViewData(): array
    {
        $groupOrder = AttributeGroup::orderBy('order')->get()->pluck('name')->toArray();

        return [
            'clients' => Client::where('is_lead', false)->orderBy('name')->get(),
            'leads' => Client::where('is_lead', true)->orderBy('name')->get(),
            'brands' => Brand::with(['models' => function ($q) { $q->orderBy('name'); }])->get(),
            'attributes' => VehicleAttribute::orderBy('order')->get()
                ->groupBy('attribute_group')
                ->sortBy(fn ($group, $key) => array_search($key, $groupOrder)),
            'defaults' => [
                'transport_cost'             => 1350,
                'ipo_cost'                   => 100,
                'imt_cost'                   => 45,
                'registration_cost'          => 65,
                'isv_cost'                   => 0,
                'license_plate_cost'         => 20,
                'inspection_commission_cost' => 350,
                'commission_cost'            => 615,
                'iuc_cost'                   => 0,
            ],
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
    public function store(Request $request)
    {
        // Validar dados do formulário
        $validated = $request->validate([
            'form_proposal_id' => 'nullable|exists:form_proposals,id',
            'import_opportunity_id' => 'nullable|exists:import_opportunities,id',
            'client_id' => 'required|exists:clients,id',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'mileage' => 'nullable|integer|min:0',
            'engine_capacity' => 'nullable|numeric|min:0',
            'co2' => 'nullable|numeric|min:0',
            'fuel' => 'nullable|in:Gasolina,Diesel,Gasolina (HEV),Diesel (HEV),Híbrido Plug-in/Gasolina,Híbrido Plug-in/Diesel,Elétrico',
            'value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'url' => 'nullable|url',
            'status' => 'nullable|in:Pendente,Aprovada,Reprovada,Enviado,Sem resposta',
            'transport_cost' => 'required|numeric|min:0',
            'ipo_cost' => 'nullable|numeric|min:0',
            'imt_cost' => 'nullable|numeric|min:0',
            'registration_cost' => 'nullable|numeric|min:0',
            'isv_cost' => 'nullable|numeric|min:0',
            'license_plate_cost' => 'nullable|numeric|min:0',
            'inspection_commission_cost' => 'nullable|numeric|min:0',
            'commission_cost' => 'nullable|numeric|min:0',
            'proposed_car_mileage' => 'nullable|integer|min:0',
            'proposed_car_year_month' => 'nullable|string',
            'proposed_car_value' => 'nullable|numeric|min:0',
            'proposed_car_notes' => 'nullable|string',
            'proposed_car_features' => 'nullable|string',
            'image' => 'nullable|image|mimes:webp,jpeg,png,jpg,gif,svg,avif|max:16000',
            'other_links' => 'nullable|array',
            'iuc_cost' => 'nullable|numeric|min:0',
        ]);

        // Remover campos que não vão para a tabela proposals
        $formProposalId = $validated['form_proposal_id'] ?? null;
        $importOpportunity = isset($validated['import_opportunity_id'])
            ? ImportOpportunity::find($validated['import_opportunity_id'])
            : null;
        unset($validated['image'], $validated['form_proposal_id'], $validated['import_opportunity_id']);

        // Uma cotação por oportunidade (protege contra duplo submit).
        if ($importOpportunity?->proposal_id && $importOpportunity->proposal) {
            return redirect()->route('admin.v2.proposals.edit', $importOpportunity->proposal_id)
                ->with('info', 'Esta oportunidade já tem cotação.');
        }

        // Gerar código único para a proposta
        $validated['proposal_code'] = strtoupper(substr($validated['brand'], 0, 1) .
            substr($validated['model'], 0, 1) .
            Str::random(8));

        // Converter array de links para JSON
        if (isset($validated['other_links'])) {
            $validated['other_links'] = json_encode($validated['other_links']);
        }

        // O estado escolhido aplica-se no fim, pelo serviço (ex. "Aprovada"
        // cria a cotação convertida); a cotação nasce sempre "Pendente".
        $requestedStatus = $validated['status'] ?? 'Pendente';
        $validated['status'] = 'Pendente';

        // Criar proposta
        $proposal = Proposal::create($validated);

        // Processar atributos personalizados (extras, características)
        if ($request->has('attributes')) {
            foreach ($request->input('attributes', []) as $attributeId => $value) {
                // Ignorar valores vazios
                if ($value === null || $value === '') {
                    continue;
                }

                // Criar registo de atributo
                ProposalAttributeValue::create([
                    'proposal_id' => $proposal->id,
                    'attribute_id' => $attributeId,
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]);
            }
        }

        // Processar upload de imagem (se existir); sem upload, usa a foto da oportunidade
        $opportunityService = app(ImportOpportunityService::class);
        if ($request->hasFile('image')) {
            $this->handleImageUpload($proposal, $request->file('image'));
        } elseif ($importOpportunity && ($photo = $opportunityService->photoAsUpload($importOpportunity))) {
            $this->handleImageUpload($proposal, $photo);
        }

        // Ligar ao pedido de origem. Vindo de uma oportunidade, o pedido pode
        // ter várias cotações — mantém a primeira. O pedido fica "em análise";
        // só passa a "convertido" quando uma cotação for aceite.
        if ($formProposalId) {
            $formProposal = FormProposal::find($formProposalId);
            if ($formProposal) {
                $formProposal->update([
                    'proposal_id' => $importOpportunity && $formProposal->proposal_id ? $formProposal->proposal_id : $proposal->id,
                    'status'      => in_array($formProposal->status, [null, 'novo'], true) ? 'em_analise' : $formProposal->status,
                ]);
            }
        }

        if ($importOpportunity) {
            $opportunityService->linkProposal($importOpportunity, $proposal, $request->user());
        }

        // Registar na timeline do cliente/lead
        $vehicle = implode(' ', array_filter([$proposal->brand, $proposal->model, $proposal->version]));
        LeadActivity::log(
            $proposal->client_id,
            "Cotação #{$proposal->id} criada" . ($vehicle ? " — {$vehicle}" : ''),
            $proposal->total_price ? "Valor total: " . number_format($proposal->total_price, 0, ',', '.') . " €." : '',
            'bi-file-earmark-text-fill',
            'primary'
        );

        if ($requestedStatus !== 'Pendente') {
            try {
                app(ProposalAcceptanceService::class)->changeStatus($proposal, $requestedStatus);
            } catch (ProposalAcceptanceException $e) {
                return redirect()->route('admin.v2.proposals.edit', $proposal->id)->with('error', $e->getMessage());
            }
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
    public function update(Request $request, $id)
    {
        // Obter proposta
        $proposal = Proposal::findOrFail($id);

        // Validar dados (mesmas regras do store)
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'mileage' => 'nullable|integer|min:0',
            'engine_capacity' => 'nullable|numeric|min:0',
            'co2' => 'nullable|numeric|min:0',
            'fuel' => 'nullable|in:Gasolina,Diesel,Gasolina (HEV),Diesel (HEV),Híbrido Plug-in/Gasolina,Híbrido Plug-in/Diesel,Elétrico',
            'value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'url' => 'nullable|url',
            'status' => 'nullable|in:Pendente,Aprovada,Reprovada,Enviado,Sem resposta',
            'transport_cost' => 'required|numeric|min:0',
            'ipo_cost' => 'nullable|numeric|min:0',
            'imt_cost' => 'nullable|numeric|min:0',
            'registration_cost' => 'nullable|numeric|min:0',
            'isv_cost' => 'nullable|numeric|min:0',
            'license_plate_cost' => 'nullable|numeric|min:0',
            'inspection_commission_cost' => 'nullable|numeric|min:0',
            'commission_cost' => 'nullable|numeric|min:0',
            'proposed_car_mileage' => 'nullable|integer|min:0',
            'proposed_car_year_month' => 'nullable|string',
            'proposed_car_value' => 'nullable|numeric|min:0',
            'proposed_car_notes' => 'nullable|string',
            'proposed_car_features' => 'nullable|string',
            'image' => 'nullable|image|mimes:webp,jpeg,png,jpg,gif,svg,avif|max:16000',
            'other_links' => 'nullable|array',
            'iuc_cost' => 'nullable|numeric|min:0',
        ]);

        // Remover 'image' do validated para não tentar salvar no banco
        unset($validated['image']);

        // Converter array de links para JSON
        if (isset($validated['other_links'])) {
            $validated['other_links'] = json_encode($validated['other_links']);
        }

        $oldStatus = $proposal->status;
        $requestedStatus = $validated['status'] ?? $oldStatus;
        unset($validated['status']);

        if ($proposal->isAccepted() && $requestedStatus !== 'Aprovada') {
            return back()->withInput()->withErrors(['status' => 'Esta cotação já foi aceite e não pode mudar de estado — para a anular, cancele a cotação convertida.']);
        }

        // Atualizar proposta (o estado é aplicado no fim, pelo serviço)
        $proposal->update($validated);

        // Remover atributos antigos
        $proposal->attributeValues()->delete();

        // Processar novos atributos
        if ($request->has('attributes')) {
            foreach ($request->input('attributes', []) as $attributeId => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                ProposalAttributeValue::create([
                    'proposal_id' => $proposal->id,
                    'attribute_id' => $attributeId,
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]);
            }
        }

        // Processar nova imagem (se existir, substitui a antiga)
        if ($request->hasFile('image')) {
            $this->handleImageUpload($proposal, $request->file('image'));
        }

        if ($requestedStatus && $requestedStatus !== $oldStatus) {
            try {
                app(ProposalAcceptanceService::class)->changeStatus($proposal->fresh(), $requestedStatus);
            } catch (ProposalAcceptanceException $e) {
                return redirect()->route('admin.v2.proposals.edit', $proposal->id)->with('error', $e->getMessage());
            }
        }

        // Registar mudança de estado na timeline (só se mudou)
        $newStatus = $proposal->fresh()->status;
        if ($oldStatus !== $newStatus) {
            $statusColors = [
                'Pendente'     => 'secondary',
                'Enviado'      => 'primary',
                'Aprovada'     => 'success',
                'Reprovada'    => 'danger',
                'Sem resposta' => 'warning',
            ];
            LeadActivity::log(
                $proposal->client_id,
                "Cotação #{$proposal->id}: estado alterado para {$newStatus}",
                '',
                'bi-arrow-repeat',
                $statusColors[$newStatus] ?? 'secondary'
            );
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
     * Processa upload de imagem para uma proposta
     * Elimina a imagem anterior se existir e guarda a nova
     * 
     * @param Proposal $proposal
     * @param \Illuminate\Http\UploadedFile $image
     * @return void
     */
    private function handleImageUpload(Proposal $proposal, $image)
    {
        // Eliminar imagem anterior (todas as variantes) se existir
        if ($proposal->images) {
            $existing = is_array($proposal->images) ? $proposal->images[0] : $proposal->images;
            (new ImageOptimizerService())->deleteAllVariants($existing);
        }

        // Optimizar: gera AVIF + WebP para thumb/medium/large.
        $optimizer = new ImageOptimizerService();
        $path      = $optimizer->optimize(
            $image,
            "proposals/{$proposal->client_id}/{$proposal->id}"
        );

        // Guardar caminho canónico (large AVIF) no banco de dados.
        $proposal->images = $path;
        $proposal->save();
    }

    /**
     * Aceita uma cotação diretamente a partir da listagem: marca-a como
     * Aprovada e cria a Cotação Convertida correspondente — o mesmo que
     * ProposalController::accept() faz no lado do cliente, mas disparado
     * pelo BO, sem os dados de contacto do formulário público (o cliente já
     * é conhecido) e sem o email/PDF de contrato automático (esse fluxo
     * fica reservado para quando é o próprio cliente a aceitar online).
     */
    public function accept($id, \App\Services\ProposalAcceptanceService $acceptance)
    {
        $proposal = Proposal::findOrFail($id);

        try {
            // No backoffice pode aceitar-se mesmo expirada/reprovada (ex. cliente
            // que aceitou por telefone); não envia emails nem contrato.
            $convertedProposal = $acceptance->accept($proposal, byClient: false);
        } catch (\App\Services\ProposalAcceptanceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'status' => $proposal->fresh()->status,
            'converted_proposal_id' => $convertedProposal->id,
            'redirect' => route('admin.v2.converted-proposals.edit', $convertedProposal->id),
        ]);
    }

    public function updateStatus(Request $request, $id, ProposalAcceptanceService $statuses)
    {
        $request->validate(['status' => 'required|string|in:Pendente,Aprovada,Reprovada,Enviado,Sem resposta']);

        $proposal = Proposal::findOrFail($id);

        try {
            $statuses->changeStatus($proposal, $request->status);
        } catch (ProposalAcceptanceException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'status' => $proposal->fresh()->status]);
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
