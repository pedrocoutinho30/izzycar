@extends('layouts.admin-v2')

@section('title', 'Editar Legalização')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-file-earmark-check', 'label' => 'Legalizações', 'href' => route('admin.legalizations.index')],
        ['icon' => 'bi bi-pencil', 'label' => 'Editar', 'href' => ''],
    ],
    'title'    => 'Editar Legalização',
    'subtitle' => $legalization->marca . ' ' . $legalization->modelo,
])

<div class="modern-card" style="max-width:680px">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-pencil me-1"></i> Dados do processo</h5>
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

        <form action="{{ route('admin.legalizations.update', $legalization) }}" method="POST">
            @csrf @method('PUT')

            <div class="row g-3">

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Cliente</label>
                    <select name="client_id" class="form-select">
                        <option value="">— Sem cliente associado —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ old('client_id', $legalization->client_id) == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Marca <span class="text-danger">*</span></label>
                    <input type="text" name="marca" class="form-control"
                           value="{{ old('marca', $legalization->marca) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Modelo <span class="text-danger">*</span></label>
                    <input type="text" name="modelo" class="form-control"
                           value="{{ old('modelo', $legalization->modelo) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Combustível <span class="text-danger">*</span></label>
                    <select name="combustivel" class="form-select" required>
                        @foreach(['Gasolina','Diesel','Elétrico','Híbrido Plug-In','Híbrido','GPL','Hidrogénio'] as $c)
                            <option value="{{ $c }}"
                                {{ old('combustivel', $legalization->combustivel) === $c ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Matrícula Portuguesa</label>
                    <input type="text" name="matricula" class="form-control"
                           value="{{ old('matricula', $legalization->matricula) }}"
                           placeholder="ex: AA-00-BB">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nº de Homologação Nacional</label>
                    <input type="text" name="num_homologacao" class="form-control"
                           value="{{ old('num_homologacao', $legalization->num_homologacao) }}"
                           placeholder="ex: e1*2018/858*00123*00">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Notas</label>
                    <textarea name="notas" class="form-control" rows="3">{{ old('notas', $legalization->notas) }}</textarea>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Guardar alterações
                    </button>
                    <a href="{{ route('admin.legalizations.show', $legalization) }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection
