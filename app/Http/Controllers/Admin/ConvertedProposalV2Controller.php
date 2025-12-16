<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConvertedProposal;
use App\Models\Client;
use App\Models\Proposal;
use Illuminate\Http\Request;

/**
 * ConvertedProposalV2Controller
 * 
 * Gestão de propostas convertidas - propostas que foram aceites pelos clientes
 * e estão em processo de concretização
 * 
 * As propostas convertidas passam por várias etapas:
 * - Inspeção de origem
 * - Transporte
 * - IPO
 * - ISV
 * - IMT
 * - Matrícula
 * - Registo automóvel
 */
class ConvertedProposalV2Controller extends Controller
{
    /**
     * Lista todas as propostas convertidas
     */
    public function index(Request $request)
    {
        // Query base com relacionamentos
        $query = ConvertedProposal::with(['client', 'proposal']);

        // Filtro de pesquisa (cliente ou matrícula)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('matricula_origem', 'like', "%{$search}%")
                  ->orWhere('matricula_destino', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Paginação
        $convertedProposals = $query->orderBy('created_at', 'desc')
                                   ->paginate(12)
                                   ->withQueryString();

        // Estatísticas
        $stats = [
            [
                'title' => 'Total Convertidas',
                'value' => ConvertedProposal::count(),
                'color' => 'primary',
                'icon' => 'bi-check2-circle'
            ],
            [
                'title' => 'Em Processo',
                'value' => ConvertedProposal::where('status', 'Em Processo')->count(),
                'color' => 'warning',
                'icon' => 'bi-hourglass-split'
            ],
            [
                'title' => 'Concluídas',
                'value' => ConvertedProposal::where('status', 'Concluída')->count(),
                'color' => 'success',
                'icon' => 'bi-check-circle'
            ],
            [
                'title' => 'Canceladas',
                'value' => ConvertedProposal::where('status', 'Cancelada')->count(),
                'color' => 'danger',
                'icon' => 'bi-x-circle'
            ]
        ];

        return view('admin.v2.converted-proposals.index', compact('convertedProposals', 'stats'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $proposals = Proposal::orderBy('created_at', 'desc')->get();
        
        return view('admin.v2.converted-proposals.form', compact('clients', 'proposals'));
    }

    /**
     * Guarda nova proposta convertida
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:500',
            'client_id' => 'required|exists:clients,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'brand' => 'nullable|string|max:255',
            'modelCar' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'km' => 'nullable|integer',
            'matricula_origem' => 'nullable|string|max:50',
            'matricula_destino' => 'nullable|string|max:50',
            // Custos e pagamentos
            'custo_inspecao_origem' => 'nullable|numeric',
            'inspecao_origem_pago' => 'nullable|boolean',
            'custo_transporte' => 'nullable|numeric',
            'transporte_pago' => 'nullable|boolean',
            'custo_ipo' => 'nullable|numeric',
            'ipo_pago' => 'nullable|boolean',
            'isv' => 'nullable|numeric',
            'isv_pago' => 'nullable|boolean',
            'custo_imt' => 'nullable|numeric',
            'imt_pago' => 'nullable|boolean',
            'custo_matricula' => 'nullable|numeric',
            'matricula_pago_impressa' => 'nullable|boolean',
            'custo_registo_automovel' => 'nullable|numeric',
            'registo_pago' => 'nullable|boolean',
            // Tranches
            'valor_primeira_tranche' => 'nullable|numeric',
            'valor_segunda_tranche' => 'nullable|numeric',
            'primeira_tranche_pago' => 'nullable|boolean',
            'segunda_tranche_pago' => 'nullable|boolean',
            // Valores gerais
            'valor_carro' => 'nullable|numeric',
            'carro_pago' => 'nullable|boolean',
            'valor_comissao' => 'nullable|numeric',
            'valor_comissao_final' => 'nullable|numeric',
            'contactos_stand' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        ConvertedProposal::create($validated);

        return redirect()->route('admin.v2.converted-proposals.index')
                        ->with('success', 'Proposta convertida criada com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $convertedProposal = ConvertedProposal::with(['client', 'proposal'])->findOrFail($id);
        $clients = Client::orderBy('name')->get();
        $proposals = Proposal::orderBy('created_at', 'desc')->get();
        
        return view('admin.v2.converted-proposals.form', compact('convertedProposal', 'clients', 'proposals'));
    }

    /**
     * Atualiza proposta convertida
     */
    public function update(Request $request, $id)
    {
        $convertedProposal = ConvertedProposal::findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|string|max:255',
            'url' => 'nullable|url|max:500',
            'client_id' => 'required|exists:clients,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'brand' => 'nullable|string|max:255',
            'modelCar' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'km' => 'nullable|integer',
            'matricula_origem' => 'nullable|string|max:50',
            'matricula_destino' => 'nullable|string|max:50',
            // Custos e pagamentos
            'custo_inspecao_origem' => 'nullable|numeric',
            'inspecao_origem_pago' => 'nullable|boolean',
            'custo_transporte' => 'nullable|numeric',
            'transporte_pago' => 'nullable|boolean',
            'custo_ipo' => 'nullable|numeric',
            'ipo_pago' => 'nullable|boolean',
            'isv' => 'nullable|numeric',
            'isv_pago' => 'nullable|boolean',
            'custo_imt' => 'nullable|numeric',
            'imt_pago' => 'nullable|boolean',
            'custo_matricula' => 'nullable|numeric',
            'matricula_pago_impressa' => 'nullable|boolean',
            'custo_registo_automovel' => 'nullable|numeric',
            'registo_pago' => 'nullable|boolean',
            // Tranches
            'valor_primeira_tranche' => 'nullable|numeric',
            'valor_segunda_tranche' => 'nullable|numeric',
            'primeira_tranche_pago' => 'nullable|boolean',
            'segunda_tranche_pago' => 'nullable|boolean',
            // Valores gerais
            'valor_carro' => 'nullable|numeric',
            'carro_pago' => 'nullable|boolean',
            'valor_comissao' => 'nullable|numeric',
            'valor_comissao_final' => 'nullable|numeric',
            'contactos_stand' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        $convertedProposal->update($validated);

        return redirect()->route('admin.v2.converted-proposals.index')
                        ->with('success', 'Proposta convertida atualizada com sucesso!');
    }

    /**
     * Elimina proposta convertida
     */
    public function destroy($id)
    {
        $convertedProposal = ConvertedProposal::findOrFail($id);
        $convertedProposal->delete();

        return redirect()->route('admin.v2.converted-proposals.index')
                        ->with('success', 'Proposta convertida eliminada com sucesso!');
    }
}
