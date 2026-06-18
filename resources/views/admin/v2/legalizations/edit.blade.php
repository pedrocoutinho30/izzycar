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

                {{-- ── Origem do veículo ──────────────────────────────── --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Veículo</label>
                    <div class="d-flex gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-primary {{ $legalization->v3_vehicle_id ? 'active' : '' }}" id="btnModeVehicle"
                                onclick="setMode('vehicle')">
                            <i class="bi bi-car-front me-1"></i> Selecionar do inventário
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary {{ !$legalization->v3_vehicle_id ? 'active' : '' }}" id="btnModeManual"
                                onclick="setMode('manual')">
                            <i class="bi bi-pencil me-1"></i> Preencher manualmente
                        </button>
                    </div>

                    <div id="vehiclePickerBlock" style="{{ !$legalization->v3_vehicle_id ? 'display:none' : '' }}">
                        <select name="v3_vehicle_id" id="vehicleSelect" class="form-select"
                                onchange="fillFromVehicle(this)">
                            <option value="">— Selecionar veículo —</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}"
                                        data-brand="{{ $v->brand }}"
                                        data-model="{{ $v->model }}"
                                        data-fuel="{{ $v->fuel }}"
                                        data-registration="{{ $v->registration }}"
                                        {{ old('v3_vehicle_id', $legalization->v3_vehicle_id) == $v->id ? 'selected' : '' }}>
                                    {{ $v->reference }} — {{ $v->brand }} {{ $v->model }}
                                    {{ $v->registration ? '(' . $v->registration . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ── Campos manuais ─────────────────────────────────── --}}
                <div id="manualFields" class="col-12" style="{{ $legalization->v3_vehicle_id ? 'display:none' : '' }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Marca</label>
                            <input type="text" id="marcaInput" name="marca" class="form-control"
                                   value="{{ old('marca', $legalization->marca) }}" placeholder="ex: Volkswagen">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Modelo</label>
                            <input type="text" id="modeloInput" name="modelo" class="form-control"
                                   value="{{ old('modelo', $legalization->modelo) }}" placeholder="ex: Golf">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Combustível</label>
                            <select id="combustivelInput" name="combustivel" class="form-select">
                                @foreach(['Gasolina','Diesel','Elétrico','Híbrido Plug-In','Híbrido','GPL','Hidrogénio'] as $c)
                                    <option value="{{ $c }}"
                                        {{ old('combustivel', $legalization->combustivel) === $c ? 'selected' : '' }}>
                                        {{ $c }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ── Cliente ─────────────────────────────────────────── --}}
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

                {{-- ── Matrícula & Homologação ──────────────────────────── --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Matrícula Portuguesa</label>
                    <input type="text" name="matricula" id="matriculaInput" class="form-control"
                           value="{{ old('matricula', $legalization->matricula) }}"
                           placeholder="ex: AA-00-BB">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nº de Homologação Nacional</label>
                    <input type="text" name="num_homologacao" class="form-control"
                           value="{{ old('num_homologacao', $legalization->num_homologacao) }}"
                           placeholder="ex: e1*2018/858*00123*00">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nº de Processo IMT</label>
                    <input type="text" name="num_processo_imt" class="form-control"
                           value="{{ old('num_processo_imt', $legalization->num_processo_imt) }}"
                           placeholder="ex: 2024/123456">
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

<script>
function setMode(mode) {
    const isVehicle = mode === 'vehicle';
    document.getElementById('vehiclePickerBlock').style.display = isVehicle ? '' : 'none';
    document.getElementById('manualFields').style.display        = isVehicle ? 'none' : '';
    document.getElementById('btnModeVehicle').classList.toggle('active', isVehicle);
    document.getElementById('btnModeManual').classList.toggle('active', !isVehicle);
    if (!isVehicle) document.getElementById('vehicleSelect').value = '';
}

function fillFromVehicle(select) {
    const opt = select.options[select.selectedIndex];
    if (opt.dataset.registration) {
        document.getElementById('matriculaInput').value = opt.dataset.registration;
    }
}
</script>
@endsection
