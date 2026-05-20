@extends('layouts.admin-v2')

@section('title', 'Veículos V3')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-car-front-fill', 'label' => 'Veículos V3', 'href' => ''],
    ],
    'title'    => 'Veículos V3',
    'subtitle' => 'Gestão completa de veículos',
    'actions'  => '<a href="' . route('admin.v3.vehicles.create') . '" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Novo Veículo</a>',
])

{{-- Flash --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Filters --}}
<div class="modern-card mb-3">
    <div class="modern-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Pesquisar referência, marca, modelo, matrícula…" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos os estados</option>
                    @foreach(\App\Models\V3Vehicle::statusOptions() as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="fuel" class="form-select form-select-sm">
                    <option value="">Todos os combustíveis</option>
                    @foreach(\App\Models\V3Vehicle::fuelOptions() as $f)
                        <option value="{{ $f }}" {{ request('fuel') === $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i> Filtrar</button>
                <a href="{{ route('admin.v3.vehicles.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Limpar</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="modern-card">
    <div class="modern-card-body p-0">
        @if($vehicles->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-car-front fs-1 d-block mb-2"></i>
                Nenhum veículo encontrado.
                <a href="{{ route('admin.v3.vehicles.create') }}" class="d-block mt-2">Criar primeiro veículo</a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px"></th>
                        <th>Referência</th>
                        <th>Veículo</th>
                        <th>Matrícula</th>
                        <th>Combustível</th>
                        <th>Estado</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicles as $v)
                    <tr>
                        <td>
                            @if($v->coverPhoto)
                                <img src="{{ asset('storage/' . $v->coverPhoto->path) }}" class="rounded" width="44" height="44" style="object-fit:cover">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:44px;height:44px"><i class="bi bi-car-front text-muted"></i></div>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary font-monospace">{{ $v->reference }}</span></td>
                        <td>
                            <div class="fw-semibold">{{ $v->brand }} {{ $v->model }}</div>
                            <small class="text-muted">{{ $v->version }} {{ $v->year ? '· ' . $v->year : '' }} {{ $v->kilometers ? '· ' . number_format($v->kilometers) . ' km' : '' }}</small>
                        </td>
                        <td>{{ $v->registration ?: '—' }}</td>
                        <td>{{ $v->fuel ?: '—' }}</td>
                        <td><span class="badge bg-{{ $v->status_color }}">{{ $v->status_label }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.v3.vehicles.edit', $v->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.v3.vehicles.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Eliminar este veículo?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-3 py-2">{{ $vehicles->links() }}</div>
        @endif
    </div>
</div>

@endsection
