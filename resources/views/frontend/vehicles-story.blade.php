<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Story — {{ $vehicle->brand }} {{ $vehicle->model }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --accent: #6e0707; --bg: #f4f4f6; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #222;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px;
            font-family: 'Inter', sans-serif;
        }

        /* Controls */
        .controls {
            display: flex; gap: 10px; margin-bottom: 20px; align-items: center;
        }
        .controls button {
            background: #444; color: #fff; border: none; border-radius: 8px;
            padding: 8px 18px; font-size: 13px; cursor: pointer; font-family: inherit;
        }
        .controls button:hover { background: var(--accent); }
        .controls span { color: #888; font-size: 13px; }

        /* Outer wrapper */
        .story-outer { transform-origin: top center; }

        /* ── STORY CANVAS 1080×1920 ── */
        .story {
            width: 1080px;
            height: 1920px;
            background: var(--bg);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* ── Top bar ── */
        .st-topbar {
            background: #fff;
            padding: 36px 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e9ecef;
            flex-shrink: 0;
        }
        .st-topbar-logo img {
            height: 48px;
            width: auto;
        }
        .st-topbar-ref {
            font-size: 26px;
            font-weight: 700;
            color: #9ca3af;
            letter-spacing: 1px;
        }

        /* ── Header: brand / status ── */
        .st-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 48px 60px 24px;
            flex-shrink: 0;
        }
        .st-brand {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--accent);
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 12px;
        }
        .st-brand::before {
            content: '';
            width: 28px; height: 4px;
            background: var(--accent);
            border-radius: 2px;
            flex-shrink: 0;
        }
        .st-model {
            font-size: 72px;
            font-weight: 900;
            color: #111;
            line-height: .95;
            letter-spacing: -2px;
        }
        .st-version {
            font-size: 34px;
            font-weight: 500;
            color: #6b7280;
            margin-top: 10px;
        }
        .st-status-badge {
            display: inline-block;
            color: #fff;
            font-size: 28px;
            font-weight: 700;
            padding: 16px 36px;
            border-radius: 40px;
        }

        /* ── Main photo ── */
        .st-photo-wrap {
            flex-shrink: 0;
            padding: 0 60px;
            margin-bottom: 0;
        }
        .st-photo-wrap img {
            width: 100%;
            height: 560px;
            object-fit: cover;
            border-radius: 24px;
            display: block;
        }
        .st-photo-placeholder {
            width: 100%;
            height: 560px;
            background: #e5e7eb;
            border-radius: 24px;
        }

        /* ── Specs bar (same as page) ── */
        .st-specs-bar {
            display: flex;
            background: #fff;
            border: 1.5px solid #e9ecef;
            border-radius: 16px;
            overflow: hidden;
            margin: 32px 60px 0;
            flex-shrink: 0;
        }
        .st-sbi {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 30px 12px;
            text-align: center;
            border-right: 1px solid #f0f0f0;
        }
        .st-sbi:last-child { border-right: none; }
        .st-sbi-icon { font-size: 26px; color: var(--accent); }
        .st-sbi-label {
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #9ca3af;
        }
        .st-sbi-value {
            font-size: 28px;
            font-weight: 800;
            color: #111;
            white-space: nowrap;
        }
        .st-sbi-electric .st-sbi-icon { color: #16a34a; }
        .st-sbi-electric .st-sbi-value { color: #15803d; }
        .st-sbi-electric .st-sbi-label { color: #4ade80; }

        /* ── Action buttons ── */
        .st-actions {
            padding: 32px 60px 0;
            flex-shrink: 0;
        }
        .st-btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            background: var(--accent);
            color: #fff;
            border-radius: 16px;
            padding: 40px 50px;
            font-size: 34px;
            font-weight: 700;
            width: 100%;
            margin-bottom: 20px;
        }
        .st-btn-row {
            display: flex;
            gap: 20px;
        }
        .st-btn-secondary {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            background: #25d366;
            color: #fff;
            border-radius: 16px;
            padding: 36px 40px;
            font-size: 32px;
            font-weight: 700;
        }
        .st-btn-icon {
            width: 110px;
            height: 110px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            border: 1.5px solid #e5e7eb;
            border-radius: 16px;
            font-size: 36px;
            color: #374151;
        }

        /* ── Attribute cards ── */
        .st-attrs {
            padding: 32px 60px 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            flex-shrink: 0;
        }
        .st-attr-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 16px;
            padding: 30px 32px;
        }
        .st-attr-title {
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--accent);
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1.5px solid rgba(110,7,7,.1);
        }
        .st-attr-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .st-attr-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 22px;
            color: #374151;
            line-height: 1.3;
        }
        .st-attr-item i {
            color: var(--accent);
            font-size: 18px;
            flex-shrink: 0;
            margin-top: 3px;
        }

        /* ── Footer ── */
        .st-footer {
            margin-top: auto;
            padding: 30px 60px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .st-footer-url {
            font-size: 28px;
            font-weight: 700;
            color: #9ca3af;
        }
        .st-footer-url span { color: var(--accent); }
        .st-footer-logo img {
            height: 38px;
            width: auto;
            opacity: .5;
        }

        @media print {
            body { padding: 0; background: #fff; }
            .controls { display: none; }
            .story-outer { transform: none !important; }
            @page { size: 1080px 1920px; margin: 0; }
        }
    </style>
    {{-- Bootstrap Icons (same as the site) --}}
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.min.css') }}">
</head>
<body>

<div class="controls">
    <span>1080 × 1920 px</span>
    <button onclick="toggleScale()">⤢ Ajustar ao ecrã</button>
    <button onclick="window.print()">⬇ Guardar / Imprimir</button>
</div>

<div class="story-outer" id="storyOuter">
<div class="story">

    {{-- Top bar --}}
    <div class="st-topbar">
        <div class="st-topbar-logo">
            <img src="{{ asset($logotipo) }}" alt="izzycar">
        </div>
        <div class="st-topbar-ref">Ref. {{ $vehicle->reference }}</div>
    </div>

    {{-- Header --}}
    <div class="st-header">
        <div>
            <div class="st-brand">{{ $vehicle->brand }}</div>
            <div class="st-model">{{ $vehicle->model }}</div>
            @if($vehicle->version)
            <div class="st-version">{{ $vehicle->version }}</div>
            @endif
        </div>
        <div style="padding-top: 8px;">
            @if($vehicle->status === 'reservado')
            <span class="st-status-badge" style="background:#f59e0b;">Reservado</span>
            @elseif($vehicle->status === 'vendido')
            <span class="st-status-badge" style="background:#dc2626;">Vendido</span>
            @else
            <span class="st-status-badge" style="background:var(--accent);">Em Stock</span>
            @endif
        </div>
    </div>

    {{-- Photo --}}
    <div class="st-photo-wrap">
        @if($vehicle->photos->isNotEmpty())
        <img src="{{ asset('storage/' . $vehicle->photos->first()->path) }}"
             style="object-position: {{ $vehicle->photos->first()->focal_x ?? 50 }}% {{ $vehicle->photos->first()->focal_y ?? 50 }}%;"
             alt="{{ $vehicle->brand }} {{ $vehicle->model }}">
        @else
        <div class="st-photo-placeholder"></div>
        @endif
    </div>

    {{-- Specs bar --}}
    <div class="st-specs-bar">
        <div class="st-sbi">
            <i class="bi bi-calendar3 st-sbi-icon"></i>
            <span class="st-sbi-label">Ano</span>
            <span class="st-sbi-value">{{ $vehicle->year ?? '—' }}</span>
        </div>
        <div class="st-sbi">
            <i class="bi bi-speedometer2 st-sbi-icon"></i>
            <span class="st-sbi-label">Km</span>
            <span class="st-sbi-value">{{ $vehicle->kilometers ? number_format($vehicle->kilometers, 0, ',', '.') : '—' }}</span>
        </div>
        <div class="st-sbi">
            <i class="bi bi-fuel-pump st-sbi-icon"></i>
            <span class="st-sbi-label">Combustível</span>
            <span class="st-sbi-value">{{ $vehicle->fuel ?? '—' }}</span>
        </div>
        <div class="st-sbi">
            <i class="bi bi-lightning-charge st-sbi-icon"></i>
            <span class="st-sbi-label">Potência</span>
            <span class="st-sbi-value">{{ $potencia ? $potencia . ' cv' : '—' }}</span>
        </div>
        <div class="st-sbi">
            <i class="bi bi-gear-wide-connected st-sbi-icon"></i>
            <span class="st-sbi-label">Transmissão</span>
            <span class="st-sbi-value">{{ $caixa ?: '—' }}</span>
        </div>
        @if($autonomia && ($vehicle->fuel === 'Elétrico' || str_contains($vehicle->fuel ?? '', 'Híbrido')))
        <div class="st-sbi st-sbi-electric">
            <i class="bi bi-battery-half st-sbi-icon"></i>
            <span class="st-sbi-label">Autonomia</span>
            <span class="st-sbi-value">{{ $autonomia }} km</span>
        </div>
        @endif
    </div>

    {{-- Action buttons --}}
    <div class="st-actions">
        <div class="st-btn-primary">
            <i class="bi bi-envelope-fill"></i> Pedir Informações
        </div>
        <div class="st-btn-row">
            <div class="st-btn-secondary">
                <i class="bi bi-whatsapp"></i> WhatsApp
            </div>
            <div class="st-btn-icon">
                <i class="bi bi-share-fill"></i>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="st-footer">
        <div class="st-footer-url">www.<span>izzycar</span>.pt</div>
        <div class="st-footer-logo">
            <img src="{{ asset($logotipo) }}" alt="izzycar">
        </div>
    </div>

</div>{{-- /story --}}
</div>{{-- /story-outer --}}

<script>
    var scaled = true;
    function applyScale() {
        var outer = document.getElementById('storyOuter');
        var scale = scaled
            ? Math.min((window.innerWidth - 80) / 1080, (window.innerHeight - 100) / 1920)
            : 1;
        outer.style.transform = 'scale(' + scale + ')';
        outer.style.transformOrigin = 'top center';
        outer.style.display = 'block';
        outer.style.width = '1080px';
        document.body.style.minHeight = scaled ? (1920 * scale + 100) + 'px' : '100vh';
    }
    function toggleScale() {
        scaled = !scaled;
        applyScale();
    }
    window.addEventListener('resize', applyScale);
    applyScale();
</script>
</body>
</html>
