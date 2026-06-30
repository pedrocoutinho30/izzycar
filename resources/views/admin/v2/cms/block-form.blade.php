@extends('layouts.admin-v2')
@section('title', ($block ? 'Editar' : 'Novo') . ' Bloco — ' . $page->name)

@section('content')

@php
    $isEdit   = isset($block) && $block;
    $action   = $isEdit
        ? route('admin.v2.cms.update-block', [$page->id, $block->id])
        : route('admin.v2.cms.store-block', $page->id);
    $dataSchema = \App\Models\CmsBlock::DATA_SCHEMA;
    $currentType = old('type', $isEdit ? $block->type : 'hero');
    $currentData = $isEdit ? ($block->data ?? []) : [];
@endphp

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door',                 'label' => 'Dashboard',          'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-layout-text-window-reverse', 'label' => 'Gestor de Conteúdo', 'href' => route('admin.v2.cms.index')],
        ['icon' => '',                                 'label' => $page->name,           'href' => route('admin.v2.cms.page', $page->id)],
        ['icon' => '', 'label' => $isEdit ? 'Editar Bloco' : 'Novo Bloco'],
    ],
    'title'       => ($isEdit ? 'Editar' : 'Novo') . ' Bloco',
    'subtitle'    => 'Página: ' . $page->name,
    'actionHref'  => '',
    'actionLabel' => '',
])

