<?php

namespace App\Http\Requests;

class StoreImportOpportunityRequest extends ImportOpportunityRequest
{
    public function opportunityData(): array
    {
        // Campos com default na BD não podem ir como null no insert.
        return array_filter(parent::opportunityData(), fn ($value) => $value !== null);
    }
}
