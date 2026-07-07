@extends('layouts.admin-v2')

@section('title', 'Cliente: ' . $client->name)

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-people', 'label' => 'Clientes', 'href' => route('admin.v2.clients.index')],
        ['icon' => 'bi bi-person', 'label' => $client->name, 'href' => ''],
    ],
    'title' => $client->name,
    'subtitle' => 'Detalhe do cliente',
    'actionHref' => route('admin.v2.clients.edit', $client->id),
    'actionLabel' => 'Editar Cliente',
])

{{-- Ações rápidas --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.v2.proposals.create', ['client_id' => $client->id]) }}"
       class="btn btn-primary-modern">
        <i class="bi bi-file-earmark-plus me-1"></i> Nova Cotação
    </a>
    <a href="{{ route('admin.v2.sales.create', ['client_id' => $client->id]) }}"
       class="btn btn-success-modern">
        <i class="bi bi-cash-coin me-1"></i> Registar Venda
    </a>
</div>

<div class="row g-4">

    {{-- ─── Informações do cliente ─── --}}
    <div class="col-lg-4">
        <div class="modern-card h-100">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-person-circle"></i>
                    Informações
                </h5>
            </div>
            <div class="modern-card-body">
                <ul class="list-unstyled mb-0">
                    @if($client->email)
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-envelope text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Email</small>
                            <a href="mailto:{{ $client->email }}">{{ $client->email }}</a>
                        </div>
                    </li>
                    @endif
                    @if($client->phone)
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-telephone text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Telefone</small>
                            <a href="tel:{{ $client->phone }}">{{ $client->phone }}</a>
                        </div>
                    </li>
                    @endif
                    @if($client->vat_number)
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-hash text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">NIF</small>
                            {{ $client->vat_number }}
                        </div>
                    </li>
                    @endif
                    @if($client->birth_date)
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-cake text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Data de Nascimento</small>
                            {{ \Carbon\Carbon::parse($client->birth_date)->format('d/m/Y') }}
                        </div>
                    </li>
                    @endif
                    @if($client->address || $client->city || $client->postal_code)
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Morada</small>
                            @if($client->address)<span>{{ $client->address }}</span><br>@endif
                            @if($client->postal_code || $client->city)
                                {{ $client->postal_code }} {{ $client->city }}
                            @endif
                        </div>
                    </li>
                    @endif
                    @if($client->client_type)
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-tag text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Tipo</small>
                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                {{ ucfirst($client->client_type) }}
                            </span>
                        </div>
                    </li>
                    @endif
                    @if($client->origin)
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-signpost text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Origem</small>
                            {{ $client->origin }}
                        </div>
                    </li>
                    @endif
                    <li class="mb-0 d-flex align-items-start gap-2">
                        <i class="bi bi-calendar text-muted mt-1"></i>
                        <div>
                            <small class="text-muted d-block">Registado em</small>
                            {{ $client->created_at->format('d/m/Y') }}
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ─── Consentimentos & observações ─── --}}
    <div class="col-lg-8">
        @if($client->observation)
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-chat-left-text"></i>
                    Observações
                </h5>
            </div>
            <div class="modern-card-body">
                <p class="mb-0">{{ $client->observation }}</p>
            </div>
        </div>
        @endif

        {{-- Consentimentos --}}
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-shield-check"></i>
                    Consentimentos
                </h5>
            </div>
            <div class="modern-card-body">
                <div class="d-flex gap-4">
                    <div>
                        <small class="text-muted d-block">Tratamento de Dados</small>
                        @if($client->data_processing_consent)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-check-circle me-1"></i>Aceite
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                <i class="bi bi-dash-circle me-1"></i>Não aceite
                            </span>
                        @endif
                    </div>
                    <div>
                        <small class="text-muted d-block">Newsletter</small>
                        @if($client->newsletter_consent)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-check-circle me-1"></i>Aceite
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                <i class="bi bi-dash-circle me-1"></i>Não aceite
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Vendas associadas --}}
        @if($client->sale->count())
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-receipt"></i>
                    Vendas
                </h5>
                <span class="badge bg-secondary rounded-pill">{{ $client->sale->count() }}</span>
            </div>
            <div class="modern-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Veículo</th>
                                <th>Data de Venda</th>
                                <th class="text-end">Preço de Venda</th>
                                <th class="text-end">Margem Bruta</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->sale as $sale)
                            @php
                                $vehicle = $sale->v3Vehicle;
                            @endphp
                            <tr>
                                <td>
                                    @if($vehicle)
                                        <strong>{{ $vehicle->brand }} {{ $vehicle->model }}</strong>
                                        @if($vehicle->reference)
                                            <br><small class="text-muted">Ref: {{ $vehicle->reference }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-end">
                                    {{ $sale->sale_price ? number_format($sale->sale_price, 2, ',', '.') . '€' : '—' }}
                                </td>
                                <td class="text-end">
                                    @if($sale->gross_margin !== null)
                                        <span class="{{ $sale->gross_margin >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($sale->gross_margin, 2, ',', '.') }}€
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($vehicle)
                                        <a href="{{ route('admin.v3.vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ─── Cotações ─── --}}
<div class="modern-card mt-2">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-file-earmark-text"></i>
            Cotações Enviadas
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $client->proposals->count() }}</span>
    </div>

    @if($client->proposals->isEmpty())
        <div class="modern-card-body text-center py-5">
            <i class="bi bi-file-earmark-text text-muted" style="font-size:2.5rem"></i>
            <p class="text-muted mt-2 mb-0">Sem cotações enviadas para este cliente.</p>
        </div>
    @else
        <div class="modern-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Veículo</th>
                            <th>Ano</th>
                            <th>Valor</th>
                            <th>Estado</th>
                            <th>Data</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($client->proposals->sortByDesc('created_at') as $proposal)
                        @php
                            $statusColors = [
                                'enviada'   => 'info',
                                'aceite'    => 'success',
                                'recusada'  => 'danger',
                                'pendente'  => 'warning',
                                'expirada'  => 'secondary',
                            ];
                            $statusColor = $statusColors[$proposal->status] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>
                                @if($proposal->proposal_code)
                                    <span class="badge bg-dark-subtle text-dark border border-dark-subtle font-monospace">
                                        {{ $proposal->proposal_code }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $proposal->brand }} {{ $proposal->model }}</strong>
                                @if($proposal->version)
                                    <br><small class="text-muted">{{ $proposal->version }}</small>
                                @endif
                            </td>
                            <td>{{ $proposal->year ?? '—' }}</td>
                            <td>
                                {{ $proposal->value ? number_format($proposal->value, 2, ',', '.') . '€' : '—' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} border border-{{ $statusColor }}-subtle">
                                    {{ ucfirst($proposal->status ?? 'pendente') }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $proposal->created_at->format('d/m/Y') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('proposals.edit', $proposal->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- ─── Histórico do cliente ─── --}}
@php
    $timeline = collect();

    // Vendas
    foreach ($client->sale as $sale) {
        $vehicle = $sale->v3Vehicle;
        $timeline->push([
            'date'  => $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date) : $sale->created_at,
            'type'  => 'sale',
            'icon'  => 'bi-cash-coin',
            'color' => 'success',
            'badge' => 'Venda',
            'title' => $vehicle ? $vehicle->brand . ' ' . $vehicle->model : 'Viatura sem referência',
            'sub'   => $sale->sale_price ? number_format($sale->sale_price, 0, ',', '.') . ' €' : null,
            'link'  => route('admin.v2.sales.edit', $sale->id),
        ]);
    }

    // Cotações
    foreach ($client->proposals as $proposal) {
        $statusLabels = [
            'Pendente'     => ['label' => 'Pendente',     'color' => 'warning'],
            'Aprovada'     => ['label' => 'Aprovada',     'color' => 'success'],
            'Recusada'     => ['label' => 'Recusada',     'color' => 'danger'],
            'Sem resposta' => ['label' => 'Sem resposta', 'color' => 'secondary'],
            'Expirada'     => ['label' => 'Expirada',     'color' => 'secondary'],
        ];
        $st = $statusLabels[$proposal->status] ?? ['label' => $proposal->status ?? 'pendente', 'color' => 'secondary'];
        $timeline->push([
            'date'        => $proposal->created_at,
            'type'        => 'proposal',
            'icon'        => 'bi-file-earmark-text',
            'color'       => 'primary',
            'badge'       => 'Cotação',
            'title'       => trim(($proposal->brand ?? '') . ' ' . ($proposal->model ?? '')),
            'sub'         => $proposal->value ? number_format($proposal->value, 0, ',', '.') . ' €' : null,
            'status_label'=> $st['label'],
            'status_color'=> $st['color'],
            'link'        => route('admin.v2.proposals.edit', $proposal->id),
        ]);
    }

    // Simuladores de custo
    foreach ($client->costSimulators as $sim) {
        $timeline->push([
            'date'  => $sim->created_at,
            'type'  => 'simulator',
            'icon'  => 'bi-calculator',
            'color' => 'warning',
            'badge' => 'Simulador',
            'title' => trim(($sim->brand ?? '') . ' ' . ($sim->model ?? '')),
            'sub'   => $sim->total_cost ? 'Total estimado: ' . number_format($sim->total_cost, 0, ',', '.') . ' €' : null,
            'link'  => route('admin.v2.cost-simulators.show', $sim->id),
        ]);
    }

    // Atividades / notas internas
    foreach ($client->activities as $activity) {
        $timeline->push([
            'date'  => $activity->created_at,
            'type'  => 'activity',
            'icon'  => $activity->icon ?? 'bi-circle-fill',
            'color' => $activity->color ?? 'secondary',
            'badge' => $activity->title,
            'title' => $activity->body,
            'sub'   => null,
            'link'  => null,
        ]);
    }

    $timeline = $timeline->sortByDesc('date');
