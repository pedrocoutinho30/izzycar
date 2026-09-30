<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChecklistItemStatus;
use App\Enums\OpportunityStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImportOpportunityRequest;
use App\Http\Requests\UpdateImportOpportunityRequest;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Services\ImportOpportunityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Oportunidades de um Pedido de Importação — carros/anúncios em análise como
 * possível solução para o pedido. A grelha vive na página do pedido
 * (form-proposals/show); o detalhe tem página própria.
 */
class ImportOpportunityController extends Controller
{
    public function __construct(private ImportOpportunityService $opportunities)
    {
    }

    public function store(StoreImportOpportunityRequest $request, FormProposal $formProposal)
    {
        $opportunity = $this->opportunities->create(
            $formProposal,
            $request->opportunityData(),
            $request->file('photo'),
            $request->user()
        );

        return redirect()
            ->route('admin.v2.form-proposals.show', $formProposal->id)
            ->with('success', "Oportunidade \"{$opportunity->title}\" adicionada.")
            ->withFragment('oportunidades');
    }

    public function show(FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $opportunity->load(['checklistEntries', 'contacts.user', 'creator', 'seller.activeContacts', 'sellerContact']);

        return view('admin.v2.form-proposals.opportunities.show', [
            'formProposal' => $formProposal,
            'opportunity' => $opportunity,
            'checklists' => $this->opportunities->checklistsFor($opportunity),
            'checklistStatuses' => $this->opportunities->checklistStatuses($opportunity),
            'progress' => $this->opportunities->progress($opportunity),
        ]);
    }

    public function update(UpdateImportOpportunityRequest $request, FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $this->opportunities->update(
            $opportunity,
            $request->opportunityData(),
            $request->file('photo'),
            $request->boolean('remove_photo')
        );

        return redirect()
            ->route('admin.v2.form-proposals.opportunities.show', [$formProposal->id, $opportunity->id])
            ->with('success', 'Oportunidade atualizada.')
            ->withFragment($request->input('section', ''));
    }

    public function destroy(FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $this->opportunities->delete($opportunity);

        return redirect()
            ->route('admin.v2.form-proposals.show', $formProposal->id)
            ->with('success', 'Oportunidade eliminada.')
            ->withFragment('oportunidades');
    }

    public function updateStatus(Request $request, FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $data = $request->validate(
            ['status' => ['required', Rule::enum(OpportunityStatus::class)]],
            ['status.required' => 'Escolha um estado.', 'status.enum' => 'Estado inválido.']
        );

        $opportunity->update($data);
        $status = $opportunity->status;

        return response()->json([
            'success' => true,
            'status' => ['value' => $status->value, 'label' => $status->label(), 'color' => $status->color(), 'icon' => $status->icon()],
        ]);
    }

    public function updateChecklist(Request $request, FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $data = $request->validate([
            'item_id' => 'required|integer',
            'status' => ['required', Rule::enum(ChecklistItemStatus::class)],
        ], [
            'status.enum' => 'Estado inválido.',
        ]);

        $this->opportunities->setChecklistStatus(
            $opportunity,
            (int) $data['item_id'],
            ChecklistItemStatus::from($data['status']),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'progress' => $this->opportunities->progress($opportunity)->toArray(),
        ]);
    }

    public function updateNotes(Request $request, FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $data = $request->validate(['notes' => 'nullable|string|max:20000'], [
            'notes.max' => 'As notas são demasiado longas.',
        ]);

        $opportunity->update($data);

        return response()->json(['success' => true, 'saved_at' => $opportunity->updated_at->format('H:i')]);
    }
}
