<?php

namespace App\Services;

use App\Models\Client;
use App\Models\LeadActivity;
use App\Models\User;
use Carbon\Carbon;

/**
 * Regras de negócio das leads, partilhadas pelo backoffice desktop
 * (LeadV2Controller) e pelo mobile. Os textos da timeline são os de sempre:
 * não mudar sem rever os ecrãs que os mostram.
 */
class LeadService
{
    /** Valores aceites em clients.lead_status nas rotas de leads. */
    public const STATUSES = ['nova', 'em_contacto', 'qualificada', 'fria', 'perdida'];

    /**
     * Labels da timeline. Não usar LeadStatus::labelOf: escreve "Em contacto"
     * e as atividades já gravadas dizem "Em Contacto".
     */
    private const STATUS_LABELS = [
        'nova'        => 'Nova',
        'em_contacto' => 'Em Contacto',
        'qualificada' => 'Qualificada',
        'fria'        => 'Fria',
        'perdida'     => 'Perdida',
    ];

    private const STATUS_COLORS = ['nova' => 'success', 'em_contacto' => 'info', 'qualificada' => 'primary', 'fria' => 'secondary', 'perdida' => 'danger'];

    /** Validação da criação manual de uma lead. */
    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:50',
            'origin'      => 'nullable|string|max:255',
            'lead_source' => 'nullable|string|max:255',
            'lead_status' => 'nullable|in:' . implode(',', self::STATUSES),
            'observation' => 'nullable|string|max:2000',
            'owner_id'    => 'nullable|exists:users,id',
        ];
    }

    /** Cria a lead com os dados já validados (o aviso de duplicados fica no controller). */
    public function create(array $data, ?User $by): Client
    {
        $lead = Client::create([
            ...$data,
            'is_lead'     => true,
            'lead_source' => $data['lead_source'] ?? 'manual',
            'lead_status' => $data['lead_status'] ?? 'nova',
            'origin'      => $data['origin'] ?? 'Manual BO',
        ]);

        LeadActivity::log(
            $lead->id,
            'Lead criado manualmente',
            'Criado por ' . ($by->name ?? '—') . '.',
            'bi-plus-circle-fill',
            'success'
        );

        return $lead;
    }

    public function changeStatus(Client $lead, string $status): void
    {
        $oldStatus = $lead->lead_status ?? 'nova';
        $lead->update(['lead_status' => $status]);

        LeadActivity::log(
            $lead->id,
            'Estado alterado: ' . (self::STATUS_LABELS[$oldStatus] ?? $oldStatus) . ' → ' . (self::STATUS_LABELS[$status] ?? $status),
            '',
            'bi-arrow-repeat',
            self::STATUS_COLORS[$status] ?? 'secondary'
        );
    }

    public function convert(Client $lead, ?User $by): void
    {
        $lead->convertToClient();

        LeadActivity::log(
            $lead->id,
            'Lead convertido em cliente',
            'Convertido manualmente pelo utilizador ' . ($by->name ?? '—') . '.',
            'bi-person-check-fill',
            'success'
        );
    }

    public function scheduleFollowup(Client $lead, string $at, ?string $note): void
    {
        $lead->update([
            'next_followup_at' => $at,
            'followup_note'    => $note,
        ]);

        LeadActivity::log(
            $lead->id,
            'Follow-up agendado para ' . Carbon::parse($at)->format('d/m/Y H:i'),
            $note ?? '',
            'bi-alarm-fill',
            'warning'
        );
    }

    /** Atividade manual (nota, chamada…) do utilizador autenticado. */
    public function logActivity(Client $lead, string $type, string $title, ?string $body): void
    {
        LeadActivity::logManual($lead->id, $type, $title, $body ?? '');
    }

    public function assignOwner(Client $lead, ?int $ownerId): void
    {
        $lead->update(['owner_id' => $ownerId ?: null]);
        // A relação pode já estar carregada com o dono anterior.
        $lead->unsetRelation('owner');

        LeadActivity::log(
            $lead->id,
            'Proprietário da lead alterado para: ' . ($lead->owner?->name ?? 'sem proprietário'),
            '',
            'bi-person-badge',
            'info'
        );
    }
}
