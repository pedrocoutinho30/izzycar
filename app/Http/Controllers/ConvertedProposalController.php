<?php

namespace App\Http\Controllers;

use App\Models\ConvertedProposal;
use Illuminate\Http\Request;
use App\Mail\ProposalStatusUpdatedMail;
use App\Models\Client;
use Illuminate\Support\Facades\Mail;
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
            'status' => 'required|in:Iniciada,Negociação Carro,Transporte,IPO,DAV,ISV,IMT,Matriculação,Entrega,Registo automóvel,Concluido,Cancelado',
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

    public function updateStatus(Request $request, $id)
    {
        $convertedProposal = ConvertedProposal::findOrFail($id);
        $oldStatus = $convertedProposal->status;
        $newStatus = $request->status;

        $client = Client::find($convertedProposal->client_id);
        $client_name = $client->name;
        $convertedProposal->status = $newStatus;

        $convertedProposal->save();
        // Enviar email para o cliente
        Mail::to('geral@izzycar.pt')->send(
            new ProposalStatusUpdatedMail($convertedProposal, $oldStatus, $newStatus, $client_name,  $convertedProposal->matricula_destino)
        );

        return response()->json([
            'success' => true,
            'message' => 'Estado atualizado com sucesso!',
            'oldStatus' => $oldStatus,
            'newStatus' => $newStatus,
        ]);
    }

    public function detailTimeline($brand, $model, $version, $id)
    {
        $convertedProposal = ConvertedProposal::findOrFail($id);
        $status_history = $convertedProposal->statusHistories()->orderBy('created_at', 'desc')->get();
        $allStatus = [
            ['status' => 'Iniciada', 'icon' => 'fa fa-play'],
            ['status' => 'Negociação Carro', 'icon' => 'fa fa-car'],

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
