{{-- JS das Oportunidades (grelha e detalhe). Tudo por data-attributes, para
     que os mesmos partials funcionem nas duas páginas. --}}
@once
@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const statusColors = ['secondary', 'info', 'warning', 'primary', 'dark', 'success', 'danger'];

    async function patchJson(url, body) {
        const response = await fetch(url, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(body),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const firstError = data.errors ? Object.values(data.errors)[0][0] : null;
            throw new Error(firstError || data.message || 'Não foi possível guardar.');
        }
        return data;
    }

    const toast = (message, type) => window.showToast ? window.showToast(message, type) : alert(message);

    // ---- Estado da oportunidade (dropdown no cartão e no detalhe) ----
    document.addEventListener('click', async (event) => {
        const option = event.target.closest('[data-opp-status] .dropdown-item[data-value]');
        if (!option) return;

        const wrapper = option.closest('[data-opp-status]');
        try {
            const { status } = await patchJson(wrapper.dataset.url, { status: option.dataset.value });
            const button = wrapper.querySelector('.opp-status-badge');
            statusColors.forEach(c => button.classList.remove('text-bg-' + c));
            button.classList.add('text-bg-' + status.color);
            button.querySelector('i').className = 'bi ' + status.icon;
            button.querySelector('span').textContent = status.label;
            wrapper.querySelectorAll('.dropdown-item').forEach(i => i.classList.toggle('active', i === option));

            const card = wrapper.closest('[data-opp-card]');
            if (card) {
                card.dataset.status = status.value;
                const inner = card.querySelector('.opp-card');
                statusColors.forEach(c => inner.classList.remove('opp-accent-' + c));
                inner.classList.add('opp-accent-' + status.color);
                inner.classList.toggle('opp-card--rejeitado', status.value === 'rejeitado');
                applyFilters();
            }
            toast('Estado atualizado: ' + status.label, 'success');
        } catch (error) {
            toast(error.message, 'error');
        }
    });

    // ---- Filtros e ordenação da grelha ----
    const grid = document.querySelector('[data-opp-grid]');
    const filters = document.querySelector('[data-opp-filters]');
    let activeStatus = '';

    function applyFilters() {
        if (!grid) return;
        const term = (filters.querySelector('[data-filter-search]').value || '').trim().toLowerCase();
        let visible = 0;
        grid.querySelectorAll('[data-opp-card]').forEach(card => {
            const show = (!activeStatus || card.dataset.status === activeStatus)
                && (!term || card.dataset.search.includes(term));
            card.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        document.querySelector('[data-opp-no-results]').classList.toggle('d-none', visible > 0);
    }

    function applySort(value) {
        const [field, direction] = value.split(':');
        const cards = [...grid.querySelectorAll('[data-opp-card]')];
        cards.sort((a, b) => {
            const va = a.dataset[field], vb = b.dataset[field];
            // Sem valor vai sempre para o fim.
            if (va === '' && vb === '') return 0;
            if (va === '') return 1;
            if (vb === '') return -1;
            return direction === 'asc' ? va - vb : vb - va;
        });
        cards.forEach(card => grid.appendChild(card));
    }

    if (grid && filters) {
        filters.addEventListener('click', (event) => {
            const chip = event.target.closest('[data-filter-status]');
            if (!chip) return;
            activeStatus = chip.dataset.filterStatus;
            filters.querySelectorAll('[data-filter-status]').forEach(c => c.classList.toggle('active', c === chip));
            applyFilters();
        });
        filters.querySelector('[data-filter-search]').addEventListener('input', applyFilters);
        filters.querySelector('[data-sort]').addEventListener('change', (e) => applySort(e.target.value));
    }

    // ---- Checklist de 3 estados ----
    function renderProgress(progress) {
        document.querySelectorAll('[data-opp-progress]').forEach(el => {
            el.querySelector('[data-role="confirmed"]').textContent = progress.confirmed;
            el.querySelector('[data-role="total"]').textContent = progress.total;
            el.querySelector('[data-role="percent"]').textContent = progress.percent + '%';
            el.querySelector('[data-role="bar-confirmed"]').style.width = progress.confirmed_width + '%';
            el.querySelector('[data-role="bar-na"]').style.width = progress.not_applicable_width + '%';
            el.querySelector('[data-role="breakdown"]').textContent =
                progress.pending + ' por confirmar' + (progress.not_applicable ? ' · ' + progress.not_applicable + ' N/A' : '');
            el.title = progress.pending ? 'Por confirmar: ' + progress.pending_labels.join(', ') : 'Nada pendente';
        });
    }

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-opp-tristate] button[data-value]');
        if (!button || button.classList.contains('active')) return;

        const group = button.closest('[data-opp-tristate]');
        const item = group.closest('.opp-checklist-item');
        group.classList.add('is-saving');
        try {
            const { progress } = await patchJson(group.dataset.url, { item_id: group.dataset.item, status: button.dataset.value });
            group.querySelectorAll('button').forEach(b => b.classList.toggle('active', b === button));
            item.dataset.status = button.dataset.value;
            item.querySelector('.opp-checklist-label i').className = 'bi ' + button.dataset.icon;
            renderProgress(progress);
        } catch (error) {
            toast(error.message, 'error');
        } finally {
            group.classList.remove('is-saving');
        }
    });

    // ---- Notas com gravação automática ----
    document.querySelectorAll('[data-opp-notes]').forEach(textarea => {
        const indicator = document.querySelector(textarea.dataset.indicator);
        let timer = null;
        let lastSaved = textarea.value;

        const save = async () => {
            if (textarea.value === lastSaved) return;
            const value = textarea.value;
            indicator.textContent = 'A guardar…';
            try {
                const data = await patchJson(textarea.dataset.url, { notes: value });
                lastSaved = value;
                indicator.textContent = 'Guardado às ' + data.saved_at;
            } catch (error) {
                indicator.textContent = 'Erro ao guardar';
                toast(error.message, 'error');
            }
        };

        textarea.addEventListener('input', () => {
            indicator.textContent = 'Alterações por guardar';
            clearTimeout(timer);
            timer = setTimeout(save, 1200);
        });
        textarea.addEventListener('blur', () => { clearTimeout(timer); save(); });
        window.addEventListener('beforeunload', (e) => {
            if (textarea.value !== lastSaved) { e.preventDefault(); e.returnValue = ''; }
        });
    });

    // Reabrir o modal de criação se a validação falhou.
    document.querySelectorAll('.modal[data-open-on-load]').forEach(modal => new bootstrap.Modal(modal).show());
})();
</script>
@endpush
@endonce
