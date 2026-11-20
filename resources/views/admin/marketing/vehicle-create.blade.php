@extends('layouts.admin-v2')

@section('title', 'Criar publicação')

@section('content')

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-stars', 'label' => 'Marketing Studio', 'href' => route('admin.marketing.index')],
['icon' => 'bi bi-plus-lg', 'label' => 'Criar publicação', 'href' => route('admin.marketing.create')],
['icon' => 'bi bi-car-front', 'label' => 'Viatura', 'href' => ''],
],
'title' => 'Publicação de viatura',
'subtitle' => 'Escolhe uma viatura do catálogo, um template e um formato. Os dados e as fotografias são carregados automaticamente.',
])

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<form method="POST" action="{{ route('admin.marketing.store') }}" id="createForm">
    @csrf
    <input type="hidden" name="type" value="veiculo">
    <input type="hidden" name="vehicle_id" id="vehicleId" value="{{ old('vehicle_id') }}">

    <div class="card mb-4">
        <div class="card-header fw-semibold">1. Viatura</div>
        <div class="card-body">
            <input type="search" id="vehicleSearch" class="form-control mb-3" placeholder="Pesquisar por marca, modelo ou referência…" autocomplete="off">
            <div id="vehicleResults" class="row g-2"></div>
            <div id="vehicleChosen" class="alert alert-success d-none mt-3 mb-0"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">2. Template</div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($templates as $t)
                <div class="col-md-6 col-xl-4">
                    <label class="card h-100 template-option" style="cursor:pointer">
                        <div class="card-body">
                            <input type="radio" name="template" value="{{ $t->key }}" class="form-check-input me-2"
                                   data-formats='@json(array_map(fn ($f) => $f->value, $t->formats))' @checked(old('template') === $t->key)>
                            <span class="fw-semibold">{{ $t->name }}</span>
                            <div class="text-muted small mt-1">{{ $t->description }}</div>
                            @if($t->key === 'vendido')<div class="small text-warning mt-1">Só para viaturas vendidas.</div>@endif
                            @if($t->key === 'novo-stock')<div class="small text-warning mt-1">Só para viaturas em stock.</div>@endif
                        </div>
                    </label>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">3. Formato</div>
        <div class="card-body">
            @foreach($formats as $f)
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="format" id="fmt_{{ $f->value }}" value="{{ $f->value }}" @checked(old('format', 'feed_vertical') === $f->value)>
                <label class="form-check-label" for="fmt_{{ $f->value }}">{{ $f->label() }}</label>
            </div>
            @endforeach
        </div>
    </div>

    <button type="submit" class="btn btn-primary" id="submitBtn"><i class="bi bi-magic me-1"></i> Gerar e abrir editor</button>
</form>

<script>
(function () {
    const search = document.getElementById('vehicleSearch');
    const results = document.getElementById('vehicleResults');
    const chosen = document.getElementById('vehicleChosen');
    const idInput = document.getElementById('vehicleId');
    const templates = [...document.querySelectorAll('input[name=template]')];
    const formats = [...document.querySelectorAll('input[name=format]')];
    let status = null, timer = null;
    const STATUS = {em_stock: 'Em stock', reservado: 'Reservado', vendido: 'Vendido'};

    function esc(s) { const d = document.createElement('div'); d.textContent = s ?? ''; return d.innerHTML; }

    function syncRules() {
        // Vendido / Novo em stock dependem do estado real da viatura.
        templates.forEach(r => {
            const need = r.value === 'vendido' ? 'vendido' : (r.value === 'novo-stock' ? 'em_stock' : null);
            const bloqueado = need && status !== need;
            r.disabled = !!bloqueado;
            r.closest('.template-option').style.opacity = bloqueado ? .45 : 1;
            if (bloqueado && r.checked) r.checked = false;
        });
        const t = templates.find(r => r.checked);
        const allowed = t ? JSON.parse(t.dataset.formats) : null;
        formats.forEach(f => {
            f.disabled = allowed ? !allowed.includes(f.value) : false;
            if (f.disabled && f.checked) f.checked = false;
        });
        if (!formats.some(f => f.checked)) { const first = formats.find(f => !f.disabled); if (first) first.checked = true; }
    }

    function load(q) {
        fetch(@json(route('admin.marketing.vehicles.search')) + '?q=' + encodeURIComponent(q), {headers: {Accept: 'application/json'}})
            .then(r => r.json()).then(list => {
                results.innerHTML = list.length ? '' : '<div class="text-muted">Sem resultados.</div>';
                list.forEach(v => {
                    const col = document.createElement('div');
                    col.className = 'col-md-6 col-xl-4';
                    col.innerHTML = `<button type="button" class="btn btn-outline-secondary w-100 text-start d-flex gap-2 align-items-center">
                        ${v.cover_url ? `<img src="${v.cover_url}" style="width:64px;height:48px;object-fit:cover;border-radius:6px" loading="lazy">` : '<div style="width:64px;height:48px" class="bg-light rounded"></div>'}
                        <span><strong>${esc(v.label)}</strong><br><small class="text-muted">${esc(v.reference)} · ${STATUS[v.status] || esc(v.status)}</small></span></button>`;
                    col.firstElementChild.addEventListener('click', () => {
                        idInput.value = v.id; status = v.status;
                        chosen.classList.remove('d-none');
                        chosen.textContent = 'Viatura escolhida: ' + v.label + ' (' + (STATUS[v.status] || v.status) + ')';
                        syncRules();
                    });
                    results.appendChild(col);
                });
            });
    }

    search.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => load(search.value), 250); });
    templates.forEach(r => r.addEventListener('change', syncRules));
    document.getElementById('createForm').addEventListener('submit', e => {
        if (!idInput.value) { e.preventDefault(); alert('Escolhe uma viatura.'); }
    });
    load('');
    syncRules();
})();
</script>
@endsection
