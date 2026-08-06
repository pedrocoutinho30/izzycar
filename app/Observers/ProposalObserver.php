<?php

namespace App\Observers;

use App\Mail\ProposalSentMail;
use App\Models\AuditLog;
use App\Models\LeadActivity;
use App\Models\Proposal;
use Illuminate\Support\Facades\Mail;

class ProposalObserver
{
    private const FOLLOWUP_DAYS = 2;

    public function created(Proposal $proposal): void
    {
        $vehicle = implode(' ', array_filter([$proposal->brand, $proposal->model, $proposal->version]));
        AuditLog::recordCreated($proposal, "Cotação #{$proposal->id} criada" . ($vehicle ? " — {$vehicle}" : ''));

        if ($proposal->status === 'Enviado') {
            $this->notifyProposalSent($proposal);
        }
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

        // Notificar cliente + angariador quando a cotação passa a "Enviado"
        if ($proposal->wasChanged('status') && $proposal->status === 'Enviado') {
            $this->notifyProposalSent($proposal);
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

    /**
     * Ao enviar uma cotação: email ao cliente com o link, email ao
     * angariador da lead (se existir) com instruções de acompanhamento, e
     * agendamento automático de um follow-up (só se a lead ainda não tiver
     * nenhum agendado, para não substituir um plano já existente).
     */
    private function notifyProposalSent(Proposal $proposal): void
    {
        $client = $proposal->client;
        if (!$client || !$proposal->proposal_code) {
            return;
        }

        $url = route('proposals.detail', $proposal->proposal_code);

        if ($client->email) {
            Mail::to($client->email)->send(new ProposalSentMail($proposal, $url, false, self::FOLLOWUP_DAYS));
        }

        $owner = $client->owner;
        if ($owner && $owner->email) {
            Mail::to($owner->email)->send(new ProposalSentMail($proposal, $url, true, self::FOLLOWUP_DAYS));
        }

        LeadActivity::log(
            $client->id,
            "Cotação #{$proposal->id} enviada ao cliente",
            'Email enviado com o link de acesso.' . ($owner ? ' Angariador notificado.' : ''),
            'bi-envelope-fill',
            'primary'
        );

        if (!$client->next_followup_at) {
            $followupAt = now()->addDays(self::FOLLOWUP_DAYS);

            $client->update([
                'next_followup_at' => $followupAt,
                'followup_note' => "Ponto de situação — cotação #{$proposal->id} enviada ao cliente.",
            ]);

            LeadActivity::log(
                $client->id,
                'Follow-up agendado automaticamente para ' . $followupAt->format('d/m/Y H:i'),
                'Gerado ao enviar a cotação — confirmar se o cliente recebeu a proposta.',
                'bi-alarm-fill',
                'warning'
            );
        }
    }
}
