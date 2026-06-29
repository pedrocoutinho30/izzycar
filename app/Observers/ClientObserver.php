<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Client;

class ClientObserver
{
    public function created(Client $client): void
    {
        $type = $client->is_lead ? 'Lead' : 'Cliente';
        AuditLog::recordCreated($client, "{$type} criado: {$client->name}");
    }

    public function updated(Client $client): void
    {
        // Ignorar actualizações de timestamps e campos irrelevantes para o log
        $ignored = ['updated_at', 'created_at'];
        $dirty = array_diff_key($client->getDirty(), array_flip($ignored));
        if (empty($dirty)) return;

        $type = $client->is_lead ? 'Lead' : 'Cliente';
        AuditLog::recordUpdated(
            $client,
            "{$type} actualizado: {$client->name}",
            array_intersect_key($client->getOriginal(), $dirty),
            $dirty
        );
    }

    public function deleted(Client $client): void
    {
        $type = $client->is_lead ? 'Lead' : 'Cliente';
        AuditLog::recordDeleted($client, "{$type} eliminado: {$client->name}");
    }
}
