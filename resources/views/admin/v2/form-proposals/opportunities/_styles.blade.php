{{-- Estilos das Oportunidades (grelha no pedido + página de detalhe). Mesma
     linguagem visual dos cartões do Radar: borda esquerda com a cor do
     estado, selo pequeno, sem colorir o cartão inteiro. --}}
@once
@push('styles')
<style>
    .opp-card {
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid var(--admin-border, #dee2e6);
        border-left: 4px solid var(--opp-accent, #adb5bd);
        border-radius: var(--border-radius, 12px);
        overflow: hidden;
        transition: var(--transition, all .2s ease);
    }
    .opp-card:hover { box-shadow: var(--shadow-sm, 0 2px 8px rgba(0,0,0,.08)); }
    .opp-card--rejeitado { opacity: .6; }

    .opp-accent-secondary { --opp-accent: #adb5bd; }
    .opp-accent-info      { --opp-accent: #0dcaf0; }
    .opp-accent-warning   { --opp-accent: #ffc107; }
    .opp-accent-primary   { --opp-accent: #0d6efd; }
    .opp-accent-dark      { --opp-accent: #495057; }
    .opp-accent-success   { --opp-accent: #198754; }
    .opp-accent-danger    { --opp-accent: #dc3545; }

    .opp-card-photo {
        height: 140px;
        background: var(--admin-light, #f8f9fa) center / cover no-repeat;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ced4da;
        font-size: 2.2rem;
    }
    .opp-card-body { padding: .9rem 1rem; flex: 1; display: flex; flex-direction: column; gap: .55rem; }
    .opp-card-title { font-weight: 700; color: var(--admin-secondary, #111); line-height: 1.2; }
    .opp-card-title a { color: inherit; text-decoration: none; }
    .opp-card-title a:hover { color: var(--admin-primary, #6e0707); }
    .opp-card-version { font-size: .8rem; color: #6c757d; }
    .opp-card-specs { font-size: .88rem; color: #495057; }
    .opp-card-specs strong { color: var(--admin-secondary, #111); }
    .opp-card-meta { font-size: .8rem; color: #495057; display: flex; flex-direction: column; gap: .2rem; }
    .opp-card-meta i { color: var(--admin-primary, #6e0707); width: 1rem; display: inline-block; }
    .opp-card-footer {
        display: flex; align-items: center; justify-content: space-between; gap: .5rem;
        padding: .6rem 1rem; border-top: 1px solid var(--admin-light, #f8f9fa);
    }

    .opp-status-badge {
        font-size: .72rem; font-weight: 600; padding: .25rem .6rem; border-radius: 999px;
        border: 0; display: inline-flex; align-items: center; gap: .3rem;
    }
    .opp-status-badge.dropdown-toggle::after { margin-left: .15rem; vertical-align: .1em; }

    .opp-progress-label { display: flex; justify-content: space-between; font-size: .78rem; color: #495057; margin-bottom: .25rem; }
    .opp-progress { height: 7px; background: var(--admin-light, #eef0f2); border-radius: 999px; overflow: hidden; display: flex; }
    .opp-progress > .seg-confirmed { background: #198754; }
    .opp-progress > .seg-na { background: #adb5bd; }

    .opp-filter-chip {
        border: 1px solid var(--admin-border, #dee2e6); background: #fff; color: #495057;
        border-radius: 999px; padding: .25rem .75rem; font-size: .8rem; font-weight: 600;
    }
    .opp-filter-chip.active { background: var(--admin-primary, #6e0707); border-color: var(--admin-primary, #6e0707); color: #fff; }
    .opp-filter-chip .count { opacity: .7; margin-left: .2rem; }

    /* Checklist de 3 estados */
    .opp-checklist-item {
        display: flex; align-items: center; justify-content: space-between; gap: .75rem;
        padding: .45rem 0; border-bottom: 1px solid var(--admin-light, #f1f3f5);
    }
    .opp-checklist-item:last-child { border-bottom: 0; }
    .opp-checklist-label { display: flex; align-items: center; gap: .5rem; font-size: .9rem; }
    .opp-checklist-item[data-status="confirmed"] .opp-checklist-label { color: #146c43; }
    .opp-checklist-item[data-status="not_applicable"] .opp-checklist-label { color: #6c757d; text-decoration: line-through; }
    .opp-tristate { display: inline-flex; border: 1px solid var(--admin-border, #dee2e6); border-radius: 8px; overflow: hidden; flex-shrink: 0; }
    .opp-tristate button {
        border: 0; background: #fff; color: #adb5bd; padding: .2rem .55rem; font-size: .95rem; line-height: 1;
    }
    .opp-tristate button + button { border-left: 1px solid var(--admin-border, #dee2e6); }
    .opp-tristate button:hover { background: var(--admin-hover, #f1f3f5); }
    .opp-tristate button.active[data-value="pending"] { background: #e9ecef; color: #495057; }
    .opp-tristate button.active[data-value="confirmed"] { background: #198754; color: #fff; }
    .opp-tristate button.active[data-value="not_applicable"] { background: #6c757d; color: #fff; }
    .opp-tristate.is-saving { opacity: .5; pointer-events: none; }

    .opp-summary-photo {
        width: 100%; aspect-ratio: 4 / 3; border-radius: 10px; object-fit: cover;
        background: var(--admin-light, #f8f9fa); display: flex; align-items: center; justify-content: center;
        color: #ced4da; font-size: 3rem;
    }
    .opp-summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .75rem 1.25rem; }
    .opp-summary-grid .label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; }
    .opp-summary-grid .value { font-size: .95rem; font-weight: 500; color: #2c3e50; }

    .opp-timeline { list-style: none; padding: 0; margin: 0; }
    .opp-timeline li { position: relative; padding: 0 0 1rem 1.6rem; border-left: 2px solid var(--admin-light, #eef0f2); margin-left: .45rem; }
    .opp-timeline li:last-child { padding-bottom: 0; }
    .opp-timeline .dot {
        position: absolute; left: -.6rem; top: 0; width: 1.15rem; height: 1.15rem; border-radius: 50%;
        background: #fff; border: 2px solid var(--admin-primary, #6e0707); color: var(--admin-primary, #6e0707);
        display: flex; align-items: center; justify-content: center; font-size: .6rem;
    }
    .opp-timeline .when { font-size: .78rem; color: #6c757d; }
    .opp-timeline .msg { font-size: .88rem; white-space: pre-line; }

    .opp-notes { min-height: 220px; font-size: .92rem; }
</style>
@endpush
@endonce
