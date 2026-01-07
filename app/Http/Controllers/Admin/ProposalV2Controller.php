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
use App\Models\Proposal;
use App\Models\Client;
use App\Models\Brand;
use App\Models\VehicleAttribute;
use App\Models\ProposalAttributeValue;
use App\Models\AttributeGroup;
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
        // Iniciar query builder
        $query = Proposal::with('client')->orderBy('created_at', 'desc');

        // FILTRO: Status
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

        // Paginar resultados (10 por página)
        $proposals = $query->paginate(10)->withQueryString();

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
        return view('admin.v2.proposals.index', compact('proposals', 'clients', 'stats'));
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
        // Obter clientes para dropdown
        $clients = Client::orderBy('name')->get();

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
            'transport_cost' => 1250,
            'ipo_cost' => 100,
            'imt_cost' => 65,
            'registration_cost' => 55,
            'isv_cost' => 0,
            'license_plate_cost' => 40,
            'inspection_commission_cost' => 350,
            'commission_cost' => 861,
            'iuc_cost' => 0,
        ];

        return view('admin.v2.proposals.form', compact('clients', 'brands', 'attributes', 'defaults'));
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
            'client_id' => 'required|exists:clients,id',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'mileage' => 'nullable|integer|min:0',
            'engine_capacity' => 'nullable|numeric|min:0',
            'co2' => 'nullable|numeric|min:0',
            'fuel' => 'nullable|in:Gasolina,Diesel,Híbrido Plug-in/Gasolina,Híbrido Plug-in/Diesel,Elétrico',
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

        // Gerar código único para a proposta
        $validated['proposal_code'] = strtoupper(substr($validated['brand'], 0, 1) .
            substr($validated['model'], 0, 1) .
            Str::random(8));

        // Converter array de links para JSON
        if (isset($validated['other_links'])) {
            $validated['other_links'] = json_encode($validated['other_links']);
        }

        // Definir status default se não fornecido
        $validated['status'] = $validated['status'] ?? 'Pendente';

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

        // Processar upload de imagem (se existir)
        if ($request->hasFile('image')) {
            $this->handleImageUpload($proposal, $request->file('image'));
        }

        // Redirect com mensagem de sucesso
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
        // Obter proposta com relacionamentos
        $proposal = Proposal::with('attributeValues')->findOrFail($id);

        // Obter clientes para dropdown
        $clients = Client::orderBy('name')->get();

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
            'transport_cost' => 1250,
            'ipo_cost' => 100,
            'imt_cost' => 65,
            'registration_cost' => 55,
            'isv_cost' => 0,
            'license_plate_cost' => 40,
            'inspection_commission_cost' => 350,
            'commission_cost' => 861,
        ];

        return view('admin.v2.proposals.form', compact('proposal', 'clients', 'brands', 'attributes', 'attributeValues', 'images', 'defaults'));
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
            'fuel' => 'nullable|in:Gasolina,Diesel,Híbrido Plug-in/Gasolina,Híbrido Plug-in/Diesel,Elétrico',
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

        // Atualizar proposta
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

        // Redirect
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

        // Eliminar imagem do storage (se existir)
        if ($proposal->images && Storage::disk('public')->exists($proposal->images)) {
            Storage::disk('public')->delete($proposal->images);
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
        // Eliminar imagem anterior se existir
        if ($proposal->images && Storage::disk('public')->exists($proposal->images)) {
            Storage::disk('public')->delete($proposal->images);
        }

        $extension = $image->getClientOriginalExtension();

        // Nome personalizado: brand_model_version_yearmonth_id
        $fileName = Str::slug($proposal->brand) . '_' .
            Str::slug($proposal->model) . '_' .
            ($proposal->version ? Str::slug($proposal->version) . '_' : '') .
            ($proposal->proposed_car_year_month ?? 'NA') . '_' .
            $proposal->id . '.' . $extension;

        // Guardar na pasta correta
        $path = $image->storeAs(
            "proposals/{$proposal->client_id}/{$proposal->id}",
            $fileName,
            'public'
        );

        // Atualizar caminho da imagem no banco de dados
        $proposal->images = $path;
        $proposal->save();
    }
}
