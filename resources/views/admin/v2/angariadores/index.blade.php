@extends('layouts.admin-v2')

@section('title', 'Angariadores')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Angariadores'],
    ],
    'title' => 'Angariadores',
    'subtitle' => 'Listagem, métricas e gestão de acesso dos angariadores',
    'actionHref' => route('admin.v2.angariadores.comissoes'),
    'actionLabel' => 'Ver Comissões',
    'extraActions' => [
        ['href' => route('admin.angariador.manual'), 'label' => 'Manual do Angariador', 'icon' => 'bi-book'],
    ],
])

@include('components.admin.stats-cards', [
    'stats' => [
        ['title' => 'Total Leads', 'value' => $totals['leadsCount'], 'icon' => 'bi-funnel', 'color' => 'primary'],
        ['title' => 'Total Convertidas', 'value' => $totals['convertedCount'], 'icon' => 'bi-person-check', 'color' => 'success'],
        ['title' => 'Comissão Paga', 'value' => number_format($totals['comissaoRecebida'], 2, ',', '.') . ' €', 'icon' => 'bi-check-circle', 'color' => 'info'],
        ['title' => 'Comissão Pendente', 'value' => number_format($totals['comissaoPendente'], 2, ',', '.') . ' €', 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
    ]
])

@if($pending->isNotEmpty())
<div class="modern-card border-warning">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-person-plus text-warning"></i> Candidaturas Pendentes</h5>
        <span class="badge bg-warning text-dark rounded-pill">{{ $pending->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Contacto</th>
                    <th>Localização</th>
                    <th>Candidatura</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($pending as $candidate)
                <tr>
                    <td>{{ $candidate->name }} {{ $candidate->last_name }}</td>
                    <td>{{ $candidate->email }} · {{ $candidate->phone }}</td>
                    <td>{{ $candidate->location ?? '—' }}</td>
                    <td>{{ $candidate->created_at->diffForHumans() }}</td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <a href="{{ route('admin.v2.users.edit', $candidate->id) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <form action="{{ route('admin.v2.angariadores.approve', $candidate->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Aprovar</button>
                            </form>
                            <form action="{{ route('admin.v2.angariadores.reject', $candidate->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Rejeitar esta candidatura?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Rejeitar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@include('components.admin.filter-bar', [
    'filters' => [
        ['type' => 'date', 'name' => 'date_from', 'label' => 'De', 'value' => request('date_from')],
        ['type' => 'date', 'name' => 'date_to', 'label' => 'Até', 'value' => request('date_to')],
    ],
    'action' => route('admin.v2.angariadores.index'),
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-people"></i> Angariadores</h5>
        <span class="badge bg-secondary rounded-pill">{{ $rows->count() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Comissão</th>
                    <th>Leads</th>
                    <th>Convertidas</th>
                    <th>Taxa Conversão</th>
                    <th>Comissão Paga</th>
                    <th>Comissão Pendente</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td>{{ $row['angariador']->name }} {{ $row['angariador']->last_name }}</td>
                    <td>{{ $row['angariador']->commission_fixed_value !== null ? number_format($row['angariador']->commission_fixed_value, 2, ',', '.') . ' €' : '—' }}</td>
                    <td>{{ $row['leadsCount'] }}</td>
                    <td>{{ $row['convertedCount'] }}</td>
                    <td>{{ $row['conversionRate'] }}%</td>
                    <td class="text-success">{{ number_format($row['comissaoRecebida'], 2, ',', '.') }} €</td>
                    <td class="text-warning">{{ number_format($row['comissaoPendente'], 2, ',', '.') }} €</td>
                    <td class="text-end">
                        <div class="item-actions d-inline-flex gap-1">
                            <a href="{{ route('admin.v2.angariadores.show', $row['angariador']->id) }}"
                               class="btn btn-icon btn-primary-modern" title="Ver detalhe">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form action="{{ route('admin.v2.angariadores.impersonate', $row['angariador']->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-icon btn-secondary-modern" title="Ver como este angariador">
                                    <i class="bi bi-person-badge"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.v2.angariadores.destroy', $row['angariador']->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Este angariador tem {{ $row['leadsCount'] }} lead(s), {{ $row['convertedCount'] }} venda(s) convertida(s), {{ number_format($row['comissaoPendente'], 2, ',', '.') }} € de comissão pendente e {{ number_format($row['comissaoRecebida'], 2, ',', '.') }} € já paga. Ao apagar a conta de {{ addslashes($row['angariador']->name) }}, essas leads e comissões não são apagadas, mas deixam de estar associadas a um angariador. Tem a certeza que quer continuar?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-danger-modern" title="Apagar angariador">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted p-4">Ainda não existem utilizadores com o papel "Angariador".</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
