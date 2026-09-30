{{-- JS dos Vendedores, partilhado pela área de Vendedores, Oportunidades e
     Veículos. Tudo por data-attributes:
       form[data-seller-form="seller|seller-edit|contact|quick-seller|quick-contact"]
         → antes de submeter, verifica possíveis duplicados e mostra-os em
           [data-dup-alert]; o utilizador decide sempre (nada é associado
           automaticamente). data-ajax submete por fetch e emite "seller:saved".
       [data-seller-picker] → Tom Select de vendedor + contacto dependente. --}}
@once
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    [data-seller-picker] .ts-wrapper { width: 100%; }
    .seller-dup-list { list-style: none; padding: 0; margin: .5rem 0; }
    .seller-dup-list li + li { margin-top: .4rem; }
    .seller-dup-alert .btn { margin-top: .25rem; }
</style>
@endpush
@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const CHECK_URL = @json(route('admin.v2.sellers.check-duplicates'));
    const SEARCH_URL = @json(route('admin.v2.sellers.search'));
    const SELLERS_URL = @json(url('gestao/v2/vendedores'));
    const toast = (message, type) => window.showToast ? window.showToast(message, type) : alert(message);
    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // ================= Deteção de possíveis duplicados =================

    const value = (form, name) => (form.querySelector(`[name="${name}"]`)?.value || '').trim();
    const setValue = (form, name, v) => { const el = form.querySelector(`[name="${name}"]`); if (el) el.value = v; };

    async function checkMatches(form) {
        const payload = {
            email: value(form, 'contact[email]'),
            phone: value(form, 'contact[phone]'),
            whatsapp: value(form, 'contact[whatsapp]'),
            domains: value(form, 'domains'),
            country: value(form, 'country') || form.dataset.sellerCountry || '',
            seller_id: value(form, 'existing_seller_id') || form.dataset.sellerId || '',
            contact_id: form.dataset.contactId || '',
        };
        Object.keys(payload).forEach(key => { if (!payload[key]) delete payload[key]; });
        if (!payload.email && !payload.phone && !payload.whatsapp && !payload.domains) return null;

        try {
            const response = await fetch(CHECK_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(payload),
            });
            if (!response.ok) return null;
            const matches = await response.json();
            return matches.blocking || matches.needs_confirmation ? matches : null;
        } catch {
            return null;
        }
    }

    function contactLine(c) {
        return `<strong>${esc(c.name)}</strong>${c.role ? ' · ' + esc(c.role) : ''}`
            + ` — <a href="${esc(c.seller.url)}" target="_blank" rel="noopener">${esc(c.seller.name)}</a>`
            + (c.seller.is_active ? '' : ' <span class="badge bg-secondary">Inativo</span>');
    }

    const actionButton = (label, action, extra = '', style = 'btn-outline-secondary') =>
        `<button type="button" class="btn btn-sm ${style} me-1" data-dup-action="${action}" ${extra}>${label}</button>`;

    function renderMatches(form, m) {
        const box = form.querySelector('[data-dup-alert]');
        if (!box) return;
        const ctx = form.dataset.sellerForm;
        const quick = ctx.startsWith('quick');
        const creatingSeller = ctx === 'seller' || ctx === 'quick-seller';
        const hasContact = value(form, 'contact[name]') !== '';
        form._dupMatches = m;
        const sections = [];

        if (m.email) {
            const c = m.email;
            sections.push(`
                <div class="mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Já existe um contacto com este email.</strong></div>
                <div class="small">${contactLine(c)}<br>${esc(c.email)}</div>
                <div class="mt-2">
                    <a href="${esc(c.seller.url)}#contacto-${c.id}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary me-1">Abrir contacto</a>
                    ${ctx === 'contact' || ctx === 'seller-edit' ? '' : actionButton('Utilizar contacto existente', 'use', 'data-kind="email" data-index="0"', 'btn-primary-modern')}
                    ${actionButton('Cancelar', 'cancel')}
                </div>`);
        }

        [['phone', 'Encontrámos um contacto com este número de telefone.'], ['whatsapp', 'Encontrámos um contacto com este número de WhatsApp.']]
            .forEach(([kind, title]) => {
                if (!m[kind].length) return;
                const items = m[kind].map((c, i) => `
                    <li>${contactLine(c)}<br><span class="text-muted">${esc([c.phone, c.whatsapp].filter(Boolean).join(' · '))}</span>
                        ${ctx === 'seller-edit' ? '' : '<br>' + actionButton('Utilizar contacto existente', 'use', `data-kind="${kind}" data-index="${i}"`)}
                    </li>`).join('');
                sections.push(`
                    <div><i class="bi bi-exclamation-triangle-fill me-1"></i><strong>${title}</strong></div>
                    <ul class="seller-dup-list small">${items}</ul>
                    ${m.blocking ? '' : `<div>${actionButton('Criar novo contacto', 'proceed', '', 'btn-primary-modern')}${actionButton('Cancelar', 'cancel')}</div>`}`);
            });

        if (m.domain.length) {
            const items = m.domain.map((s, i) => {
                const contacts = s.contacts.length
                    ? '<ul class="mb-1">' + s.contacts.map(c => `<li>${esc(c.name)}${c.email ? ' — ' + esc(c.email) : ''}</li>`).join('') + '</ul>'
                    : '<div class="text-muted">Sem contactos ativos.</div>';
                const addButton = creatingSeller && hasContact
                    ? actionButton('Adicionar a este vendedor', 'add-to-seller', `data-index="${i}"`, 'btn-primary-modern')
                    : `<a href="${esc(s.url)}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary me-1">Abrir vendedor</a>`;
                return `<li><a href="${esc(s.url)}" target="_blank" rel="noopener"><strong>${esc(s.name)}</strong></a>`
                    + (s.country_label ? ` <span class="text-muted">· ${esc(s.country_label)}</span>` : '')
                    + (s.is_active ? '' : ' <span class="badge bg-secondary">Inativo</span>')
                    + `<div class="mt-1">Contactos existentes:</div>${contacts}${m.blocking ? '' : addButton}</li>`;
            }).join('');
            const question = creatingSeller
                ? (hasContact ? 'Este novo contacto pertence a este vendedor?' : 'Pode tratar-se do mesmo vendedor.')
                : (ctx === 'seller-edit' ? 'Outro vendedor já usa este domínio.' : 'Este contacto pode pertencer a outro vendedor.');
            const proceedLabel = creatingSeller ? 'Criar novo vendedor' : (ctx === 'seller-edit' ? 'Guardar mesmo assim' : 'Adicionar mesmo assim');
            sections.push(`
                <div><i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Encontrámos um vendedor com o mesmo domínio${m.domain_name ? ' (' + esc(m.domain_name) + ')' : ''}.</strong></div>
                <div class="small mt-1">${question}</div>
                <ul class="seller-dup-list small">${items}</ul>
                ${m.blocking ? '' : `<div>${actionButton(proceedLabel, 'proceed', '', 'btn-primary-modern')}${actionButton('Cancelar', 'cancel')}</div>`}`);
        }

        box.innerHTML = `<div class="alert alert-warning seller-dup-alert mb-3">${sections.join('<hr class="my-2">')}</div>`;
        box.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    function clearMatches(form) {
        const box = form.querySelector('[data-dup-alert]');
        if (box) box.innerHTML = '';
        form._dupMatches = null;
    }

    function showErrors(form, errors) {
        const box = form.querySelector('[data-dup-alert]');
        const list = Object.values(errors || {}).flat().map(e => `<li>${esc(e)}</li>`).join('');
        box.innerHTML = `<div class="alert alert-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Verifique os campos.<ul class="mb-0 mt-1 small">${list}</ul></div>`;
    }

    async function proceed(form) {
        if (!('ajax' in form.dataset)) {
            HTMLFormElement.prototype.submit.call(form);
            return;
        }

        const submit = form.querySelector('[type="submit"]');
        if (submit) submit.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: new FormData(form),
            });
            const data = await response.json().catch(() => ({}));

            if (response.status === 422) {
                const matches = data.errors?.matches ? await checkMatches(form) : null;
                matches ? renderMatches(form, matches) : showErrors(form, data.errors);
                return;
            }
            if (!response.ok) throw new Error(data.message || 'Não foi possível guardar.');

            clearMatches(form);
            form.dispatchEvent(new CustomEvent('seller:saved', { bubbles: true, detail: data }));
        } catch (error) {
            toast(error.message, 'error');
        } finally {
            if (submit) submit.disabled = false;
        }
    }

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-seller-form]');
        if (!form) return;
        event.preventDefault();

        if (value(form, 'confirm_matches') !== '1') {
            const matches = await checkMatches(form);
            if (matches) {
                renderMatches(form, matches);
                return;
            }
        }
        proceed(form);
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-dup-action]');
        if (!button) return;
        const form = button.closest('form[data-seller-form]');
        const m = form._dupMatches;
        const action = button.dataset.dupAction;

        if (action === 'cancel') {
            clearMatches(form);
            form.querySelector('[data-dup-field]')?.focus();
        } else if (action === 'proceed') {
            setValue(form, 'confirm_matches', '1');
            proceed(form);
        } else if (action === 'add-to-seller') {
            setValue(form, 'existing_seller_id', m.domain[button.dataset.index].id);
            setValue(form, 'confirm_matches', '1');
            proceed(form);
        } else if (action === 'use') {
            const contact = button.dataset.kind === 'email' ? m.email : m[button.dataset.kind][button.dataset.index];
            if (form.dataset.sellerForm.startsWith('quick')) {
                form.dispatchEvent(new CustomEvent('seller:use-existing', { bubbles: true, detail: { seller: contact.seller, contact } }));
            } else {
                window.location = contact.seller.url + '#contacto-' + contact.id;
            }
        }
    });

    // Aviso logo ao sair dos campos (email, telefone, WhatsApp, domínios).
    document.addEventListener('change', async (event) => {
        const field = event.target.closest('form[data-seller-form] [data-dup-field], form[data-seller-form] [data-seller-domains]');
        if (!field) return;
        const form = field.closest('form');
        setValue(form, 'confirm_matches', '0');
        setValue(form, 'existing_seller_id', '');
        const matches = await checkMatches(form);
        matches ? renderMatches(form, matches) : clearMatches(form);
    });


    // ================= Seletor de vendedor + contacto =================

    function tomSelectReady() {
        if (window.TomSelect) return Promise.resolve();
        return new Promise((resolve) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/tom-select@2/dist/js/tom-select.complete.min.js';
            script.onload = resolve;
            document.head.appendChild(script);
        });
    }

    const contactsUrl = (sellerId) => `${SELLERS_URL}/${sellerId}/contactos`;

    function initPicker(root) {
        const sellerSelect = root.querySelector('[data-role="seller"]');
        const contactSelect = root.querySelector('[data-role="contact"]');
        const newContactButton = root.querySelector('[data-action="new-contact"]');

        const sellerTs = new TomSelect(sellerSelect, {
            valueField: 'id',
            labelField: 'name',
            searchField: ['name', 'primary_contact', 'country_label'],
            placeholder: 'Pesquisar vendedor...',
            preload: 'focus',
            loadThrottle: 250,
            load(query, callback) {
                fetch(SEARCH_URL + '?q=' + encodeURIComponent(query), { headers: { 'Accept': 'application/json' } })
                    .then(r => r.json()).then(callback).catch(() => callback());
            },
            render: {
                option: (d, e) => `<div><div>${e(d.name)}</div><small class="text-muted">${[d.country_label, d.primary_contact].filter(Boolean).map(e).join(' · ')}</small></div>`,
                item: (d, e) => `<div>${e(d.name)}</div>`,
                no_results: () => '<div class="no-results">Nenhum vendedor encontrado — use "+ Novo vendedor".</div>',
            },
            onChange: (id) => loadContacts(id, null),
        });

        const contactTs = new TomSelect(contactSelect, {
            valueField: 'id',
            labelField: 'name',
            searchField: ['name', 'email', 'role'],
            placeholder: 'Escolher contacto...',
            render: {
                option: (d, e) => `<div><div>${e(d.name)}${d.is_primary ? ' <span class="badge bg-light text-dark">Principal</span>' : ''}</div><small class="text-muted">${[d.role, d.email, d.phone].filter(Boolean).map(e).join(' · ')}</small></div>`,
                no_results: () => '<div class="no-results">Sem contactos — use "+ Novo contacto".</div>',
            },
        });

        function toggleContact(enabled) {
            enabled ? contactTs.enable() : contactTs.disable();
            if (newContactButton) newContactButton.disabled = !enabled;
        }

        async function loadContacts(sellerId, selectId) {
            contactTs.clear(true);
            contactTs.clearOptions();
            toggleContact(!!sellerId);
            if (!sellerId) return;

            const contacts = await fetch(contactsUrl(sellerId), { headers: { 'Accept': 'application/json' } }).then(r => r.json()).catch(() => []);
            contactTs.addOptions(contacts);
            // Sem contacto indicado, sugere o principal (pode ser alterado).
            const pick = selectId ?? contacts.find(c => c.is_primary)?.id;
            if (pick) contactTs.setValue(String(pick), true);
        }

        toggleContact(!!sellerTs.getValue());

        root._picker = {
            sellerId: () => sellerTs.getValue(),
            seller: () => sellerTs.options[sellerTs.getValue()] || null,
            setSeller(seller, contact) {
                sellerTs.addOption(seller);
                sellerTs.setValue(String(seller.id), true);
                loadContacts(seller.id, contact ? contact.id : null);
            },
            addContact(contact) {
                contactTs.addOption(contact);
                contactTs.setValue(String(contact.id), true);
            },
        };
    }

    // ---- Modais rápidos "Novo vendedor" / "Novo contacto" ----
    let activePicker = null;
    let returnModal = null;

    function resetQuickForm(form) {
        form.reset();
        clearMatches(form);
        setValue(form, 'confirm_matches', '0');
        setValue(form, 'existing_seller_id', '');
    }

    function hideQuickModal(form) {
        bootstrap.Modal.getInstance(form.closest('.modal'))?.hide();
    }

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-seller-picker] [data-action]');
        if (!button) return;
        activePicker = button.closest('[data-seller-picker]');

        const isContact = button.dataset.action === 'new-contact';
        const modal = document.getElementById(isContact ? 'sellerContactQuickModal' : 'sellerQuickModal');
        const form = modal.querySelector('form');
        resetQuickForm(form);

        if (isContact) {
            const seller = activePicker._picker.seller();
            if (!seller) return;
            form.action = contactsUrl(seller.id);
            form.dataset.sellerId = seller.id;
            form.dataset.sellerCountry = seller.country || '';
            modal.querySelector('[data-seller-name]').textContent = seller.name;
        }

        // Bootstrap não empilha modais: esconde o da oportunidade e volta a
        // abri-lo (com o que já estava preenchido) quando este fechar.
        const parent = button.closest('.modal');
        returnModal = parent;
        if (parent) {
            const parentModal = bootstrap.Modal.getOrCreateInstance(parent);
            const hideParent = () => {
                parent.addEventListener('hidden.bs.modal', () => bootstrap.Modal.getOrCreateInstance(modal).show(), { once: true });
                parentModal.hide();
            };
            // O Bootstrap ignora hide() enquanto o modal ainda está a abrir.
            parentModal._isTransitioning ? parent.addEventListener('shown.bs.modal', hideParent, { once: true }) : hideParent();
        } else {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    });

    // Os modais rápidos podem ser incluídos depois deste script (vão para o
    // mesmo stack), por isso a ligação espera pelo DOM completo.
    function bindQuickModals() {
        ['sellerQuickModal', 'sellerContactQuickModal'].forEach(id => {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.addEventListener('hidden.bs.modal', () => {
                if (returnModal) bootstrap.Modal.getOrCreateInstance(returnModal).show();
                returnModal = null;
            });
            const form = modal.querySelector('form');

            form.addEventListener('seller:saved', (event) => {
                const { seller, contact, message } = event.detail;
                if (activePicker) {
                    seller ? activePicker._picker.setSeller(seller, contact) : activePicker._picker.addContact(contact);
                }
                toast(message, 'success');
                hideQuickModal(form);
            });
            form.addEventListener('seller:use-existing', (event) => {
                const { seller, contact } = event.detail;
                if (activePicker) {
                    const current = activePicker._picker.sellerId();
                    String(current) === String(seller.id) && id === 'sellerContactQuickModal'
                        ? activePicker._picker.addContact(contact)
                        : activePicker._picker.setSeller(seller, contact);
                }
                hideQuickModal(form);
            });
        });
    }

    function ready(fn) {
        document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', fn) : fn();
    }

    ready(() => {
        bindQuickModals();

        // Validação falhou no servidor por correspondências: mostrá-las de novo.
        document.querySelectorAll('form[data-seller-form][data-recheck]').forEach(async (form) => {
            const matches = await checkMatches(form);
            if (matches) renderMatches(form, matches);
        });

        // Reabrir modais cujo formulário falhou a validação.
        document.querySelectorAll('.modal[data-seller-open-on-load]').forEach(modal => new bootstrap.Modal(modal).show());

        const pickers = document.querySelectorAll('[data-seller-picker]');
        if (pickers.length) tomSelectReady().then(() => pickers.forEach(initPicker));
    });
})();
</script>
@endpush
@endonce
