@extends('layouts.admin-v2')

@section('title', 'Criar publicação — Importação')

@section('content')

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-stars', 'label' => 'Marketing Studio', 'href' => route('admin.marketing.index')],
['icon' => 'bi bi-plus-lg', 'label' => 'Criar publicação', 'href' => route('admin.marketing.create')],
['icon' => 'bi bi-globe-europe-africa', 'label' => 'Importação', 'href' => ''],
],
'title' => 'Publicação: Importação realizada',
'subtitle' => 'Parte de uma viatura importada ou de dados introduzidos por ti. Nunca aparecem o nome do cliente, a matrícula nem o VIN.',
])

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<form method="POST" action="{{ route('admin.marketing.content.store', 'importacao') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="template" value="importacao">
    <input type="hidden" name="vehicle_id" id="vehicleId" value="{{ old('vehicle_id') }}">

    <div class="card mb-4">
        <div class="card-header fw-semibold">1. Viatura importada (opcional)</div>
        <div class="card-body">
            <input type="search" id="vSearch" class="form-control mb-3" placeholder="Pesquisar viaturas importadas…" autocomplete="off">
            <div id="vResults" class="list-group"></div>
            <div id="vChosen" class="alert alert-success d-none mt-3 mb-0"></div>
            <button type="button" id="vClear" class="btn btn-sm btn-link d-none">Limpar escolha e preencher à mão</button>
        </div>
    </div>

    <div class="card mb-4" id="manualCard">
        <div class="card-header fw-semibold">2. Dados (se não escolheste uma viatura)</div>
        <div class="card-body row g-3">
            <div class="col-md-4"><label class="form-label small">Marca</label><input name="brand" class="form-control" maxlength="100" value="{{ old('brand') }}"></div>
            <div class="col-md-4"><label class="form-label small">Modelo</label><input name="model" class="form-control" maxlength="100" value="{{ old('model') }}"></div>
            <div class="col-md-4"><label class="form-label small">Versão</label><input name="version" class="form-control" maxlength="150" value="{{ old('version') }}"></div>
            <div class="col-md-4"><label class="form-label small">Ano</label><input name="year" type="number" class="form-control" value="{{ old('year') }}"></div>
            <div class="col-md-8"><label class="form-label small">Fotografias (até 12; sem matrículas visíveis)</label><input name="photos[]" type="file" accept="image/*" multiple class="form-control"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">3. Detalhes divulgáveis</div>
        <div class="card-body row g-3">
            <div class="col-md-6"><label class="form-label small">País de origem (opcional)</label><input name="origin_country" class="form-control" maxlength="80" value="{{ old('origin_country') }}"></div>
            <div class="col-md-6"><label class="form-label small">Poupança comprovada (€, opcional)</label><input name="savings" type="number" min="0" class="form-control" value="{{ old('savings') }}"><div class="form-text">Só se tiveres a comparação documentada. Nunca é calculada.</div></div>
            <div class="col-12"><label class="form-label small">Destaques (um por linha, máx. 4; opcional)</label><textarea name="highlights" rows="3" class="form-control">{{ old('highlights') }}</textarea></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">4. Formato</div>
        <div class="card-body d-flex flex-wrap gap-3">
            @foreach($formats as $f)
            <label class="form-check"><input type="radio" class="form-check-input" name="format" value="{{ $f->value }}" @checked(old('format', 'feed_vertical') === $f->value)> {{ $f->label() }}</label>
            @endforeach
        </div>
    </div>

    <div class="card mb-4 border-warning">
        <div class="card-body">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="authorized" id="authorized" value="1" @checked(old('authorized'))>
                <label class="form-check-label fw-semibold" for="authorized">Confirmo que a importação está concluída e que o cliente autorizou a divulgação.</label>
            </div>
        </div>
    </div>

    <button class="btn btn-primary"><i class="bi bi-arrow-right-circle me-1"></i> Criar e abrir o editor</button>
</form>

<script>
(function () {
    const url = @json(route('admin.marketing.vehicles.search'));
    const box = document.getElementById('vResults'), chosen = document.getElementById('vChosen');
    const idEl = document.getElementById('vehicleId'), clear = document.getElementById('vClear');
    let timer;
    function mark(on, label) {
        chosen.textContent = on ? 'Escolhida: ' + label : '';
        chosen.classList.toggle('d-none', !on);
        clear.classList.toggle('d-none', !on);
        document.getElementById('manualCard').classList.toggle('opacity-50', on);
    }
    async function load(q) {
        const r = await fetch(url + '?imported=1&q=' + encodeURIComponent(q), {headers: {'Accept': 'application/json'}});
        const list = await r.json();
        box.innerHTML = '';
        list.forEach(v => {
            const b = document.createElement('button');
            b.type = 'button'; b.className = 'list-group-item list-group-item-action'; b.textContent = v.label;
            b.addEventListener('click', () => { idEl.value = v.id; mark(true, v.label); });
            box.appendChild(b);
        });
    }
    clear.addEventListener('click', () => { idEl.value = ''; mark(false); });
    document.getElementById('vSearch').addEventListener('input', e => { clearTimeout(timer); timer = setTimeout(() => load(e.target.value), 250); });
    load('');
})();
</script>
@endsection
