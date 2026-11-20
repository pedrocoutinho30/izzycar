@extends('layouts.admin-v2')

@section('title', 'Marketing Studio — Dashboard')

@section('content')

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-stars', 'label' => 'Marketing Studio', 'href' => route('admin.marketing.index')],
['icon' => 'bi bi-speedometer2', 'label' => 'Dashboard', 'href' => ''],
],
'title' => 'Dashboard do Marketing Studio',
'subtitle' => 'Contagens reais das publicações guardadas. Não inclui alcance nem interações (o Studio não publica).',
'actionHref' => route('admin.marketing.create'),
'actionLabel' => 'Criar publicação'
])

@if($s['total'] === 0)
    @include('components.admin.empty-state', ['icon' => 'stars', 'title' => 'Ainda não há publicações', 'message' => 'Cria a primeira para veres aqui os números.'])
@else
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2"><div class="card h-100"><div class="card-body"><div class="text-muted small">Total</div><div class="fs-2 fw-bold">{{ $s['total'] }}</div></div></div></div>
    @foreach($statuses as $st)
    <div class="col-6 col-lg-2">
        <a class="card h-100 text-decoration-none text-reset" href="{{ route('admin.marketing.index', ['status' => $st->value]) }}">
            <div class="card-body"><div class="text-muted small">{{ $st->label() }}</div><div class="fs-2 fw-bold">{{ $s['by_status'][$st->value] }}</div></div>
        </a>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-4"><div class="card h-100"><div class="card-header fw-semibold">Por categoria</div>
        <ul class="list-group list-group-flush">
            @foreach($types as $t)
            <li class="list-group-item d-flex justify-content-between"><a href="{{ route('admin.marketing.index', ['type' => $t->value]) }}" class="text-reset text-decoration-none">{{ $t->label() }}</a><b>{{ $s['by_type'][$t->value] }}</b></li>
            @endforeach
        </ul></div></div>
    <div class="col-lg-4"><div class="card h-100"><div class="card-header fw-semibold">Prontas a publicar</div>
        @include('admin.marketing._dashboard-list', ['posts' => $s['ready'], 'empty' => 'Nenhuma publicação aprovada ou agendada.', 'showDate' => true])</div></div>
    <div class="col-lg-4"><div class="card h-100"><div class="card-header fw-semibold">Em revisão</div>
        @include('admin.marketing._dashboard-list', ['posts' => $s['review'], 'empty' => 'Nada em revisão.', 'showDate' => false])</div></div>
</div>

<div class="card mb-4"><div class="card-header fw-semibold">Recentes <span class="text-muted fw-normal small">· publicadas este mês: {{ $s['published_this_month'] }}</span></div>
    @include('admin.marketing._dashboard-list', ['posts' => $s['recent'], 'empty' => '', 'showDate' => false])</div>
@endif

@endsection
