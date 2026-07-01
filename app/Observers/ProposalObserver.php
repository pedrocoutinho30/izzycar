<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\LeadActivity;
use App\Models\Proposal;

class ProposalObserver
{
    public function created(Proposal $proposal): void
    {
        $vehicle = implode(' ', array_filter([$proposal->brand, $proposal->model, $proposal->version]));
        AuditLog::recordCreated($proposal, "Cotação #{$proposal->id} criada" . ($vehicle ? " — {$vehicle}" : ''));
    }

    public function updated(Proposal $proposal): void
    {
        $ignored = ['updated_at', 'created_at'];
        $dirty = array_diff_key($proposal->getDirty(), array_flip($ignored));
        if (empty($dirty)) return;

        // Converter lead em cliente quando cotação é aprovada
        if ($proposal->wasChanged('status') && $proposal->status === 'Aprovada') {
            $client = $proposal->client;
            if ($client && $client->is_lead) {
                $client->convertToClient();
                LeadActivity::log(
                    $client->id,
                    'Lead convertido em cliente',
                    "Cotação #{$proposal->id} aprovada — lead convertido automaticamente em cliente.",
                    'bi-person-check-fill',
                    'success'
                );
            }
        }

        $vehicle = implode(' ', array_filter([$proposal->brand, $proposal->model, $proposal->version]));
        AuditLog::recordUpdated(
            $proposal,
            "Cotação #{$proposal->id} actualizada" . ($vehicle ? " — {$vehicle}" : ''),
            array_intersect_key($proposal->getOriginal(), $dirty),
            $dirty
        );
    }

    public function deleted(Proposal $proposal): void
    {
        $vehicle = implode(' ', array_filter([$proposal->brand, $proposal->model, $proposal->version]));
        AuditLog::recordDeleted($proposal, "Cotação #{$proposal->id} eliminada" . ($vehicle ? " — {$vehicle}" : ''));
    }
}