<form method="POST" action="{{ $action }}" id="blockForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row g-4">

        {{-- ── Coluna principal ── --}}
        <div class="col-lg-8">

            {{-- Tipo + nome --}}
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-sliders"></i> Identificação do Bloco</h5>
                </div>
                <div class="row g-3 p-3">
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Bloco <span class="text-danger">*</span></label>
                        <select name="type" id="blockType" class="form-select" required>
                            @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ $currentType === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Define a estrutura visual deste bloco no site.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nome interno <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $isEdit ? $block->name : '') }}"
                               placeholder="ex: hero, why_import, faq" required>
                        <div class="form-text">Identificador único nesta página (sem espaços).</div>
                    </div>
                </div>
            </div>

            {{-- Conteúdo textual --}}
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-text-paragraph"></i> Conteúdo</h5>
                </div>
                <div class="row g-3 p-3">
                    <div class="col-12 field-title">
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-control"
                               value="{{ old('title', $isEdit ? $block->title : '') }}"
                               placeholder="Título principal do bloco">
                    </div>
                    <div class="col-12 field-subtitle">
                        <label class="form-label">Subtítulo</label>
                        <textarea name="subtitle" class="form-control" rows="2"
                                  placeholder="Subtítulo ou descrição curta">{{ old('subtitle', $isEdit ? $block->subtitle : '') }}</textarea>
                    </div>
                    <div class="col-12 field-body">
                        <label class="form-label">
                            Corpo / Texto <span class="text-muted small" id="bodyHint"></span>
                        </label>
                        <textarea name="body" id="bodyField" class="form-control" rows="5"
                                  placeholder="Texto principal (pode usar HTML)">{{ old('body', $isEdit ? $block->body : '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Botões --}}
            <div class="modern-card mb-4 field-buttons">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-cursor"></i> Botões / CTA</h5>
                </div>
                <div class="row g-3 p-3">
                    <div class="col-md-6">
                        <label class="form-label">Texto do Botão 1</label>
                        <input type="text" name="button_text" class="form-control"
                               value="{{ old('button_text', $isEdit ? $block->button_text : '') }}"
                               placeholder="ex: Pedir Cotação">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link do Botão 1</label>
                        <input type="text" name="button_url" class="form-control"
                               value="{{ old('button_url', $isEdit ? $block->button_url : '') }}"
                               placeholder="ex: /importacao">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Texto do Botão 2</label>
                        <input type="text" name="button2_text" class="form-control"
                               value="{{ old('button2_text', $isEdit ? $block->button2_text : '') }}"
                               placeholder="ex: Simular Custos">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link do Botão 2</label>
                        <input type="text" name="button2_url" class="form-control"
                               value="{{ old('button2_url', $isEdit ? $block->button2_url : '') }}"
                               placeholder="ex: /simulador-custos">
                    </div>
                </div>
            </div>

            {{-- Repeater — só aparece para tipos que têm data[] --}}
            <div class="modern-card mb-4" id="repeaterCard" style="display:none">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-list-check"></i> Itens / Lista</h5>
                    <button type="button" class="btn btn-sm btn-primary-modern" onclick="addRow()">
                        <i class="bi bi-plus-lg me-1"></i> Adicionar Item
                    </button>
                </div>
                <div id="repeaterBody" class="p-3 d-flex flex-column gap-3"></div>
                {{-- Hidden JSON output --}}
                <input type="hidden" name="data" id="dataField">
            </div>

        </div>

        {{-- ── Sidebar ── --}}
        <div class="col-lg-4">
            <div class="modern-card sticky-top" style="top:80px">
                <div class="modern-card-header">
                    <h5 class="modern-card-title"><i class="bi bi-toggle-on"></i> Publicação</h5>
                </div>
                <div class="p-3">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="blockActive"
                               {{ old('active', $isEdit ? $block->active : true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="blockActive">Bloco visível no site</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ordem</label>
                        <input type="number" name="order" class="form-control"
                               value="{{ old('order', $isEdit ? $block->order : ($page->blocks()->max('order') + 1)) }}" min="0">
                    </div>
                    <div class="field-image mb-3">
                        <label class="form-label">Imagem</label>
                        <input type="text" name="image" class="form-control"
                               value="{{ old('image', $isEdit ? $block->image : '') }}"
                               placeholder="files/nome-do-ficheiro.jpg">
                        <div class="form-text">Caminho relativo dentro de <code>storage/</code>.</div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary-modern">
                            <i class="bi bi-save me-1"></i>
                            {{ $isEdit ? 'Guardar Bloco' : 'Criar Bloco' }}
                        </button>
                        <a href="{{ route('admin.v2.cms.page', $page->id) }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@endsection

@push('styles')
<style>
.repeater-item { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 1rem; position: relative; }
.repeater-item .remove-row { position: absolute; top: .5rem; right: .75rem; background: none; border: none; color: #dc3545; font-size: 1.1rem; cursor: pointer; }
.repeater-item label { font-size: .8rem; font-weight: 600; color: #666; margin-bottom: .25rem; }
.repeater-item .drag-handle { cursor: grab; color: #aaa; margin-right: .5rem; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
// ── Schema dos tipos com repeater ─────────────────────
const DATA_SCHEMA = @json(\App\Models\CmsBlock::DATA_SCHEMA);

// ── Dados actuais (ao editar) ─────────────────────────
let currentData = @json($currentData);

// ── Visibilidade de campos por tipo ──────────────────
const TYPE_CONFIG = {
    hero:   { body: 'Badge/Texto de topo', buttons: true,  image: true,  repeater: false },
    badges: { body: false,                 buttons: false, image: false, repeater: true  },
    text:   { body: 'Texto (HTML aceite)', buttons: false, image: false, repeater: false },
    cards:  { body: false,                 buttons: false, image: false, repeater: true  },
    steps:  { body: false,                 buttons: true,  image: false, repeater: true  },
    cta:    { body: 'Telefone de contacto',buttons: true,  image: true,  repeater: false },
    costs:  { body: false,                 buttons: false, image: false, repeater: true  },
    faq:    { body: false,                 buttons: false, image: false, repeater: true  },
};

function applyTypeUI(type) {
    const cfg = TYPE_CONFIG[type] || { body: true, buttons: true, image: true, repeater: false };

    // Body field
    const bodyWrap = document.querySelector('.field-body');
    if (cfg.body) {
        bodyWrap.style.display = '';
        document.getElementById('bodyHint').textContent = '— ' + cfg.body;
    } else {
        bodyWrap.style.display = 'none';
    }

    // Buttons card
    document.querySelector('.field-buttons').style.display = cfg.buttons ? '' : 'none';

    // Image field
    document.querySelector('.field-image').style.display = cfg.image ? '' : 'none';

    // Repeater
    const repeaterCard = document.getElementById('repeaterCard');
    repeaterCard.style.display = cfg.repeater ? '' : 'none';
    if (cfg.repeater) {
        renderRepeater(type);
    }
}

// ── Repeater ──────────────────────────────────────────
function renderRepeater(type) {
    const schema = DATA_SCHEMA[type] || [];
    const body   = document.getElementById('repeaterBody');
    body.innerHTML = '';

    const rows = currentData.length ? currentData : [{}];
    rows.forEach((row, i) => addRow(row, false));
}

function addRow(data = {}, scroll = true) {
    const type   = document.getElementById('blockType').value;
    const schema = DATA_SCHEMA[type] || [];
    const body   = document.getElementById('repeaterBody');
    const idx    = Date.now();

    const fields = schema.map(f => {
        const val = data[f.key] ?? '';
        const input = f.type === 'textarea'
            ? `<textarea class="form-control form-control-sm" data-key="${f.key}" rows="2" placeholder="${f.label}">${val}</textarea>`
            : `<input type="text" class="form-control form-control-sm" data-key="${f.key}" value="${escHtml(val)}" placeholder="${f.label}">`;
        return `<div class="col"><label>${f.label}</label>${input}</div>`;
    }).join('');

    const el = document.createElement('div');
    el.className = 'repeater-item';
    el.dataset.idx = idx;
    el.innerHTML = `
        <button type="button" class="remove-row" onclick="this.closest('.repeater-item').remove(); serializeData()">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="d-flex align-items-start gap-2">
            <span class="drag-handle mt-2"><i class="bi bi-grip-vertical"></i></span>
            <div class="row g-2 flex-grow-1">${fields}</div>
        </div>
    `;
    el.querySelectorAll('input,textarea').forEach(el => el.addEventListener('input', serializeData));
    body.appendChild(el);

    if (scroll) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    serializeData();
}

function serializeData() {
    const body = document.getElementById('repeaterBody');
    const type = document.getElementById('blockType').value;
    const schema = DATA_SCHEMA[type] || [];
    const rows = [];
    body.querySelectorAll('.repeater-item').forEach(item => {
        const row = {};
        schema.forEach(f => {
            const el = item.querySelector(`[data-key="${f.key}"]`);
            if (el) row[f.key] = el.value;
        });
        rows.push(row);
    });
    document.getElementById('dataField').value = JSON.stringify(rows);
}

// ── Sortable em repeater ──────────────────────────────
function initRepeaterSort() {
    const el = document.getElementById('repeaterBody');
    if (!el) return;
    new Sortable(el, { handle: '.drag-handle', animation: 150, onEnd: serializeData });
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Init ──────────────────────────────────────────────
document.getElementById('blockType').addEventListener('change', function() {
    currentData = [];
    applyTypeUI(this.value);
});

applyTypeUI('{{ $currentType }}');
initRepeaterSort();

// Pre-fill data on edit
@if($isEdit && $block && $block->data)
currentData = @json($block->data);
const type = '{{ $block->type }}';
if ({{ json_encode(isset(\App\Models\CmsBlock::DATA_SCHEMA[$block->type])) }}) {
    document.getElementById('repeaterBody').innerHTML = '';
    currentData.forEach(row => addRow(row, false));
    serializeData();
}
@endif
</script>
@endpush
