<?php

namespace App\Observers;

use App\Models\AuditLog;
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
