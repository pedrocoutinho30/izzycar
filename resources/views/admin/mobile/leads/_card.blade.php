{{-- Cartão de uma lead na lista mobile. Espera $lead (com owner) e $phones (PhoneNumberService). --}}
@php
    $estado = \App\Enums\LeadStatus::tryFrom($lead->lead_status ?? 'nova');
    $tel = $phones->telUrl($lead->phone);
    $whatsapp = $phones->whatsappUrl($lead->phone);
@endphp
<div class="m-card m-lead-card">
    <a href="{{ route('admin.mobile.leads.show', $lead->id) }}" class="m-lead-card__link">
        <div class="d-flex justify-content-between align-items-start gap-2">
            <strong class="m-lead-card__name">{{ $lead->name }}</strong>
            <span class="badge bg-{{ $estado?->color() ?? 'secondary' }} flex-shrink-0">{{ \App\Enums\LeadStatus::labelOf($lead->lead_status) }}</span>
        </div>
        <div class="text-muted small mt-1">
            {{ \App\Enums\LeadSource::labelOf($lead->lead_source) ?? 'Outro' }} · {{ $lead->created_at->format('d/m/Y') }}
            @if($lead->owner)
                · <i class="bi bi-person-badge"></i> {{ $lead->owner->name }}
            @endif
        </div>
        @if($lead->next_followup_at)
            <div class="small mt-1 {{ $lead->next_followup_at->isPast() ? 'text-danger' : 'text-muted' }}">
                <i class="bi bi-alarm"></i> {{ $lead->next_followup_at->format('d/m/Y H:i') }}
            </div>
        @endif
    </a>
    @if($tel || $lead->email)
        <div class="m-contact-actions">
            @if($tel)
                <a href="{{ $tel }}" class="btn btn-outline-secondary" aria-label="Ligar"><i class="bi bi-telephone"></i></a>
            @endif
            @if($whatsapp)
                <a href="{{ $whatsapp }}" class="btn btn-outline-success" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
            @endif
            @if($lead->email)
                <a href="mailto:{{ $lead->email }}" class="btn btn-outline-primary" aria-label="Email"><i class="bi bi-envelope"></i></a>
            @endif
        </div>
    @endif
</div>
