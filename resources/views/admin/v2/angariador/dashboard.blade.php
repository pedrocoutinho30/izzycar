@extends('layouts.admin-v2')

@section('title', 'O Meu Painel')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Início'],
    ],
    'title' => 'O Meu Painel',
    'subtitle' => 'Resumo das leads e comissões associadas ao seu código de angariador',
])

@include('components.admin.stats-cards', [
    'stats' => [
        ['title' => 'Leads Geradas', 'value' => $leadsCount, 'icon' => 'bi-funnel', 'color' => 'primary'],
        ['title' => 'Leads Convertidas', 'value' => $convertedCount, 'icon' => 'bi-person-check', 'color' => 'success'],
        ['title' => 'Taxa de Conversão', 'value' => $conversionRate . '%', 'icon' => 'bi-graph-up', 'color' => 'info'],
        ['title' => 'Comissão Pendente', 'value' => number_format($comissaoPendente, 2, ',', '.') . ' €', 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
    ]
])

@php
    $meuLink = auth()->user()->referral_code
        ? route('frontend.form-import') . '?angariador=' . auth()->user()->referral_code
        : null;
@endphp

<div class="modern-card mb-4">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-link-45deg"></i> O Meu Link de Angariador</h5>
    </div>
    <div class="p-4">
        @if($meuLink)
        <p class="text-muted small mb-2">Partilhe sempre este link com os seus contactos — é o que garante que a lead fica associada a si.</p>
        <div class="input-group">
            <input type="text" class="form-control" id="meuLinkInput" value="{{ $meuLink }}" readonly>
            <button type="button" class="btn btn-outline-secondary" onclick="copiarMeuLink()">
                <i class="bi bi-clipboard me-1"></i> Copiar
            </button>
        </div>
        @else
        <div class="alert alert-warning mb-0">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Ainda não tem um código de angariador definido. Contacte a administração.
        </div>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-funnel"></i> As Minhas Leads</h5>
            </div>
            <div class="p-4 text-center">
                <a href="{{ route('admin.angariador.leads') }}" class="btn btn-primary-modern">
                    <i class="bi bi-arrow-right me-1"></i> Ver todas as leads
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-cash-coin"></i> As Minhas Comissões</h5>
            </div>
            <div class="p-4 text-center">
                <div class="mb-3 text-muted">
                    Recebido: <strong class="text-success">{{ number_format($comissaoRecebida, 2, ',', '.') }} €</strong>
                    &nbsp;·&nbsp;
                    Pendente: <strong class="text-warning">{{ number_format($comissaoPendente, 2, ',', '.') }} €</strong>
                </div>
                <a href="{{ route('admin.angariador.comissoes') }}" class="btn btn-primary-modern">
                    <i class="bi bi-arrow-right me-1"></i> Ver detalhe de comissões
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function copiarMeuLink() {
    const input = document.getElementById('meuLinkInput');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        input.nextElementSibling?.focus();
    });
}
</script>
@endpush
