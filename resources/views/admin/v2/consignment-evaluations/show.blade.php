@extends('layouts.admin-v2')

@section('title', 'Avaliação ' . $evaluation->reference)

@section('content')

@php
    $sl = \App\Models\ConsignmentEvaluation::$statusLabels[$evaluation->status] ?? ['label' => $evaluation->status, 'color' => 'secondary'];
@endphp

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard',     'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-handshake',  'label' => 'Consignações',  'href' => route('admin.v2.consignment-evaluations.index')],
        ['icon' => 'bi bi-file-text',  'label' => $evaluation->reference, 'href' => ''],
    ],
    'title'    => $evaluation->brand . ' ' . $evaluation->model,
    'subtitle' => $evaluation->reference . ' · Submetido ' . $evaluation->created_at->format('d/m/Y H:i'),
])

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- ── COLUNA PRINCIPAL ── --}}
    <div class="col-lg-8">

        {{-- Veículo --}}
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-car-front"></i> Dados do Veículo</h5>
            </div>
            <div class="modern-card-body">
                <div class="row g-3">
                    @php
                    $vehicleFields = [
                        ['label' => 'Marca',       'value' => $evaluation->brand],
                        ['label' => 'Modelo',      'value' => $evaluation->model],
                        ['label' => 'Versão',      'value' => $evaluation->version ?: '—'],
                        ['label' => 'Ano',         'value' => $evaluation->year],
                        ['label' => 'Quilómetros', 'value' => number_format($evaluation->kilometers, 0, ',', '.') . ' km'],
                        ['label' => 'Matrícula',   'value' => $evaluation->plate],
                        ['label' => 'Combustível', 'value' => $evaluation->fuel],
                        ['label' => 'Transmissão', 'value' => $evaluation->gearbox],
                        ['label' => 'Potência',    'value' => $evaluation->power ? $evaluation->power . ' cv' : '—'],
                        ['label' => 'Cilindrada',  'value' => $evaluation->displacement ? $evaluation->displacement . ' cc' : '—'],
                        ['label' => 'Cor',         'value' => $evaluation->color ?: '—'],
                        ['label' => 'Valor esperado', 'value' => $evaluation->price_expectation ? number_format($evaluation->price_expectation, 0, ',', '.') . ' €' : '—'],
                    ];
                    @endphp
                    @foreach($vehicleFields as $f)
                    <div class="col-sm-6 col-md-4">
                        <div class="text-muted small text-uppercase fw-semibold" style="letter-spacing:.04em;font-size:.7rem;">{{ $f['label'] }}</div>
                        <div class="fw-600 mt-1">{{ $f['value'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Estado --}}
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-clipboard-check"></i> Estado do Veículo</h5>
                <span class="badge bg-secondary">{{ $evaluation->condition }}</span>
            </div>
            <div class="modern-card-body">
                <div class="row g-3 mb-3">
                    @php
                    $checks = [
                        ['label' => 'Livrete de revisões', 'value' => $evaluation->has_service_book],
                        ['label' => 'Segunda chave',       'value' => $evaluation->has_2nd_key],
                        ['label' => 'IUC em dia',          'value' => $evaluation->has_iuc],
                        ['label' => 'Inspeção válida',     'value' => $evaluation->has_inspection],
                    ];
                    @endphp
                    @foreach($checks as $c)
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $c['value'] ? 'bi-check-circle-fill text-success' : 'bi-x-circle text-secondary' }}"></i>
                            <span class="small">{{ $c['label'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($evaluation->description)
                <div class="bg-light rounded p-3 small" style="white-space:pre-wrap;line-height:1.7">{{ $evaluation->description }}</div>
                @else
                <p class="text-muted small mb-0">Sem descrição.</p>
                @endif
            </div>
        </div>

        {{-- Fotos --}}
        @if(!empty($evaluation->photos))
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-images"></i> Fotografias</h5>
                <span class="badge bg-secondary">{{ count($evaluation->photos) }}</span>
            </div>
            <div class="modern-card-body">
                <div class="row g-2">
                    @foreach($evaluation->photos as $photo)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ asset('storage/' . $photo) }}" target="_blank">
                            <img src="{{ asset('storage/' . $photo) }}"
                                 class="img-fluid rounded"
                                 style="width:100%;height:120px;object-fit:cover;"
                                 alt="Foto">
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── SIDEBAR ── --}}
    <div class="col-lg-4">

        {{-- Contacto --}}
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-person"></i> Contacto</h5>
            </div>
            <div class="modern-card-body">
                <div class="d-flex flex-column gap-3">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold" style="font-size:.7rem;">Nome</div>
                        <div class="fw-600">{{ $evaluation->name }}</div>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold" style="font-size:.7rem;">Telemóvel</div>
                        <a href="tel:{{ $evaluation->phone }}" class="fw-600 text-decoration-none">{{ $evaluation->phone }}</a>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold" style="font-size:.7rem;">E-mail</div>
                        <a href="mailto:{{ $evaluation->email }}" class="fw-600 text-decoration-none small">{{ $evaluation->email }}</a>
                    </div>
                    @if($evaluation->location)
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold" style="font-size:.7rem;">Localização</div>
                        <div class="fw-600">{{ $evaluation->location }}</div>
                    </div>
                    @endif
                </div>
                <div class="d-flex gap-2 mt-4">
                    <a href="tel:{{ $evaluation->phone }}" class="btn btn-sm btn-success flex-grow-1">
                        <i class="bi bi-telephone me-1"></i> Ligar
                    </a>
                    <a href="mailto:{{ $evaluation->email }}" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="bi bi-envelope me-1"></i> Email
                    </a>
                </div>
            </div>
        </div>

        {{-- Estado --}}
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-tag"></i> Estado</h5>
                <span class="badge bg-{{ $sl['color'] }}">{{ $sl['label'] }}</span>
            </div>
            <div class="modern-card-body">
                <form action="{{ route('admin.v2.consignment-evaluations.update-status', $evaluation->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm mb-3">
                        @foreach(\App\Models\ConsignmentEvaluation::$statusLabels as $key => $s)
                        <option value="{{ $key }}" {{ $evaluation->status === $key ? 'selected' : '' }}>{{ $s['label'] }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary w-100">Actualizar Estado</button>
                </form>
            </div>
        </div>

        {{-- Notas --}}
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title"><i class="bi bi-journal-text"></i> Notas Internas</h5>
            </div>
            <div class="modern-card-body">
                <form action="{{ route('admin.v2.consignment-evaluations.update-notes', $evaluation->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <textarea name="notes" class="form-control form-control-sm mb-3" rows="5"
                        placeholder="Notas internas sobre este pedido...">{{ $evaluation->notes }}</textarea>
                    <button type="submit" class="btn btn-sm btn-secondary w-100">Guardar Notas</button>
                </form>
            </div>
        </div>

        {{-- Eliminar --}}
        <div class="modern-card border-danger">
            <div class="modern-card-body">
                <form action="{{ route('admin.v2.consignment-evaluations.destroy', $evaluation->id) }}" method="POST"
                      onsubmit="return confirm('Tem a certeza que quer eliminar este pedido?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i> Eliminar Pedido
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
