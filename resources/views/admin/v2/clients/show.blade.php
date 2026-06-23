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

@endsection
