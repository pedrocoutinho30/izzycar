@extends('layouts.admin-v2')

@section('title', 'Criar publicação — Testemunho')

@section('content')

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-stars', 'label' => 'Marketing Studio', 'href' => route('admin.marketing.index')],
['icon' => 'bi bi-plus-lg', 'label' => 'Criar publicação', 'href' => route('admin.marketing.create')],
['icon' => 'bi bi-chat-quote', 'label' => 'Testemunho', 'href' => ''],
],
'title' => 'Publicação: Testemunho',
'subtitle' => 'Só aparecem testemunhos com autorização de uso. O texto nunca é reescrito: no editor só podes escolher um excerto do original.',
])

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<form method="POST" action="{{ route('admin.marketing.content.store', 'testemunho') }}">
    @csrf
    <input type="hidden" name="testimonial_id" id="testimonialId" value="{{ old('testimonial_id') }}">
    <input type="hidden" name="template" value="testemunho">

    <div class="card mb-4">
        <div class="card-header fw-semibold">1. Testemunho</div>
        <div class="card-body">
            <input type="search" id="tSearch" class="form-control mb-3" placeholder="Pesquisar por nome ou texto…" autocomplete="off">
            <div id="tResults" class="list-group"></div>
            <div id="tEmpty" class="alert alert-info d-none mb-0">Nenhum testemunho autorizado. Marca "Autoriza uso em publicações" no testemunho (Testemunhos → editar) depois de o cliente autorizar.</div>
            <div id="tChosen" class="alert alert-success d-none mt-3 mb-0"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">2. Formato</div>
        <div class="card-body d-flex flex-wrap gap-3">
            @foreach($formats as $f)
            <label class="form-check"><input type="radio" class="form-check-input" name="format" value="{{ $f->value }}" @checked(old('format', 'feed_square') === $f->value)> {{ $f->label() }}</label>
            @endforeach
        </div>
    </div>

    <button class="btn btn-primary" id="tGo" disabled><i class="bi bi-arrow-right-circle me-1"></i> Criar e abrir o editor</button>
</form>

<script>
(function () {
    const url = @json(route('admin.marketing.testimonials.search'));
    const box = document.getElementById('tResults'), chosen = document.getElementById('tChosen');
    const idEl = document.getElementById('testimonialId'), go = document.getElementById('tGo');
    let timer;
    async function load(q) {
        const r = await fetch(url + '?q=' + encodeURIComponent(q), {headers: {'Accept': 'application/json'}});
        const list = await r.json();
        box.innerHTML = '';
        document.getElementById('tEmpty').classList.toggle('d-none', list.length > 0 || q !== '');
        list.forEach(t => {
            const b = document.createElement('button');
            b.type = 'button'; b.className = 'list-group-item list-group-item-action';
            const strong = document.createElement('b'); strong.textContent = t.label;
            const p = document.createElement('div'); p.className = 'small text-muted'; p.textContent = t.excerpt;
            b.append(strong, p);
            b.addEventListener('click', () => { idEl.value = t.id; chosen.textContent = 'Escolhido: ' + t.label; chosen.classList.remove('d-none'); go.disabled = false; });
            box.appendChild(b);
        });
    }
    document.getElementById('tSearch').addEventListener('input', e => { clearTimeout(timer); timer = setTimeout(() => load(e.target.value), 250); });
    if (idEl.value) go.disabled = false;
    load('');
})();
</script>
@endsection
