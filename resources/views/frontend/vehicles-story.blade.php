<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Story — {{ $vehicle->brand }} {{ $vehicle->model }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #1a1a1a;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            padding: 24px;
            font-family: 'Inter', sans-serif;
        }

        /* ── Controls bar ── */
        .controls {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            align-items: center;
            color: #aaa;
            font-size: 13px;
        }
        .controls button {
            background: #333;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            cursor: pointer;
            transition: background .2s;
        }
        .controls button:hover { background: #6e0707; }
        .controls label { display: flex; align-items: center; gap: 6px; cursor: pointer; }

        /* ── Story wrapper (scales to fit screen) ── */
        .story-outer {
            transform-origin: top center;
        }

        /* ── Story canvas: 1080×1920 ── */
        .story {
            width: 1080px;
            height: 1920px;
            position: relative;
            overflow: hidden;
            background: #0a0a0a;
            font-family: 'Inter', sans-serif;
        }

        /* Photo */
        .st-photo {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 55%;
            object-fit: cover;
            object-position: center;
        }

        /* Gradient overlay */
        .st-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(0,0,0,.12) 0%,
                rgba(0,0,0,.05) 35%,
                rgba(10,10,10,.7) 52%,
                #0a0a0a 65%
            );
        }

        /* Logo pill top-left */
        .st-logo {
            position: absolute;
            top: 70px;
            left: 70px;
            background: rgba(0,0,0,.55);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 100px;
            padding: 18px 44px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .st-logo-dot {
            width: 14px; height: 14px;
            background: #6e0707;
            border-radius: 50%;
        }
        .st-logo-text {
            font-size: 36px;
            font-weight: 900;
            color: #fff;
            letter-spacing: 1px;
        }

        /* Reference pill top-right */
        .st-ref {
            position: absolute;
            top: 70px;
            right: 70px;
            background: rgba(110,7,7,.85);
            border-radius: 100px;
            padding: 18px 40px;
            font-size: 28px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 1px;
        }

        /* Bottom content */
        .st-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 0 80px 110px;
        }

        /* Brand */
        .st-brand {
            font-size: 34px;
            font-weight: 900;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: #6e0707;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .st-brand::before {
            content: '';
            display: inline-block;
            width: 40px;
            height: 4px;
            background: #6e0707;
            border-radius: 2px;
        }

        /* Model */
        .st-model {
            font-size: 96px;
            font-weight: 900;
            color: #fff;
            line-height: .95;
            letter-spacing: -2px;
            margin-bottom: 16px;
        }

        /* Version */
        .st-version {
            font-size: 38px;
            font-weight: 500;
            color: rgba(255,255,255,.45);
            margin-bottom: 56px;
        }

        /* Specs row */
        .st-specs {
            display: flex;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 24px;
            overflow: hidden;
            margin-bottom: 56px;
        }
        .st-spec {
            flex: 1;
            padding: 36px 20px;
            text-align: center;
            border-right: 1px solid rgba(255,255,255,.08);
        }
        .st-spec:last-child { border-right: none; }
        .st-spec-label {
            font-size: 22px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,.35);
            margin-bottom: 10px;
        }
        .st-spec-value {
            font-size: 34px;
            font-weight: 800;
            color: #fff;
            white-space: nowrap;
        }

        /* Status badge (reservado/vendido) */
        .st-status {
            display: inline-block;
            border-radius: 16px;
            padding: 24px 56px;
            font-size: 56px;
            font-weight: 900;
            color: #fff;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 56px;
        }

        /* Price */
        .st-price-row {
            display: flex;
            align-items: flex-end;
            gap: 24px;
            margin-bottom: 60px;
        }
        .st-price-label {
            font-size: 26px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,255,255,.35);
            padding-bottom: 14px;
        }
        .st-price {
            font-size: 116px;
            font-weight: 900;
            color: #fff;
            line-height: 1;
            letter-spacing: -3px;
        }
        .st-price-eur {
            font-size: 64px;
            font-weight: 700;
            color: rgba(255,255,255,.5);
            padding-bottom: 16px;
        }
        .st-iva {
            background: #1d4ed8;
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            border-radius: 10px;
            padding: 12px 24px;
            margin-left: 16px;
            vertical-align: middle;
            display: inline-block;
        }

        /* CTA button */
        .st-cta {
            background: #6e0707;
            color: #fff;
            border-radius: 100px;
            padding: 46px 60px;
            font-size: 38px;
            font-weight: 800;
            text-align: center;
            letter-spacing: .5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .st-cta-arrow {
            width: 72px; height: 72px;
            background: rgba(255,255,255,.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
        }

        /* Electric autonomy highlight */
        .st-electric {
            display: inline-flex;
            align-items: center;
            gap: 16px;
            background: rgba(22,163,74,.15);
            border: 1.5px solid rgba(22,163,74,.35);
            border-radius: 16px;
            padding: 20px 36px;
            font-size: 32px;
            font-weight: 700;
            color: #4ade80;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>

<div class="controls">
    <span>Story 1080×1920</span>
    <button onclick="printStory()">⬇ Screenshot / Imprimir</button>
    <button onclick="toggleScale()">⤢ Ajustar ao ecrã</button>
</div>

<div class="story-outer" id="storyOuter">
    <div class="story" id="story">

        {{-- Foto principal --}}
        @if($vehicle->photos->isNotEmpty())
        <img class="st-photo"
             src="{{ asset('storage/' . $vehicle->photos->first()->path) }}"
             style="object-position: {{ $vehicle->photos->first()->focal_x ?? 50 }}% {{ $vehicle->photos->first()->focal_y ?? 50 }}%;"
             alt="{{ $vehicle->brand }} {{ $vehicle->model }}">
        @else
        <div class="st-photo" style="background: #1a1a1a;"></div>
        @endif

        <div class="st-overlay"></div>

        {{-- Logo --}}
        <div class="st-logo">
            <div class="st-logo-dot"></div>
            <div class="st-logo-text">izzycar</div>
        </div>

        {{-- Referência --}}
        <div class="st-ref">{{ $vehicle->reference }}</div>

        {{-- Conteúdo inferior --}}
        <div class="st-content">

            <div class="st-brand">{{ $vehicle->brand }}</div>
            <div class="st-model">{{ $vehicle->model }}</div>
            @if($vehicle->version)
            <div class="st-version">{{ $vehicle->version }}</div>
            @else
            <div style="margin-bottom:56px;"></div>
            @endif

            {{-- Specs --}}
            <div class="st-specs">
                @if($vehicle->year)
                <div class="st-spec">
                    <div class="st-spec-label">Ano</div>
                    <div class="st-spec-value">{{ $vehicle->year }}</div>
                </div>
                @endif
                @if($vehicle->kilometers)
                <div class="st-spec">
                    <div class="st-spec-label">Km</div>
                    <div class="st-spec-value">{{ number_format($vehicle->kilometers, 0, ',', '.') }}</div>
                </div>
                @endif
                @if($vehicle->fuel)
                <div class="st-spec">
                    <div class="st-spec-label">Combustível</div>
                    <div class="st-spec-value">{{ $vehicle->fuel }}</div>
                </div>
                @endif
                @if($potencia)
                <div class="st-spec">
                    <div class="st-spec-label">Potência</div>
                    <div class="st-spec-value">{{ $potencia }} cv</div>
                </div>
                @endif
                @if($caixa)
                <div class="st-spec">
                    <div class="st-spec-label">Caixa</div>
                    <div class="st-spec-value">{{ $caixa }}</div>
                </div>
                @endif
            </div>

            {{-- Autonomia elétrica --}}
            @if($autonomia && ($vehicle->fuel === 'Elétrico' || str_contains($vehicle->fuel ?? '', 'Híbrido')))
            <div class="st-electric">
                🔋 Autonomia elétrica: <strong>{{ $autonomia }} km</strong>
            </div>
            @endif

            

            {{-- CTA --}}
            <div class="st-cta">
                <span>Ver viatura em izzycar.pt</span>
                <div class="st-cta-arrow">→</div>
            </div>

        </div>{{-- /st-content --}}

    </div>{{-- /story --}}
</div>

<script>
    var scaled = true;

    function applyScale() {
        var outer  = document.getElementById('storyOuter');
        var vw     = window.innerWidth - 80;
        var scale  = scaled ? Math.min(vw / 1080, (window.innerHeight - 120) / 1920) : 1;
        outer.style.transform = 'scale(' + scale + ')';
        outer.style.width  = '1080px';
        outer.style.height = (1920 * scale) + 'px';
        outer.style.marginBottom = scaled ? 0 : '40px';
    }

    function toggleScale() {
        scaled = !scaled;
        applyScale();
        document.querySelector('.controls button:last-child').textContent =
            scaled ? '⤢ Ajustar ao ecrã' : '⤡ Tamanho real (1080×1920)';
    }

    function printStory() {
        window.print();
    }

    window.addEventListener('resize', applyScale);
    applyScale();
</script>

<style>
    @media print {
        body { padding: 0; background: #fff; }
        .controls { display: none; }
        .story-outer { transform: none !important; }
        @page { size: 1080px 1920px; margin: 0; }
    }
</style>

</body>
</html>
