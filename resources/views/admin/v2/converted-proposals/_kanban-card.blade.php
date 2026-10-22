@php
    $vehicle = trim(($converted->brand ?? '') . ' ' . ($converted->modelCar ?? '')) ?: 'Sem viatura';
@endphp
<a href="{{ route('admin.v2.converted-proposals.edit', $converted->id) }}" class="cpk-card" draggable="true"
   data-id="{{ $converted->id }}" data-title="{{ $vehicle }}"
   data-has-email="{{ filled($converted->client?->email) ? 1 : 0 }}"
   data-status-url="{{ route('admin.v2.converted-proposals.update-status', $converted->id) }}"
   title="{{ $vehicle }}{{ $converted->client ? ' · ' . $converted->client->name : '' }}{{ $converted->matricula_destino ? ' · ' . $converted->matricula_destino : '' }}">
    <div class="cpk-card__title">{{ $vehicle }}</div>
    <div class="cpk-card__sub">{{ $converted->client?->name ?? 'Sem cliente' }}</div>
    @if($converted->matricula_destino || $converted->matricula_origem)
    <div class="cpk-card__sub font-monospace">{{ $converted->matricula_destino ?: $converted->matricula_origem }}</div>
    @endif
</a>
