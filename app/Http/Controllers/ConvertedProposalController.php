<?php

namespace App\Http\Controllers;

use App\Models\ConvertedProposal;
use Illuminate\Http\Request;
use App\Services\ConvertedProposalService;
use Illuminate\Support\Facades\Validator;

class ConvertedProposalController extends Controller
{
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
