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

                {{-- ── Origem do veículo ──────────────────────────────── --}}
                <div class="col-12">
                    <label class="form-label fw-semibold">Veículo</label>
                    <div class="d-flex gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-primary active" id="btnModeVehicle"
                                onclick="setMode('vehicle')">
                            <i class="bi bi-car-front me-1"></i> Selecionar do inventário
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnModeManual"
                                onclick="setMode('manual')">
                            <i class="bi bi-pencil me-1"></i> Preencher manualmente
                        </button>
                    </div>

                    {{-- Vehicle picker --}}
                    <div id="vehiclePickerBlock">
                        <select name="vehicle_id" id="vehicleSelect" class="form-select"
                                onchange="fillFromVehicle(this)">
                            <option value="">— Selecionar veículo —</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}"
                                        data-brand="{{ $v->brand }}"
                                        data-model="{{ $v->model }}"
                                        data-fuel="{{ $v->fuel }}"
                                        data-registration="{{ $v->registration }}"
                                        {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->reference }} — {{ $v->brand }} {{ $v->model }}
                                    {{ $v->registration ? '(' . $v->registration . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ── Campos manuais (ocultos quando veículo selecionado) ── --}}
                <div id="manualFields" class="col-12" style="display:none">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Marca</label>
                            <input type="text" id="marcaInput" name="marca" class="form-control"
                                   value="{{ old('marca') }}" placeholder="ex: Volkswagen">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Modelo</label>
                            <input type="text" id="modeloInput" name="modelo" class="form-control"
                                   value="{{ old('modelo') }}" placeholder="ex: Golf">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Combustível</label>
                            <select id="combustivelInput" name="combustivel" class="form-select">
                                @foreach(['Gasolina','Diesel','Elétrico','Híbrido Plug-In','Híbrido','GPL','Hidrogénio'] as $c)
                                    <option value="{{ $c }}" {{ old('combustivel', 'Gasolina') === $c ? 'selected' : '' }}>{{ $c }}</option>
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
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ── Matrícula & Homologação ──────────────────────────── --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Matrícula Portuguesa</label>
                    <input type="text" name="matricula" id="matriculaInput" class="form-control"
                           value="{{ old('matricula') }}" placeholder="ex: AA-00-BB">
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

<script>
function setMode(mode) {
    const isVehicle = mode === 'vehicle';
    document.getElementById('vehiclePickerBlock').style.display = isVehicle ? '' : 'none';
    document.getElementById('manualFields').style.display        = isVehicle ? 'none' : '';
    document.getElementById('btnModeVehicle').classList.toggle('active', isVehicle);
    document.getElementById('btnModeManual').classList.toggle('active', !isVehicle);
    // Clear vehicle_id if manual
    if (!isVehicle) document.getElementById('vehicleSelect').value = '';
}

function fillFromVehicle(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt.value) return;
    document.getElementById('matriculaInput').value = opt.dataset.registration || '';
}

// On load: if old vehicle_id set, stay in vehicle mode; else check if manual fields have values
document.addEventListener('DOMContentLoaded', function() {
    const hasVehicle = document.getElementById('vehicleSelect').value;
    const hasManual  = document.getElementById('marcaInput')?.value;
    if (!hasVehicle && hasManual) {
        setMode('manual');
    }
});
</script>
@endsection
