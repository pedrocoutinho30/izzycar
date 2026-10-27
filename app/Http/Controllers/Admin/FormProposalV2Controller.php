<?php

/**
 * ==================================================================
 * FORM PROPOSALS CONTROLLER V2
 * ==================================================================
 * 
 * Controller para formulários de proposta recebidos do site
 * 
 * @author Izzycar Team
 * @version 2.0
 * ==================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormProposal;
use App\Services\FormProposalService;
use App\Services\ImportOpportunityService;
use Illuminate\Http\Request;

class FormProposalV2Controller extends Controller
{
    /** Estados que se escolhem à mão ("convertido" é automático). */
    public const MANUAL_STATUSES = FormProposalService::MANUAL_STATUSES;

    public function __construct(private FormProposalService $formProposals)
    {
    }

    public function index(Request $request)
    {
        $query = $this->formProposals->query($request->only(['status', 'search']));

        $formProposals = $query->paginate(15)->withQueryString();

        // Stats
        $stats = [
            ['title' => 'Total', 'value' => FormProposal::count(), 'icon' => 'envelope', 'color' => 'primary'],
            ['title' => 'Novos', 'value' => FormProposal::where(fn ($q) => $q->where('status', 'novo')->orWhereNull('status'))->count(), 'icon' => 'envelope-exclamation', 'color' => 'warning'],
            ['title' => 'Em Análise', 'value' => FormProposal::where('status', 'em_analise')->count(), 'icon' => 'hourglass-split', 'color' => 'info'],
            ['title' => 'Convertidos', 'value' => FormProposal::whereNotNull('proposal_id')->count(), 'icon' => 'check-circle', 'color' => 'success'],
        ];

        return view('admin.v2.form-proposals.index', compact('formProposals', 'stats'));
    }

    /**
     * Pedido manual, criado na lead/cliente — serve para agrupar oportunidades
     * quando o cliente não preencheu o formulário do site (ex. cliente antigo
     * que quer um segundo carro).
     */
    public function store(Request $request)
    {
        $data = $request->validate(
            $this->formProposals->manualRules($request->input('brand')),
            $this->formProposals->vehicleMessages()
        );

        $formProposal = $this->formProposals->createManual($data, $request->user());

        return redirect()
            ->route('admin.v2.form-proposals.show', $formProposal->id)
            ->with('success', 'Pedido criado. Já pode adicionar oportunidades.')
            ->withFragment('oportunidades');
    }

    public function update(Request $request, $id)
    {
        $formProposal = FormProposal::findOrFail($id);
        $formProposal->update($request->validate(
            $this->formProposals->vehicleRules($request->input('brand'), $formProposal),
            $this->formProposals->vehicleMessages()
        ));

        return redirect()->route('admin.v2.form-proposals.show', $formProposal->id)->with('success', 'Pedido atualizado.');
    }

    public function show($id, ImportOpportunityService $opportunityService)
    {
        $formProposal = FormProposal::with(['client', 'creator', 'opportunities.checklistEntries', 'opportunities.seller'])->findOrFail($id);

        $opportunityProgress = $formProposal->opportunities
            ->mapWithKeys(fn ($opportunity) => [$opportunity->id => $opportunityService->progress($opportunity)]);

        return view('admin.v2.form-proposals.show', compact('formProposal', 'opportunityProgress'));
    }

    public function updateStatus(Request $request, $id)
    {
        $formProposal = FormProposal::findOrFail($id);

        $request->validate($this->formProposals->statusRules(), $this->formProposals->statusMessages());

        $this->formProposals->changeStatus($formProposal, $request->status);

        return redirect()->back()->with('success', 'Estado atualizado!');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:form_proposals,id',
            'status' => 'required|in:' . implode(',', self::MANUAL_STATUSES),
        ]);

        $formProposals = FormProposal::whereIn('id', $data['ids'])->get();

        foreach ($formProposals as $formProposal) {
            $formProposal->update(['status' => $data['status']]);
        }

        return response()->json(['success' => true, 'count' => $formProposals->count()]);
    }

    public function destroy($id, ImportOpportunityService $opportunityService)
    {
        $formProposal = FormProposal::with('client')->findOrFail($id);
        $client = $formProposal->client;

        // As oportunidades são eliminadas em cascata pela FK; as fotos não.
        $opportunityService->deletePhotosFor($formProposal);
        $formProposal->delete();

        // Os pedidos vivem dentro da lead/cliente: voltar para lá.
        $back = match (true) {
            $client === null => route('admin.v2.form-proposals.index'),
            (bool) $client->is_lead => route('admin.v2.leads.show', $client->id),
            default => route('admin.v2.clients.show', $client->id),
        };

        return redirect($back)->with('success', 'Pedido eliminado!');
    }
}
