@extends('layouts.admin-v2')

@section('title', 'Nova Legalização')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-file-earmark-check', 'label' => 'Legalizações', 'href' => route('admin.legalizations.index')],
        ['icon' => 'bi bi-plus', 'label' => 'Nova', 'href' => ''],
    ],
    'title'    => 'Nova Legalização',
    'subtitle' => 'Regista um novo processo de legalização',
])

<div class="modern-card" style="max-width:680px">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-file-earmark-plus me-1"></i> Dados do processo</h5>
    </div>
    <div class="modern-card-body">

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            @foreach($errors->all() as $err)
                <div><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $err }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ route('admin.legalizations.store') }}" method="POST">
            @csrf

            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Cliente</label>
                    <select name="client_id" class="form-select">
                        <option value="">— Sem cliente associado —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Marca <span class="text-danger">*</span></label>
                    <input type="text" name="marca" class="form-control" value="{{ old('marca') }}"
                           placeholder="ex: Volkswagen" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Modelo <span class="text-danger">*</span></label>
                    <input type="text" name="modelo" class="form-control" value="{{ old('modelo') }}"
                           placeholder="ex: Golf" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Combustível <span class="text-danger">*</span></label>
                    <select name="combustivel" class="form-select" required>
                        @foreach(['Gasolina','Diesel','Elétrico','Híbrido Plug-In','Híbrido','GPL','Hidrogénio'] as $c)
                            <option value="{{ $c }}" {{ old('combustivel', 'Gasolina') === $c ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Matrícula Portuguesa</label>
                    <input type="text" name="matricula" class="form-control" value="{{ old('matricula') }}"
                           placeholder="ex: AA-00-BB">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nº de Homologação Nacional</label>
                    <input type="text" name="num_homologacao" class="form-control" value="{{ old('num_homologacao') }}"
                           placeholder="ex: e1*2018/858*00123*00">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Notas</label>
                    <textarea name="notas" class="form-control" rows="3"
                              placeholder="Informações adicionais sobre este processo…">{{ old('notas') }}</textarea>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Criar legalização
                    </button>
                    <a href="{{ route('admin.legalizations.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection
