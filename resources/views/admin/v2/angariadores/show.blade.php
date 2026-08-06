@extends('layouts.admin-v2')

@section('title', 'Angariador — ' . $angariador->name)

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Angariadores', 'href' => route('admin.v2.angariadores.index')],
        ['icon' => '', 'label' => $angariador->name],
    ],
    'title' => $angariador->name . ' ' . $angariador->last_name,
    'subtitle' => 'Detalhe de leads, propostas e comissões — sem impersonation',
    'extraActions' => [
        ['href' => route('admin.v2.users.edit', $angariador->id), 'label' => 'Editar Utilizador', 'icon' => 'bi-pencil'],
    ],
])

@include('components.admin.stats-cards', [
    'stats' => [
        ['title' => 'Leads Geradas', 'value' => $leadsCount, 'icon' => 'bi-funnel', 'color' => 'primary'],
        ['title' => 'Leads Convertidas', 'value' => $convertedCount, 'icon' => 'bi-person-check', 'color' => 'success'],
        ['title' => 'Taxa de Conversão', 'value' => $conversionRate . '%', 'icon' => 'bi-graph-up', 'color' => 'info'],
        ['title' => 'Comissão (paga / pendente)', 'value' => number_format($comissaoRecebida, 2, ',', '.') . ' € / ' . number_format($comissaoPendente, 2, ',', '.') . ' €', 'icon' => 'bi-cash-coin', 'color' => 'warning'],
    ]
])

<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-funnel"></i> Leads</h5>
        <span class="badge bg-secondary rounded-pill">{{ $leads->count() }} total</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Contacto</th>
                    <th>Registada em</th>
                    <th>Propostas</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr>
                    <td>{{ $lead->name }}</td>
                    <td>{{ $lead->email ?? $lead->phone ?? '—' }}</td>
                    <td>{{ $lead->created_at->format('d/m/Y') }}</td>
                    <td>{{ $lead->proposals->count() }}</td>
                    <td>{{ $lead->is_lead ? 'Lead' : 'Convertido' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.v2.leads.show', $lead->id) }}" class="btn btn-icon btn-primary-modern" title="Ver lead (vista admin)">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted p-4">Este angariador ainda não gerou leads.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
