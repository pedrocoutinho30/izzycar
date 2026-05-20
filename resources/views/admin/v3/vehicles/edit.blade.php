@extends('layouts.admin-v2')

@section('title', $vehicle->reference . ' — ' . trim($vehicle->brand . ' ' . $vehicle->model))

@section('content')
@php
    $returnTab = session('return_tab', 'general');
    $sale      = $vehicle->sales->first();

    $subtitleParts = array_filter([
        $vehicle->reference,
        $vehicle->year,
        $vehicle->sub_model,
        $vehicle->registration,
    ]);
    $subtitle = implode(' · ', $subtitleParts)
    
        . ' · ' . $vehicle->status_label ;
@endphp

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door',      'label' => 'Dashboard',    'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-car-front-fill',  'label' => 'Veículos V3',  'href' => route('admin.v3.vehicles.index')],
        ['icon' => 'bi bi-pencil',          'label' => $vehicle->reference ?? 'Editar', 'href' => ''],
    ],
    'title'    => ($vehicle->brand ?? '—') . ' ' . ($vehicle->model ?? ''),
    'subtitle' => $subtitle,
])

{{-- Flash --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3" id="flashSuccess">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ══ V3 Tabs Container ══════════════════════════════════════════ --}}
<div class="v3-vehicle-tabs">

    {{-- Tab Nav --}}
    <nav class="v3-tab-nav" id="v3TabNav">
        <a class="v3-tab-link" data-tab="general" href="#">
            <i class="bi bi-info-circle"></i> <span class="d-none d-md-inline">Informação </span>Geral
        </a>
        <a class="v3-tab-link" data-tab="purchase" href="#">
            <i class="bi bi-bag"></i> <span class="d-none d-md-inline">Dados </span>Compra
        </a>
        <a class="v3-tab-link" data-tab="expenses" href="#">
            <i class="bi bi-receipt"></i> Despesas
            @php $manualExpensesCount = $vehicle->expenses->where('category', '!=', 'vehicle_purchase')->count(); @endphp
            @if($manualExpensesCount)
                <span class="badge bg-secondary rounded-pill">{{ $manualExpensesCount }}</span>
            @endif
        </a>
        <a class="v3-tab-link" data-tab="documents" href="#">
            <i class="bi bi-file-earmark-text"></i> Documentos
            @if($vehicle->documents->count())
                <span class="badge bg-secondary rounded-pill">{{ $vehicle->documents->count() }}</span>
            @endif
        </a>
        <a class="v3-tab-link" data-tab="sale" href="#">
            <i class="bi bi-cash-coin"></i> Venda
            @if($sale)
                <span class="badge bg-success rounded-pill">Registada</span>
            @endif
        </a>
        <a class="v3-tab-link" data-tab="photos" href="#">
            <i class="bi bi-images"></i> Fotos
            @if($vehicle->photos->count())
                <span class="badge bg-secondary rounded-pill">{{ $vehicle->photos->count() }}</span>
            @endif
        </a>
        @if($vehicle->is_imported)
        <a class="v3-tab-link" data-tab="legalization" href="#">
            <i class="bi bi-globe2"></i> Legalização
            @php $leg = $vehicle->legalization; @endphp
            @if($leg)
                @php $legPct = $leg->progressPercent(); @endphp
                <span class="badge bg-{{ $legPct === 100 ? 'success' : ($legPct > 0 ? 'info' : 'secondary') }} rounded-pill">
                    {{ $legPct }}%
                </span>
            @else
                <span class="badge bg-warning rounded-pill">Nova</span>
            @endif
        </a>
        @endif
    </nav>

    {{-- Auto-save status bar --}}
    <div id="v3SaveStatus" class="v3-save-status d-none">
        <span id="v3SaveMsg"></span>
    </div>

    {{-- Tab Panes --}}
    <div id="tab-general"      class="v3-tab-pane">@include('admin.v3.vehicles.partials.tab-general')</div>
    <div id="tab-purchase"     class="v3-tab-pane">@include('admin.v3.vehicles.partials.tab-purchase')</div>
    <div id="tab-expenses"     class="v3-tab-pane">@include('admin.v3.vehicles.partials.tab-expenses')</div>
    <div id="tab-documents"    class="v3-tab-pane">@include('admin.v3.vehicles.partials.tab-documents')</div>
    <div id="tab-sale"         class="v3-tab-pane">@include('admin.v3.vehicles.partials.tab-sale')</div>
    <div id="tab-photos"       class="v3-tab-pane">@include('admin.v3.vehicles.partials.tab-photos')</div>
    @if($vehicle->is_imported)
    <div id="tab-legalization" class="v3-tab-pane">@include('admin.v3.vehicles.partials.tab-legalization')</div>
    @endif

</div>

{{-- ══ Styles ══════════════════════════════════════════════════════ --}}
<style>
.v3-vehicle-tabs {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    overflow: hidden;
}
.v3-tab-nav {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #e9ecef;
    background: #f8f9fa;
    padding: 0 1rem;
    overflow-x: auto;
    scrollbar-width: none;
}
.v3-tab-nav::-webkit-scrollbar { display: none; }
.v3-tab-link {
    display: flex;
    align-items: center;
    gap: .35rem;
    padding: .75rem 1.1rem;
    font-size: .875rem;
    font-weight: 500;
    color: #6c757d;
    text-decoration: none;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    white-space: nowrap;
    transition: color .15s, border-color .15s;
    cursor: pointer;
    flex-shrink: 0;
}
.v3-tab-link:hover { color: var(--admin-primary, #4A6FA5); }
.v3-tab-link.active {
    color: var(--admin-primary, #4A6FA5);
    border-bottom-color: var(--admin-primary, #4A6FA5);
    font-weight: 600;
}
.v3-tab-pane { display: none; padding: 1.75rem; }
.v3-tab-pane.active { display: block; }
.v3-save-status {
    padding: .4rem 1.25rem;
    font-size: .8rem;
    font-weight: 500;
    border-bottom: 1px solid #e9ecef;
}
.v3-save-status.saving  { background: #fff3cd; color: #856404; }
.v3-save-status.saved   { background: #d1e7dd; color: #0a3622; }
.v3-save-status.error   { background: #f8d7da; color: #58151c; }
</style>

{{-- ══ Scripts ═════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
(function () {
    // Tabs that auto-save on switch
    const SAVEABLE  = ['general', 'purchase'];
    const SAVE_URLS = {
        general:  '{{ route("admin.v3.vehicles.save-general",  $vehicle->id) }}',
        purchase: '{{ route("admin.v3.vehicles.save-purchase", $vehicle->id) }}',
    };
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
              || '{{ csrf_token() }}';

    let currentTab = '{{ $returnTab }}';

    // ── Status bar ────────────────────────────────────────────────
    const statusBar = document.getElementById('v3SaveStatus');
    const statusMsg = document.getElementById('v3SaveMsg');

    function showStatus(type, msg) {
        statusBar.className = 'v3-save-status ' + type;
        statusMsg.textContent = msg;
        statusBar.classList.remove('d-none');
        if (type === 'saved') {
            setTimeout(() => statusBar.classList.add('d-none'), 2500);
        }
    }

    // ── Errors ────────────────────────────────────────────────────
    function showErrors(formId, errors) {
        clearErrors(formId);
        const container = document.querySelector('#' + formId + ' .v3-tab-errors');
        if (!container || !errors) return;
        const msgs = Object.values(errors).flat().join('<br>');
        container.innerHTML = `<div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>${msgs}</div>`;
        container.style.display = '';
    }

    function clearErrors(formId) {
        const container = document.querySelector('#' + formId + ' .v3-tab-errors');
        if (container) { container.innerHTML = ''; container.style.display = 'none'; }
    }

    // ── Auto-save ─────────────────────────────────────────────────
    async function autoSave(tab) {
        const formId = 'form-' + tab;
        const form   = document.getElementById(formId);
        if (!form) return true;

        showStatus('saving', 'A guardar…');

        const body = new FormData(form);
        try {
            const res  = await fetch(SAVE_URLS[tab], {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                body,
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
                showStatus('error', 'Erro ao guardar. Corrija os campos em destaque.');
                showErrors(formId, data.errors || {});
                return false;
            }

            clearErrors(formId);
            showStatus('saved', '✓ ' + (data.message || 'Guardado com sucesso'));
            return true;
        } catch {
            showStatus('error', 'Erro de ligação. Tente novamente.');
            return false;
        }
    }

    // ── Tab activation ────────────────────────────────────────────
    function activateTab(tabId) {
        document.querySelectorAll('.v3-tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.v3-tab-link').forEach(l => l.classList.remove('active'));
        document.getElementById('tab-' + tabId)?.classList.add('active');
        document.querySelector(`.v3-tab-link[data-tab="${tabId}"]`)?.classList.add('active');
    }

    async function switchToTab(targetTab) {
        if (targetTab === currentTab) return;

        if (SAVEABLE.includes(currentTab)) {
            const ok = await autoSave(currentTab);
            if (!ok) return; // Block switch on validation error
        }

        activateTab(targetTab);
        currentTab = targetTab;
    }

    // ── Init ──────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        activateTab(currentTab);

        document.querySelectorAll('.v3-tab-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                switchToTab(link.dataset.tab);
            });
        });

        // Auto-dismiss flash after 4s
        const flash = document.getElementById('flashSuccess');
        if (flash) setTimeout(() => { const a = bootstrap.Alert.getOrCreateInstance(flash); a?.close(); }, 4000);
    });

    // Expose globally for tab-specific save buttons
    window.v3SaveTab = (tab) => autoSave(tab);
})();
</script>
@endpush

@endsection
