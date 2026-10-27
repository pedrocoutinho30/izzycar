<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Enums\ChecklistItemStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImportOpportunityRequest;
use App\Http\Requests\StoreOpportunityContactRequest;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\ImportOpportunityContact;
use App\Permissions\PermissionService;
use App\Services\FormProposalService;
use App\Services\ImportOpportunityService;
use Illuminate\Http\Request;

/**
 * Oportunidades de um pedido na versão mobile. As ações do desktop devolvem
 * JSON (estado, checklist, notas) ou redirecionam para as vistas do desktop,
 * por isso aqui há ações próprias que usam os mesmos serviços e voltam à
 * página mobile. As rotas usam scopeBindings: uma oportunidade de outro pedido dá 404.
 */
class ImportOpportunityController extends Controller
{
    public function __construct(
        private ImportOpportunityService $opportunities,
        private FormProposalService $formProposals,
        private PermissionService $permissions,
    ) {
    }

    public function store(StoreImportOpportunityRequest $request, FormProposal $formProposal)
    {
        $this->authorizeUpdate($request, $formProposal);

        $opportunity = $this->opportunities->create($formProposal, $request->opportunityData(), $request->file('photo'), $request->user());

        return redirect()->route('admin.mobile.form-proposals.opportunities.show', [$formProposal->id, $opportunity->id])
            ->with('success', "Oportunidade \"{$opportunity->title}\" adicionada.");
    }

    public function show(Request $request, FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $this->authorizeView($request, $formProposal);

        $opportunity->load(['checklistEntries', 'contacts.user', 'seller', 'sellerContact']);

        return view('admin.mobile.form-proposals.opportunities.show', [
            'formProposal' => $formProposal,
            'opportunity' => $opportunity,
            'checklists' => $this->opportunities->checklistsFor($opportunity),
            'checklistStatuses' => $this->opportunities->checklistStatuses($opportunity),
            'progress' => $this->opportunities->progress($opportunity),
            'canUpdate' => $this->permissions->allows($request->user(), 'form-proposals', 'update'),
        ]);
    }

    public function status(Request $request, FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $this->authorizeUpdate($request, $formProposal);

        $data = $request->validate($this->formProposals->opportunityStatusRules(), $this->formProposals->opportunityStatusMessages());
        $status = $this->formProposals->changeOpportunityStatus($opportunity, $data['status']);

        return $this->backToOpportunity($formProposal, $opportunity, "Estado: {$status->label()}.");
    }

    public function checklist(Request $request, FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $this->authorizeUpdate($request, $formProposal);

        $data = $request->validate($this->formProposals->checklistRules(), $this->formProposals->checklistMessages());
        $this->opportunities->setChecklistStatus($opportunity, (int) $data['item_id'], ChecklistItemStatus::from($data['status']), $request->user());

        return $this->backToOpportunity($formProposal, $opportunity, 'Checklist atualizada.', 'checklist');
    }

    public function notes(Request $request, FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $this->authorizeUpdate($request, $formProposal);

        $data = $request->validate($this->formProposals->notesRules(), $this->formProposals->notesMessages());
        $this->formProposals->saveOpportunityNotes($opportunity, $data);

        return $this->backToOpportunity($formProposal, $opportunity, 'Notas guardadas.', 'notas');
    }

    public function storeContact(StoreOpportunityContactRequest $request, FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $this->authorizeUpdate($request, $formProposal);

        $this->opportunities->logContact($opportunity, $request->validated(), $request->user());

        return $this->backToOpportunity($formProposal, $opportunity, 'Contacto registado.', 'historico');
    }

    public function destroyContact(Request $request, FormProposal $formProposal, ImportOpportunity $opportunity, ImportOpportunityContact $contact)
    {
        $this->authorizeUpdate($request, $formProposal);

        $this->opportunities->deleteContact($opportunity, $contact);

        return $this->backToOpportunity($formProposal, $opportunity, 'Registo removido.', 'historico');
    }

    /** Oportunidades só para quem vê todos os pedidos (como no desktop) e pode ver este. */
    private function authorizeView(Request $request, FormProposal $formProposal): void
    {
        $user = $request->user();

        abort_unless(
            $this->permissions->allows($user, 'form-proposals', 'view', null, 'all')
                && $this->permissions->allows($user, 'form-proposals', 'view', $formProposal),
            403
        );
    }

    /** Mexer nas oportunidades é editar o pedido (sem âmbito), como no desktop. */
    private function authorizeUpdate(Request $request, FormProposal $formProposal): void
    {
        $this->authorizeView($request, $formProposal);
        abort_unless($this->permissions->allows($request->user(), 'form-proposals', 'update'), 403);
    }

    private function backToOpportunity(FormProposal $formProposal, ImportOpportunity $opportunity, string $message, ?string $fragment = null)
    {
        $redirect = redirect()->route('admin.mobile.form-proposals.opportunities.show', [$formProposal->id, $opportunity->id])
            ->with('success', $message);

        return $fragment ? $redirect->withFragment($fragment) : $redirect;
    }
}
