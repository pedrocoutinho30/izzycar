@extends('layouts.admin-v2')

@section('title', 'Resultados da Pesquisa')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Resultados da Pesquisa'],
    ],
    'title' => 'Resultados da Pesquisa',
    'subtitle' => $term !== ''
        ? $total . ' resultado' . ($total != 1 ? 's' : '') . ' para "' . $term . '"'
        : 'Escreva um termo na barra de pesquisa para começar',
])

<form action="{{ route('admin.v2.search.results') }}" method="GET" class="modern-card mb-4">
    <div class="p-3 d-flex gap-2">
        <input type="text" name="q" value="{{ $term }}" class="form-control" placeholder="Pesquisar clientes, leads, cotações, viaturas...">
        <button type="submit" class="btn btn-primary-modern"><i class="bi bi-search me-1"></i> Pesquisar</button>
    </div>
</form>

@forelse($groups as $group)
<div class="modern-card mb-4">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <span class="badge bg-{{ $group['color'] }} me-2"><i class="bi {{ $group['icon'] }} me-1"></i>{{ $group['label'] }}</span>
        </h5>
        <span class="badge bg-secondary rounded-pill">{{ $group['count'] }}</span>
    </div>
    <div class="list-group list-group-flush">
        @foreach($group['items'] as $item)
        <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-semibold">{{ $item['title'] }}</div>
                @if($item['subtitle'])
                <div class="small text-muted">{{ $item['subtitle'] }}</div>
                @endif
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
        </a>
        @endforeach
    </div>
</div>
@empty
@if($term !== '')
@include('components.admin.empty-state', [
    'icon' => 'bi-search',
    'title' => 'Sem resultados',
    'message' => 'Não foram encontrados resultados para "' . $term . '".',
])
@endif
@endforelse

@endsection
