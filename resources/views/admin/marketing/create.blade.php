@extends('layouts.admin-v2')

@section('title', 'Criar publicação')

@section('content')

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-stars', 'label' => 'Marketing Studio', 'href' => route('admin.marketing.index')],
['icon' => 'bi bi-plus-lg', 'label' => 'Criar publicação', 'href' => ''],
],
'title' => 'Criar publicação',
'subtitle' => 'Escolhe o tipo de conteúdo. Só as publicações de viatura exigem uma viatura do catálogo.',
])

@php
$cards = [
    ['icon' => 'bi-car-front', 'title' => 'Viatura', 'text' => 'A partir de uma viatura do catálogo: dados e fotografias carregados automaticamente.', 'href' => route('admin.marketing.vehicles.create')],
    ['icon' => 'bi-hand-thumbs-up', 'title' => 'Recomendação', 'text' => 'Um carro de fora que recomendas, com fotografias e dados introduzidos por ti.', 'href' => route('admin.marketing.recommendations.create')],
    ['icon' => 'bi-lightbulb', 'title' => 'Educativo', 'text' => 'Dicas, mitos e verdades, perguntas frequentes, passo a passo. Escreves tu o conteúdo.', 'href' => route('admin.marketing.content.create', 'educativo')],
    ['icon' => 'bi-briefcase', 'title' => 'Serviço', 'text' => 'Apresenta um serviço da Izzycar: importação, legalização, consultoria…', 'href' => route('admin.marketing.content.create', 'servico')],
    ['icon' => 'bi-globe-europe-africa', 'title' => 'Importação', 'text' => 'Uma importação concluída, com autorização do cliente. Sem nome nem matrícula.', 'href' => route('admin.marketing.content.create', 'importacao')],
    ['icon' => 'bi-chat-quote', 'title' => 'Testemunho', 'text' => 'Um testemunho real de um cliente que autorizou o uso em publicações.', 'href' => route('admin.marketing.content.create', 'testemunho')],
];
@endphp

<div class="row g-3">
    @foreach($cards as $c)
    <div class="col-md-6 col-xl-4">
        <a href="{{ $c['href'] }}" class="card h-100 text-decoration-none text-reset shadow-sm">
            <div class="card-body">
                <div class="fs-2 mb-2" style="color:#6e0707"><i class="bi {{ $c['icon'] }}"></i></div>
                <h5 class="card-title mb-1">{{ $c['title'] }}</h5>
                <p class="card-text text-muted mb-0">{{ $c['text'] }}</p>
            </div>
        </a>
    </div>
    @endforeach
</div>

@endsection
