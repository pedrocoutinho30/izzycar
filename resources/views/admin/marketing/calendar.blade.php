@extends('layouts.admin-v2')

@section('title', 'Marketing Studio — Calendário')

@section('content')
@php
    $meses = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    $dias = ['Seg','Ter','Qua','Qui','Sex','Sáb','Dom'];
    $titulo = $view === 'week'
        ? $from->format('d/m') . ' a ' . $to->format('d/m/Y')
        : $meses[$date->month - 1] . ' ' . $date->year;
    $q = fn (array $x) => route('admin.marketing.calendar', array_filter(array_merge(['view' => $view, 'date' => $date->toDateString(), 'channel' => $channel], $x)));
@endphp

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-stars', 'label' => 'Marketing Studio', 'href' => route('admin.marketing.index')],
['icon' => 'bi bi-calendar3', 'label' => 'Calendário', 'href' => ''],
],
'title' => 'Calendário editorial',
'subtitle' => 'Planeamento interno: o Studio não publica nas redes. Agenda cada publicação no editor.',
])

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($pending > 0)
<div class="alert alert-warning">{{ $pending }} {{ $pending === 1 ? 'publicação' : 'publicações' }} do plano por selecionar: abre-as no calendário, cria a publicação real (viatura, testemunho, importação…) e elimina o marcador.</div>
@endif

<div class="d-flex flex-wrap gap-2 align-items-center mb-3">
    <a class="btn btn-outline-secondary" href="{{ $q(['date' => $prev]) }}"><i class="bi bi-chevron-left"></i></a>
    <div class="fw-semibold px-2">{{ $titulo }}</div>
    <a class="btn btn-outline-secondary" href="{{ $q(['date' => $next]) }}"><i class="bi bi-chevron-right"></i></a>
    <a class="btn btn-outline-secondary" href="{{ $q(['date' => now()->toDateString()]) }}">Hoje</a>
    <div class="btn-group ms-2">
        <a class="btn btn-outline-primary {{ $view === 'month' ? 'active' : '' }}" href="{{ $q(['view' => 'month']) }}">Mês</a>
        <a class="btn btn-outline-primary {{ $view === 'week' ? 'active' : '' }}" href="{{ $q(['view' => 'week']) }}">Semana</a>
    </div>
    <div class="btn-group">
        <a class="btn btn-outline-secondary {{ ! $channel ? 'active' : '' }}" href="{{ route('admin.marketing.calendar', ['view' => $view, 'date' => $date->toDateString()]) }}">Todas as redes</a>
        <a class="btn btn-outline-secondary {{ $channel === 'instagram' ? 'active' : '' }}" href="{{ $q(['channel' => 'instagram']) }}">Instagram</a>
        <a class="btn btn-outline-secondary {{ $channel === 'facebook' ? 'active' : '' }}" href="{{ $q(['channel' => 'facebook']) }}">Facebook</a>
    </div>
    <form method="POST" action="{{ route('admin.marketing.plan.generate') }}" class="ms-auto d-flex gap-2"
          onsubmit="return confirm('Criar 30 rascunhos (um por dia) a partir desta data? Não publica nada nem escreve legendas.')">
        @csrf
        <input type="date" name="start_date" class="form-control" required value="{{ old('start_date', now()->addDay()->toDateString()) }}" aria-label="Início do plano">
        <button class="btn btn-outline-primary text-nowrap"><i class="bi bi-calendar-plus"></i> Plano de 30 dias</button>
    </form>
</div>
@error('start_date')<div class="alert alert-danger">{{ $message }}</div>@enderror

@if($view === 'month')
<div class="row row-cols-7 g-1 text-center fw-semibold small mb-1" style="--bs-gutter-x:.25rem">@foreach($dias as $d)<div class="col">{{ $d }}</div>@endforeach</div>
<div class="row row-cols-7 g-1">
    @for($day = $from; $day <= $to; $day = $day->addDay())
        @php $dentro = $day->month === $date->month; $lista = $dentro ? ($posts[$day->toDateString()] ?? collect()) : collect(); @endphp
        <div class="col">
            <div class="border rounded p-1 {{ $dentro ? '' : 'bg-body-tertiary opacity-50' }} {{ $day->isToday() ? 'border-primary' : '' }}" style="min-height:120px">
                <div class="small {{ $day->isToday() ? 'fw-bold text-primary' : 'text-muted' }}">{{ $day->day }}</div>
                @foreach($lista as $p) @include('admin.marketing._calendar-post', ['p' => $p]) @endforeach
            </div>
        </div>
    @endfor
</div>
@else
<div class="row g-2">
    @for($day = $from; $day <= $to; $day = $day->addDay())
        <div class="col-md">
            <div class="border rounded p-2 h-100 {{ $day->isToday() ? 'border-primary' : '' }}" style="min-height:200px">
                <div class="fw-semibold small mb-2">{{ $dias[$day->dayOfWeekIso - 1] }} {{ $day->format('d/m') }}</div>
                @foreach(($posts[$day->toDateString()] ?? collect()) as $p) @include('admin.marketing._calendar-post', ['p' => $p]) @endforeach
            </div>
        </div>
    @endfor
</div>
@endif

@endsection
