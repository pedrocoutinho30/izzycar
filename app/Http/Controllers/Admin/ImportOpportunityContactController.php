<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOpportunityContactRequest;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\ImportOpportunityContact;
use App\Services\ImportOpportunityService;

/**
 * Histórico de contactos com o vendedor de uma Oportunidade.
 */
class ImportOpportunityContactController extends Controller
{
    public function __construct(private ImportOpportunityService $opportunities)
    {
    }

    public function store(StoreOpportunityContactRequest $request, FormProposal $formProposal, ImportOpportunity $opportunity)
    {
        $this->opportunities->logContact($opportunity, $request->validated(), $request->user());

        return redirect()
            ->route('admin.v2.form-proposals.opportunities.show', [$formProposal->id, $opportunity->id])
            ->with('success', 'Contacto registado.')
            ->withFragment('historico');
    }

    public function destroy(FormProposal $formProposal, ImportOpportunity $opportunity, ImportOpportunityContact $contact)
    {
        $this->opportunities->deleteContact($opportunity, $contact);

        return redirect()
            ->route('admin.v2.form-proposals.opportunities.show', [$formProposal->id, $opportunity->id])
            ->with('success', 'Registo removido.')
            ->withFragment('historico');
    }
}
