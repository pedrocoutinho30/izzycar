@extends('layouts.admin-v2')

@section('title', 'Criar publicação — ' . $type->label())

@section('content')

@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-stars', 'label' => 'Marketing Studio', 'href' => route('admin.marketing.index')],
['icon' => 'bi bi-plus-lg', 'label' => 'Criar publicação', 'href' => route('admin.marketing.create')],
['icon' => 'bi bi-pencil', 'label' => $type->label(), 'href' => ''],
],
'title' => 'Publicação: ' . $type->label(),
'subtitle' => 'Escolhe um tema (opcional), um template e um formato. O texto escreves tu no editor: a biblioteca só sugere o tema e a estrutura.',
])

@if($errors->any())
<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
@endif

<form method="POST" action="{{ route('admin.marketing.content.store', $type->value) }}">
    @csrf

    <div class="card mb-4">
        <div class="card-header fw-semibold">1. Tema</div>
        <div class="card-body">
            <label class="form-label small" for="topic">Biblioteca de temas</label>
            <select name="topic" id="topic" class="form-select mb-2">
                <option value="">Sem tema (escrevo o título)</option>
                @foreach($topics as $t)
                <option value="{{ $t['key'] }}" data-title="{{ $t['title'] }}" data-hint="{{ $t['hint'] }}" data-template="{{ $t['template'] }}" data-legal="{{ $t['legal_check'] ? 1 : 0 }}" @selected(old('topic') === $t['key'])>{{ $t['title'] }}{{ $t['legal_check'] ? ' (fiscal/legal)' : '' }}</option>
                @endforeach
            </select>
            <div id="topicHint" class="form-text"></div>
            <div id="topicLegal" class="alert alert-warning mt-2 mb-0 d-none"><i class="bi bi-exclamation-triangle"></i> Tema fiscal/legal: <b>Verificar a legislação atual antes de publicar</b>. Não há texto pré-escrito, escreves tu.</div>
            <label class="form-label small mt-3" for="title">Título (opcional se escolheres um tema)</label>
            <input type="text" name="title" id="title" class="form-control" maxlength="120" value="{{ old('title') }}">
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">2. Template</div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($templates as $t)
                <div class="col-md-6 col-xl-4">
                    <label class="card h-100 p-3" style="cursor:pointer">
                        <input type="radio" name="template" value="{{ $t->key }}" class="form-check-input mb-2" data-formats='@json(array_map(fn ($f) => $f->value, $t->formats))' @checked(old('template', $templates->keys()->first()) === $t->key)>
                        <b>{{ $t->name }}</b>
                        <span class="small text-muted">{{ $t->description }}</span>
                    </label>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header fw-semibold">3. Formato</div>
        <div class="card-body d-flex flex-wrap gap-3">
            @foreach($formats as $f)
            <label class="form-check"><input type="radio" class="form-check-input" name="format" value="{{ $f->value }}" @checked(old('format', 'feed_vertical') === $f->value)> {{ $f->label() }}</label>
            @endforeach
        </div>
    </div>

    <button class="btn btn-primary"><i class="bi bi-arrow-right-circle me-1"></i> Criar e abrir o editor</button>
</form>

<script>
(function () {
    const sel = document.getElementById('topic');
    const title = document.getElementById('title');
    function formats() {
        const t = document.querySelector('input[name=template]:checked');
        if (!t) return;
        const ok = JSON.parse(t.dataset.formats);
        document.querySelectorAll('input[name=format]').forEach(f => { f.disabled = !ok.includes(f.value); });
        const cur = document.querySelector('input[name=format]:checked');
        if (!cur || cur.disabled) document.querySelector('input[name=format]:not(:disabled)').checked = true;
    }
    sel.addEventListener('change', () => {
        const o = sel.selectedOptions[0];
        document.getElementById('topicHint').textContent = o.dataset.hint ? 'Estrutura sugerida: ' + o.dataset.hint : '';
        document.getElementById('topicLegal').classList.toggle('d-none', o.dataset.legal !== '1');
        if (o.dataset.template) {
            const r = document.querySelector(`input[name=template][value="${o.dataset.template}"]`);
            if (r) r.checked = true;
        }
        formats();
    });
    document.querySelectorAll('input[name=template]').forEach(r => r.addEventListener('change', formats));
    formats();
})();
</script>
@endsection
