<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ProposalStatusUpdatedMail;
use App\Models\ConvertedProposal;
use App\Models\ConvertedProposalDocument;
use App\Models\Client;
use App\Models\Proposal;
use App\Models\User;
use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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
        $query = ConvertedProposal::with(['client', 'proposal', 'owner']);

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
            $statuses = $request->status === 'Cancelada' ? ConvertedProposal::CANCELLED_STATUSES : [$request->status];
            $query->whereIn('status', $statuses);
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
                'value' => ConvertedProposal::notCancelled()->where(fn ($q) => $q->where('status', '!=', 'Concluido')->orWhereNull('status'))->count(),
                'color' => 'warning',
                'icon' => 'bi-hourglass-split'
            ],
            [
                'title' => 'Concluídas',
                'value' => ConvertedProposal::where('status', 'Concluido')->count(),
                'color' => 'success',
                'icon' => 'bi-check-circle'
            ],
            [
                'title' => 'Canceladas',
                'value' => ConvertedProposal::whereIn('status', ConvertedProposal::CANCELLED_STATUSES)->count(),
                'color' => 'danger',
                'icon' => 'bi-x-circle'
            ]
        ];

        return view('admin.v2.converted-proposals.index', compact('convertedProposals', 'stats'));
    }

    /**
     * Pipeline kanban: uma coluna por etapa em curso; concluídas e canceladas
     * só têm uma zona onde largar. A mudança de estado vai pela rota
     * update-status (que decide se avisa o cliente por email).
     */
    public function kanban()
    {
        $proposals = ConvertedProposal::with('client:id,name,email')->orderByDesc('created_at')->get();

        $columns = array_values(array_diff(ConvertedProposal::PIPELINE_STATUSES, ['Concluido']));
        $grouped = $proposals->groupBy(fn (ConvertedProposal $p) => $p->boardColumn());

        return view('admin.v2.converted-proposals.kanban', [
            'columns' => $columns,
            'grouped' => $grouped,
            'doneCount' => $grouped->get('done', collect())->count(),
            'cancelledCount' => $grouped->get('cancelled', collect())->count(),
        ]);
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $proposals = Proposal::orderBy('created_at', 'desc')->get();
        $angariadores = User::role('angariador')->orderBy('name')->get();

        return view('admin.v2.converted-proposals.form', compact('clients', 'proposals', 'angariadores'));
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
            'owner_id' => 'nullable|exists:users,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'brand' => 'nullable|string|max:255',
            'modelCar' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'km' => 'nullable|integer',
            'matricula_origem' => 'nullable|string|max:50',
            'matricula_destino' => 'nullable|string|max:50',
            // Custos e pagamentos
            'vin' => 'nullable|string|max:17',
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
        $convertedProposal = ConvertedProposal::with(['client', 'proposal', 'documents', 'v3Vehicle.legalization'])->findOrFail($id);
        $clients = Client::orderBy('name')->get();
        $proposals = Proposal::orderBy('created_at', 'desc')->get();
        $angariadores = User::role('angariador')->orderBy('name')->get();
        $statusHistory = $convertedProposal->statusHistories()->orderBy('created_at', 'desc')->get();

        return view('admin.v2.converted-proposals.form', compact('convertedProposal', 'clients', 'proposals', 'angariadores', 'statusHistory'));
    }

    /**
     * Cria a viatura do cliente e a legalização a partir desta cotação
     * convertida (marca, modelo, versão, ano, km, matrícula, vendedor…).
     */
    public function createVehicle(Request $request, $id, \App\Services\ConvertedProposalService $service)
    {
        abort_unless($request->user()->can('vehicles.create'), 403, 'Não tem permissão para criar viaturas.');

        $convertedProposal = ConvertedProposal::findOrFail($id);
        $result = $service->createVehicleAndLegalization($convertedProposal);

        return redirect()
            ->route('admin.v3.vehicles.edit', $result['vehicle']->id)
            ->with('success', $result['created']
                ? "Viatura {$result['vehicle']->reference} e legalização criadas a partir da cotação convertida. Confirme o VIN e restantes dados."
                : 'Esta cotação convertida já tinha viatura.');
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
            'owner_id' => 'nullable|exists:users,id',
            'proposal_id' => 'nullable|exists:proposals,id',
            'brand' => 'nullable|string|max:255',
            'modelCar' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:255',
            'year' => 'nullable|integer',
            'km' => 'nullable|integer',
            'matricula_origem' => 'nullable|string|max:50',
            'matricula_destino' => 'nullable|string|max:50',
            // Custos e pagamentos
            'vin' => 'nullable|string|max:17',
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

        $oldStatus = $convertedProposal->status;
        $convertedProposal->update($validated);

        // O estado já está gravado: um cliente sem email ou uma falha no envio
        // não pode impedir a atualização.
        $client = $convertedProposal->client;
        if ($convertedProposal->wasChanged('status') && $client && filled($client->email)) {
            try {
                Mail::to($client->email)->bcc('izzycarpt@gmail.com')->send(
                    new ProposalStatusUpdatedMail($convertedProposal, $oldStatus, $convertedProposal->status, $client->name, $convertedProposal->matricula_destino)
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

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

    /**
     * Carrega um documento assinado pelo cliente (contrato, etc.), devolvido
     * fora do sistema (email, WhatsApp...) e guardado aqui manualmente pelo BO.
     */
    public function uploadSignedDocument(Request $request, $id)
    {
        $convertedProposal = ConvertedProposal::findOrFail($id);

        $request->validate([
            'ficheiro' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $file = $request->file('ficheiro');
        $path = $file->store("converted-proposals/{$convertedProposal->id}", 'local');

        ConvertedProposalDocument::create([
            'converted_proposal_id' => $convertedProposal->id,
            'tipo'          => 'assinado',
            'nome_original' => $file->getClientOriginalName(),
            'caminho'       => $path,
        ]);

        return back()->with('success', 'Documento assinado carregado com sucesso.');
    }

    /**
     * Mostra no browser o contrato de prestação de serviços desta cotação,
     * gerado agora com os dados atuais (o que foi enviado ao cliente fica em
     * "Documentos Gerados/Enviados").
     */
    public function contract($id)
    {
        $convertedProposal = ConvertedProposal::with('client', 'proposal')->findOrFail($id);
        abort_unless($convertedProposal->client, 404, 'Esta cotação não tem cliente associado.');

        $pdf = ContractService::generateContractPdf($convertedProposal->client, $convertedProposal);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="contrato_' . $convertedProposal->id . '.pdf"',
        ]);
    }

    /**
     * Download de um documento (gerado ou assinado) associado à cotação convertida.
     */
    public function downloadDocument($id, ConvertedProposalDocument $document)
    {
        abort_if((int) $document->converted_proposal_id !== (int) $id, 403);

        return Storage::disk('local')->download($document->caminho, $document->nome_original);
    }

    /**
     * Apaga um documento (gerado ou assinado) associado à cotação convertida.
     */
    public function deleteDocument($id, ConvertedProposalDocument $document)
    {
        abort_if((int) $document->converted_proposal_id !== (int) $id, 403);

        Storage::disk('local')->delete($document->caminho);
        $document->delete();

        return back()->with('success', 'Documento removido.');
    }
}
