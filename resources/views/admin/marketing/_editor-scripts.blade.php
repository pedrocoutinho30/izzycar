@php
    $fotosPost = $post->data['photos'] ?? [];
    $photoKeys = array_keys($fotosPost);
    $photoUrls = [];
    $baseFocal = [];
    foreach ($fotosPost as $i => $f) {
        $photoUrls[$i] = \App\Marketing\PostContent::photoUrl($post, (int) $i, $f);
        $baseFocal[$i] = [$f['focal_x'] ?? 50, $f['focal_y'] ?? 50];
    }
@endphp
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script>
(function () {
    const root = document.getElementById('studio');
    const CSRF = @json(csrf_token());
    const photos = @json($photoKeys);
    const photoUrls = @json($photoUrls);
    const baseFocal = @json($baseFocal);
    const slug = @json(\Illuminate\Support\Str::slug($post->title ?: 'publicacao'));

    const $ = id => document.getElementById(id);
    let order = @json(array_slice($config['photo_order'] ?? array_keys($post->data['photos'] ?? []), 0, 12));
    let focal = @json((object) ($config['focal'] ?? []));
    let current = {w: {{ $preview['width'] }}, h: {{ $preview['height'] }}, slides: {{ $preview['slides'] }}};
    let timer = null, seq = 0;
    const isText = @json($kind === 'text');
    const isQuote = @json($kind === 'quote');
    const legal = @json($legalCheck);
    const sourceLost = @json((bool) $sourceLost);
    let overflow = false;
    const shownStatus = @json($post->status->value);
    let items = @json(array_values($overrides['items'] ?? $defaults['items']));

    function state() {
        const o = {cta: $('o_cta').value};
        if (!isQuote) { o.title = $('o_title').value; o.subtitle = $('o_subtitle').value; }
        if (isQuote) {
            o.quote = $('o_quote').value;
            o.show_full_name = $('o_fullname').checked;
        } else if (isText) {
            o.items = items.filter(i => i.a.trim() || i.b.trim());
        } else {
            o.badge = $('o_badge').value;
            o.highlights = $('o_highlights').value.split(/\r?\n/).map(s => s.trim()).filter(Boolean).slice(0, 6);
            if ($('o_price')) o.price = $('o_price').value === '' ? null : $('o_price').value;
        }
        if ($('o_savings')) o.savings = $('o_savings').value === '' ? null : $('o_savings').value;
        const s = {template: $('f_template').value, format: $('f_format').value, overrides: o, photo_order: order};
        if (Object.keys(focal).length) s.focal = focal;
        if ($('f_perslide')) s.gallery_photos_per_slide = parseInt($('f_perslide').value, 10);
        return s;
    }

    function syncFormats() {
        const t = $('f_template').selectedOptions[0];
        const allowed = JSON.parse(t.dataset.formats);
        [...$('f_format').options].forEach(o => { o.disabled = !allowed.includes(o.value); });
        if (!allowed.includes($('f_format').value)) $('f_format').value = allowed[0];
        $('badgeBox')?.classList.toggle('d-none', !['novo-stock', 'vendido', 'recomendacao', 'importacao'].includes($('f_template').value));
        $('perSlideBox')?.classList.toggle('d-none', $('f_template').value !== 'carrossel');
    }

    function frames(html) {
        const stage = $('exportStage');
        stage.innerHTML = html;
        const wrap = $('previewFrames');
        wrap.innerHTML = '';
        const avail = Math.min(wrap.clientWidth || 480, 520);
        const sc = Math.min(1, avail / current.w);
        stage.querySelectorAll('.mk-slide').forEach(sl => {
            const f = document.createElement('div');
            f.style.cssText = `width:${current.w * sc}px;height:${current.h * sc}px;overflow:hidden;border-radius:6px;box-shadow:0 2px 12px rgba(0,0,0,.18);flex:0 0 auto`;
            const c = sl.cloneNode(true);
            c.style.transformOrigin = 'top left';
            c.style.transform = `scale(${sc})`;
            f.appendChild(c);
            wrap.appendChild(f);
        });
        $('btnZip').classList.toggle('d-none', current.slides < 2);
        // Texto que não cabe fica cortado na imagem: avisa e bloqueia a exportação.
        overflow = [...stage.querySelectorAll('.mk-txt__main, .mk-mito__top, .mk-mito__bottom')].some(n => n.scrollHeight > n.clientHeight + 2);
        $('overflowWarn')?.classList.toggle('d-none', !overflow);
    }

    function refresh() {
        clearTimeout(timer);
        timer = setTimeout(async () => {
            const my = ++seq;
            const r = await fetch(root.dataset.previewUrl, {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF}, body: JSON.stringify(state())});
            if (my !== seq) return;
            if (!r.ok) { $('saveMsg').textContent = 'Não foi possível atualizar a pré-visualização.'; return; }
            const j = await r.json();
            current = {w: j.width, h: j.height, slides: j.slides};
            if (j.format !== $('f_format').value) $('f_format').value = j.format;
            frames(j.html);
        }, 250);
    }

    // ── Fotos ──
    function drawPhotos() {
        const grid = $('photoGrid'); if (!grid) return;
        grid.innerHTML = '';
        photos.forEach(i => {
            const pos = order.indexOf(i);
            const d = document.createElement('div');
            d.style.cssText = 'position:relative;width:104px;height:78px;border-radius:6px;overflow:hidden;cursor:pointer;border:3px solid ' + (pos >= 0 ? '#B59151' : 'transparent') + ';opacity:' + (pos >= 0 ? 1 : .45);
            const f = focal[i] || baseFocal[i] || [50, 50];
            d.innerHTML = `<div style="width:100%;height:100%;background:#262626 url('${photoUrls[i]}') ${f[0]}% ${f[1]}%/cover"></div>` +
                (pos >= 0 ? `<span style="position:absolute;left:4px;top:4px;background:#111;color:#fff;font-size:11px;padding:1px 6px;border-radius:10px">${pos === 0 ? '★ ' : ''}${pos + 1}</span>` : '');
            d.addEventListener('click', () => { if (pos < 0 && order.length >= 12) { $('saveMsg').textContent = 'Máximo de 12 fotos.'; return; }
                order = pos >= 0 ? order.filter(x => x !== i) : [...order, i]; drawPhotos(); refresh(); });
            d.addEventListener('dblclick', e => {
                if (pos < 0) return;
                const r = d.getBoundingClientRect();
                focal[i] = [Math.round((e.clientX - r.left) / r.width * 100), Math.round((e.clientY - r.top) / r.height * 100)];
                drawPhotos(); refresh();
            });
            grid.appendChild(d);
        });
    }

    // ── Conteúdo de texto (educativos e serviços) ──
    function maxItems() { return parseInt($('f_template').selectedOptions[0].dataset.maxItems || '0', 10); }
    function drawItems() {
        const box = $('itemsList'); if (!box) return;
        box.innerHTML = '';
        items.forEach((it, i) => {
            const row = document.createElement('div');
            row.className = 'border rounded p-2 mb-2';
            const a = document.createElement('input'); a.className = 'form-control form-control-sm mb-1'; a.maxLength = 160; a.placeholder = 'a'; a.value = it.a;
            const b = document.createElement('textarea'); b.className = 'form-control form-control-sm mb-1'; b.rows = 2; b.maxLength = 500; b.placeholder = 'b'; b.value = it.b;
            const rm = document.createElement('button'); rm.type = 'button'; rm.className = 'btn btn-sm btn-outline-danger'; rm.innerHTML = '<i class="bi bi-trash"></i>';
            a.addEventListener('input', () => { items[i].a = a.value; refresh(); });
            b.addEventListener('input', () => { items[i].b = b.value; refresh(); });
            rm.addEventListener('click', () => { items.splice(i, 1); drawItems(); refresh(); });
            row.append(a, b, rm); box.appendChild(row);
        });
        const limit = maxItems() || 8;
        const full = items.length >= limit;
        let warn = $('itemsOverWarn');
        if (!warn) {
            warn = document.createElement('div'); warn.id = 'itemsOverWarn'; warn.className = 'alert alert-warning py-1 px-2 small d-none';
            box.after(warn);
        }
        warn.textContent = `Este template mostra só os primeiros ${limit} itens; os restantes não aparecem na imagem.`;
        warn.classList.toggle('d-none', items.length <= limit);
        $('btnAddItem').disabled = full;
        $('btnAddItem').title = full ? 'Máximo de itens deste template.' : '';
    }

    // ── Guardar ──
    async function save() {
        const s = state();
        const body = Object.assign({}, s, {
            title: $('o_title')?.value, caption: $('f_caption').value, hashtags: $('f_hashtags').value,
            cta: $('f_cta').value, status: $('f_status').value || undefined,
            scheduled_for: $('f_when').value || null, channel: $('f_channel').value || null,
        });
        const r = await fetch(root.dataset.updateUrl, {method: 'PUT', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF}, body: JSON.stringify(body)});
        const j = await r.json().catch(() => ({}));
        if (!r.ok) { $('saveMsg').textContent = (j.message || 'Erro ao guardar.'); return false; }
        $('priceWarn')?.classList.toggle('d-none', !j.price_differs);
        $('saveMsg').textContent = 'Guardado.';
        if (j.status && j.status !== shownStatus) location.reload(); // aprovado <-> agendado
        return true;
    }

    // ── Exportação (o mesmo HTML da pré-visualização, em tamanho real) ──
    async function ready() {
        if (document.fonts) { await Promise.all(['400', '600', '700'].map(w => document.fonts.load(w + ' 24px Inter'))); await document.fonts.ready; }
    }
    async function capture(node, type) {
        const canvas = await html2canvas(node, {width: current.w, height: current.h, scale: 1, useCORS: true, logging: false, backgroundColor: null, windowWidth: current.w, windowHeight: current.h});
        return await new Promise(res => canvas.toBlob(res, type === 'jpg' ? 'image/jpeg' : 'image/png', 0.95));
    }
    function download(blob, name) {
        const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = name; document.body.appendChild(a); a.click();
        setTimeout(() => { URL.revokeObjectURL(a.href); a.remove(); }, 1000);
    }
    async function exportNodes(type, zip) {
        if (sourceLost) { $('saveMsg').textContent = 'Exportação bloqueada: o testemunho já não tem autorização.'; return; }
        if (overflow) { $('saveMsg').textContent = 'O texto não cabe na imagem: encurta-o antes de exportar.'; return; }
        if (isQuote && !$('o_quote').value.trim()) { $('saveMsg').textContent = 'Escolhe o excerto do testemunho antes de exportar.'; return; }
        if (isText && !items.some(i => i.a.trim() || i.b.trim())) { $('saveMsg').textContent = 'Preenche o conteúdo antes de exportar.'; return; }
        if (legal && !confirm('Este tema é fiscal/legal. Confirmaste na legislação atual os valores, prazos e condições do texto?')) return;
        $('saveMsg').textContent = 'A exportar…';
        try {
            await ready();
            const nodes = [...$('exportStage').querySelectorAll('.mk-slide')];
            if (zip) {
                const z = new JSZip();
                for (let i = 0; i < nodes.length; i++) z.file(`${slug}-${i + 1}.${type}`, await capture(nodes[i], type));
                download(await z.generateAsync({type: 'blob'}), `${slug}.zip`);
            } else {
                for (let i = 0; i < nodes.length; i++) download(await capture(nodes[i], type), `${slug}${nodes.length > 1 ? '-' + (i + 1) : ''}.${type}`);
            }
            $('saveMsg').textContent = 'Exportado.';
        } catch (e) { console.error(e); $('saveMsg').textContent = 'Erro na exportação.'; }
    }

    // ── Legenda (sugestão; nunca grava sozinha) ──
    $('btnCaption').addEventListener('click', async () => {
        if (sourceLost) { $('captionMsg').textContent = 'Bloqueado: o testemunho já não tem autorização.'; return; }
        if ($('f_caption').value.trim() && !confirm('Substituir a legenda atual pela sugestão?')) return;
        const btn = $('btnCaption'); btn.disabled = true; $('captionMsg').textContent = 'A gerar…';
        try {
            if (!(await save())) { $('captionMsg').textContent = 'Corrige os erros antes de gerar a legenda.'; return; }
            const r = await fetch(root.dataset.captionUrl, {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF}, body: JSON.stringify({tone: $('f_tone').value})});
            const j = await r.json().catch(() => ({}));
            if (!r.ok) { $('captionMsg').textContent = j.message || 'Não foi possível gerar a legenda.'; return; }
            $('f_caption').value = j.caption; $('f_hashtags').value = j.hashtags; $('f_cta').value = j.cta;
            $('captionMsg').textContent = (j.warning || 'Sugestão gerada com IA.') + ' Revê e guarda.';
        } finally { btn.disabled = false; }
    });
    $('btnSave').addEventListener('click', save);
    $('btnPng').addEventListener('click', () => exportNodes('png', false));
    $('btnJpg').addEventListener('click', () => exportNodes('jpg', false));
    $('btnZip').addEventListener('click', () => exportNodes('png', true));
    $('btnCopy').addEventListener('click', async () => {
        const t = [$('f_caption').value, $('f_cta').value, $('f_hashtags').value].filter(Boolean).join('\n\n');
        await navigator.clipboard.writeText(t); $('saveMsg').textContent = 'Legenda copiada.';
    });
    $('btnTxt').addEventListener('click', () => {
        const t = [$('f_caption').value, $('f_cta').value, $('f_hashtags').value].filter(Boolean).join('\n\n');
        download(new Blob([t], {type: 'text/plain;charset=utf-8'}), `${slug}-legenda.txt`);
    });
    ['f_template', 'f_format', 'o_quote', 'o_title', 'o_subtitle', 'o_price', 'o_savings', 'o_highlights', 'o_cta', 'o_badge', 'f_perslide']
        .forEach(id => $(id)?.addEventListener(id.startsWith('f_t') ? 'change' : 'input', () => { if (id === 'f_template') syncFormats(); refresh(); }));
    $('o_fullname')?.addEventListener('change', refresh);
    $('f_format').addEventListener('change', refresh);
    $('f_perslide')?.addEventListener('change', refresh);

    $('btnAddItem')?.addEventListener('click', () => { items.push({a: '', b: ''}); drawItems(); refresh(); });
    $('f_template').addEventListener('change', drawItems);
    syncFormats(); drawItems(); drawPhotos(); frames($('exportStage').innerHTML);
    window.addEventListener('resize', () => frames($('exportStage').innerHTML));
})();
</script>
