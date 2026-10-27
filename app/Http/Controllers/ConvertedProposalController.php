<?php

namespace App\Http\Controllers;

use App\Models\ConvertedProposal;
use Illuminate\Http\Request;
use App\Services\ConvertedProposalService;
use Illuminate\Support\Facades\Validator;

class ConvertedProposalController extends Controller
{
    /**
     * Lista todas as propostas convertidas.
     */
    public function index()
    {
        $proposals = ConvertedProposal::with(['client', 'proposal'])
            ->orderBy('created_at', 'desc')
            ->paginate(15); // paginação de 15 por página

        return view('converted_proposals.index', compact('proposals'));
    }

    /**
     * Mostra o formulário de edição.
     */
    public function edit(ConvertedProposal $convertedProposal)
    {

        $status_history = $convertedProposal->statusHistories()->orderBy('created_at', 'desc')->get();
        return view('converted_proposals.edit', compact('convertedProposal', 'status_history'));
    }

    /**
     * Atualiza os dados da proposta convertida.
     */
    public function update(Request $request, ConvertedProposal $convertedProposal)
    {

        $validator = \Validator::make($request->all(), [
            'status' => 'required|in:Iniciada,Negociação Carro,Pagamento do Carro,Transporte,IPO,DAV,ISV,IMT,Matriculação,Entrega,Registo automóvel,Concluido,Cancelado',
            'url' => 'nullable|url',
            'client_id' => 'required|exists:clients,id',
            'proposal_id' => 'required|exists:proposals,id',
            'inspecao_origem_pago' => 'nullable|boolean',
            'transporte_pago' => 'nullable|boolean',
            'ipo_pago' => 'nullable|boolean',
            'isv_pago' => 'nullable|boolean',
            'imt_pago' => 'nullable|boolean',
            'matricula_pago_impressa' => 'nullable|boolean',
            'registo_pago' => 'nullable|boolean',
            'primeira_tranche_pago' => 'nullable|boolean',
            'segunda_tranche_pago' => 'nullable|boolean',
            'carro_pago' => 'nullable|boolean',
            'observacoes' => 'nullable|string',
            'matricula_destino' => 'nullable|string',
            'matricula_origem' => 'nullable|string',
            'custo_inspecao_origem' => 'nullable|numeric',
            'custo_transporte' => 'nullable|numeric',
            'custo_ipo' => 'nullable|numeric',
            'isv' => 'nullable|numeric',
            'custo_imt' => 'nullable|numeric',
            'custo_matricula' => 'nullable|numeric',
            'custo_registo_automovel' => 'nullable|numeric',
            'valor_primeira_tranche' => 'nullable|numeric',
            'valor_segunda_tranche' => 'nullable|numeric',
            'valor_carro' => 'nullable|numeric',
            'valor_comissao' => 'nullable|numeric',
            'valor_comissao_final' => 'nullable|numeric',
            'contactos_stand' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);
        if ($validator->fails()) {

            return response()->json($validator->errors()); // ver erros
        }
        $data = $validator->validated();
        $convertedProposal->update($data);

        return redirect()->route('converted-proposals.index')
            ->with('success', 'Proposta atualizada com sucesso!');
    }

    public function updateStatus(Request $request, $id, ConvertedProposalService $service)
    {
        $request->validate($service->statusRules(), $service->statusMessages());

        $convertedProposal = ConvertedProposal::findOrFail($id);
        $result = $service->changeStatus($convertedProposal, $request->status, $request->boolean('notify', true));

        // Sem alteração: não grava nem envia nada ao cliente.
        if (!$result['changed']) {
            return response()->json(['success' => true, 'message' => 'O estado já era este.', 'oldStatus' => $result['oldStatus'], 'newStatus' => $result['newStatus']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Estado atualizado com sucesso!',
            'notified' => $result['notified'],
            'oldStatus' => $result['oldStatus'],
            'newStatus' => $result['newStatus'],
        ]);
    }

    public function detailTimeline($brand, $model, $version, $code)
    {
        // Só pelo código aleatório: o id sequencial deixaria ver processos alheios.
        $convertedProposal = ConvertedProposal::where('tracking_code', $code)->firstOrFail();
        $status_history = $convertedProposal->statusHistories()->orderBy('created_at', 'desc')->get();
        $allStatus = [
            ['status' => 'Iniciada', 'icon' => 'fa fa-play'],
            ['status' => 'Negociação Carro', 'icon' => 'fa fa-car'],
            ['status' => 'Pagamento do Carro', 'icon' => 'bi bi-credit-card'],

            ['status' => 'Transporte', 'icon' => 'fa fa-truck'],
            ['status' => 'IPO', 'icon' => 'fa fa-car'],
            ['status' => 'DAV', 'icon' => 'bi bi-list-check'],
            ['status' => 'ISV', 'icon' => 'bi bi-cash-stack'],
            ['status' => 'Matriculação', 'icon' => 'bi bi-card-text'],
            ['status' => 'IMT', 'icon' => 'bi bi-building'],
            ['status' => 'Entrega', 'icon' => 'bi bi-box-seam'],
            ['status' => 'Registo automóvel', 'icon' => 'bi bi-file-earmark'],
            ['status' => 'Concluido', 'icon' => 'bi bi-check-circle'],
        ];
        return view('converted_proposals.timeline', compact('convertedProposal', 'status_history', 'allStatus'));
    }
}
