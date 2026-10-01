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
use App\Models\Client;
use App\Services\ImportOpportunityService;
use Illuminate\Http\Request;

class FormProposalV2Controller extends Controller
{
    public function index(Request $request)
    {
        $query = FormProposal::withCount('opportunities')->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

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
        $data = $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
        ] + $this->vehicleRules($request), $this->vehicleMessages());

        $client = Client::findOrFail($data['client_id']);

        $formProposal = FormProposal::create(collect($data)->except('client_id')->all() + [
            'client_id' => $client->id,
            // name/email/phone são obrigatórios na tabela (vêm do formulário do site).
            'name' => $client->name,
            'email' => $client->email ?? '',
            'phone' => $client->phone ?? '',
            'origin' => FormProposal::ORIGIN_MANUAL,
            'status' => 'em_analise',
            'created_by' => $request->user()?->id,
            'angariador_code' => $client->angariador_code,
        ]);

        \App\Models\LeadActivity::log(
            $client->id,
            'Pedido de importação criado',
            "Pedido \"{$formProposal->label}\" criado manualmente no backoffice.",
            'bi-envelope-plus',
            'primary'
        );

        return redirect()
            ->route('admin.v2.form-proposals.show', $formProposal->id)
            ->with('success', 'Pedido criado. Já pode adicionar oportunidades.')
            ->withFragment('oportunidades');
    }

    public function update(Request $request, $id)
    {
        $formProposal = FormProposal::findOrFail($id);
        $formProposal->update($request->validate($this->vehicleRules($request, $formProposal), $this->vehicleMessages()));

        return redirect()->route('admin.v2.form-proposals.show', $formProposal->id)->with('success', 'Pedido atualizado.');
    }

    /** Campos do pedido editáveis no backoffice (manual ou do site). */
    private function vehicleRules(Request $request, ?FormProposal $current = null): array
    {
        // Marca e modelo vêm dos selects do catálogo (valores antigos do site
        // fora do catálogo só são aceites se não mudarem).
        [$brandRule, $modelRule] = \App\Support\VehicleCatalog::rules($request->input('brand'), $current?->brand, $current?->model);

        return [
            'title' => 'nullable|string|max:120',
            'brand' => ['nullable', 'string', 'max:100', $brandRule],
            'model' => ['nullable', 'string', 'max:100', $modelRule],
            'version' => 'nullable|string|max:255',
            'fuel' => 'nullable|string|max:50',
            'year_min' => 'nullable|integer|min:1950|max:' . (now()->year + 1),
            'km_max' => 'nullable|integer|min:0|max:2000000',
            'budget' => 'nullable|numeric|min:0|max:10000000',
            'message' => 'nullable|string|max:5000',
        ];
    }

    private function vehicleMessages(): array
    {
        return [
            'year_min.integer' => 'O ano tem de ser um número.',
            'km_max.integer' => 'Os quilómetros têm de ser um número inteiro.',
            'budget.numeric' => 'O orçamento tem de ser um número.',
        ];
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

        $request->validate([
            'status' => 'required|in:novo,em_analise,convertido,rejeitado,arquivado',
        ]);

        $formProposal->update(['status' => $request->status]);

        // Quando convertido, marcar o cliente como cliente real
        if ($request->status === 'convertido' && $formProposal->client_id) {
            $client = \App\Models\Client::find($formProposal->client_id);
            if ($client && $client->is_lead) {
                $client->convertToClient();
            }
        }

        return redirect()->back()->with('success', 'Estado atualizado!');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:form_proposals,id',
            'status' => 'required|in:novo,em_analise,convertido,rejeitado,arquivado',
        ]);

        $formProposals = FormProposal::whereIn('id', $data['ids'])->get();

        foreach ($formProposals as $formProposal) {
            $formProposal->update(['status' => $data['status']]);

            // Quando convertido, marcar o cliente como cliente real (mesma lógica do updateStatus individual)
            if ($data['status'] === 'convertido' && $formProposal->client_id) {
                $client = Client::find($formProposal->client_id);
                if ($client && $client->is_lead) {
                    $client->convertToClient();
                }
            }
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