@endphp

<div class="modern-card mt-4">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-clock-history"></i>
            Histórico do Cliente
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $timeline->count() }} evento(s)</span>
    </div>

    @if($timeline->isEmpty())
        <div class="modern-card-body text-center py-5">
            <i class="bi bi-clock text-muted" style="font-size:2.5rem"></i>
            <p class="text-muted mt-2 mb-0">Ainda não existe histórico associado a este cliente.</p>
        </div>
    @else
        <div class="modern-card-body px-4 py-3">
            <div class="iz-timeline">
                @foreach($timeline as $event)
                @php $isLink = !empty($event['link']); @endphp
                <div class="iz-timeline-item">
                    <div class="iz-timeline-icon bg-{{ $event['color'] }} bg-opacity-15 text-{{ $event['color'] }}">
                        <i class="bi {{ $event['icon'] }}"></i>
                    </div>
                    <div class="iz-timeline-content">
                        <div class="iz-timeline-header">
                            <span class="iz-timeline-badge badge-{{ $event['color'] }}">{{ $event['badge'] }}</span>
                            @if(isset($event['status_label']))
                                <span class="iz-timeline-badge badge-{{ $event['status_color'] }}">{{ $event['status_label'] }}</span>
                            @endif
                            <span class="iz-timeline-date">{{ $event['date']->format('d/m/Y') }}</span>
                        </div>
                        @if($isLink)
                            <a href="{{ $event['link'] }}" class="iz-timeline-title">{{ $event['title'] ?: '—' }}</a>
                        @else
                            <div class="iz-timeline-title iz-timeline-title--plain">{{ $event['title'] ?: '—' }}</div>
                        @endif
                        @if($event['sub'])
                            <div class="iz-timeline-sub">{{ $event['sub'] }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@endsection

@push('styles')
<style>
.iz-timeline {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 0;
}
.iz-timeline::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
    z-index: 0;
}
.iz-timeline-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: .75rem 0;
    position: relative;
    z-index: 1;
}
.iz-timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}
.iz-timeline-content { flex: 1; min-width: 0; padding-top: .2rem; }
.iz-timeline-header {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .35rem;
    margin-bottom: .25rem;
}
.iz-timeline-badge {
    display: inline-block;
    padding: .15rem .5rem;
    border-radius: 20px;
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .02em;
}
.badge-primary    { background: rgba(13,110,253,.12);  color: #0d6efd; }
.badge-success    { background: rgba(25,135, 84,.12);  color: #198754; }
.badge-warning    { background: rgba(255,193,  7,.15); color: #856404; }
.badge-danger     { background: rgba(220, 53, 69,.12); color: #dc3545; }
.badge-secondary  { background: rgba(108,117,125,.12); color: #6c757d; }
.badge-info       { background: rgba( 13,202,240,.12); color: #0dcaf0; }

.iz-timeline-date { font-size: .75rem; color: #999; margin-left: auto; white-space: nowrap; }
.iz-timeline-title {
    font-weight: 600;
    font-size: .9rem;
    color: #1a1a1a;
    text-decoration: none;
    display: block;
}
a.iz-timeline-title:hover { color: var(--admin-primary, #b30000); text-decoration: underline; }
.iz-timeline-title--plain { color: #333; }
.iz-timeline-sub { font-size: .8rem; color: #6c757d; margin-top: .15rem; }
</style>
@endpush
