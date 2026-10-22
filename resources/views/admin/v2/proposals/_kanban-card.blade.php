@php
    $vehicle = trim(implode(' ', array_filter([$proposal->brand, $proposal->model, $proposal->year]))) ?: 'Sem viatura';
    $accepted = $proposal->status === 'Aprovada';
    $expired = in_array($proposal->status, ['Pendente', 'Enviado'], true) && $proposal->isExpired();
@endphp
<a href="{{ route('admin.v2.proposals.edit', $proposal->id) }}" class="prk-card" @if(!$accepted) draggable="true" @endif
   data-id="{{ $proposal->id }}" data-title="{{ $vehicle }}"
   data-has-email="{{ filled($proposal->client?->email) ? 1 : 0 }}"
   data-status-url="{{ route('admin.v2.proposals.updateStatus', $proposal->id) }}"
   title="{{ $vehicle }}{{ $proposal->client ? ' · ' . $proposal->client->name : '' }}{{ $accepted ? ' · já aceite: para anular, cancele a cotação convertida' : '' }}">
    <div class="prk-card__title">{{ $vehicle }}</div>
    <div class="prk-card__sub">{{ $proposal->client?->name ?? 'Sem cliente' }}</div>
    <div class="prk-card__foot">
        <span><i class="bi bi-calendar3"></i> {{ $proposal->created_at->format('d/m/Y') }}</span>
        @if($expired)<span class="badge text-bg-warning fw-normal">Expirada</span>@endif
        @if($accepted)<i class="bi bi-lock-fill" title="Já aceite"></i>@endif
    </div>
</a>
