@push('styles')
<style>
    .process-steps-modern {
        display: flex;
        gap: 2rem;
        margin-top: 2rem;
        align-items: flex-start;
    }

    /* Sidebar — card unificado com timeline */
    .process-nav-modern {
        flex: 0 0 280px;
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid rgba(0,0,0,0.07);
        box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        overflow: hidden;
        position: relative;
    }

    /* Linha vertical de timeline */
    .process-nav-modern::before {
        content: '';
        position: absolute;
        left: 41px;
        top: 40px;
        bottom: 40px;
        width: 2px;
        background: linear-gradient(180deg, rgba(153,0,0,0.7) 0%, rgba(153,0,0,0.1) 100%);
        z-index: 0;
        pointer-events: none;
    }

    .process-nav-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.1rem 1.25rem;
        background: transparent;
        border: none;
        border-left: 3px solid transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        border-radius: 0;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
        z-index: 1;
    }

    .process-nav-item:last-child {
        border-bottom: none;
    }

    .process-nav-item:hover {
        background: rgba(153,0,0,0.03);
        border-left-color: rgba(153,0,0,0.35);
    }

    .process-nav-item.active {
        background: linear-gradient(135deg, rgba(110,7,7,0.07) 0%, rgba(153,0,0,0.02) 100%);
        border-left-color: #990000;
    }

    .process-step-number {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        background: #f0f0f0;
        border-radius: 50%;
        border: 2px solid #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        color: #aaa;
        transition: all 0.3s ease;
        position: relative;
        z-index: 2;
    }

    .process-nav-item.active .process-step-number {
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        border-color: #990000;
        color: #fff;
        box-shadow: 0 4px 14px rgba(153,0,0,0.45), 0 0 0 3px rgba(153,0,0,0.15);
    }

    .process-step-title {
        flex: 1;
        font-size: 0.88rem;
        font-weight: 600;
        color: #888;
        margin: 0;
        transition: color 0.25s ease;
        line-height: 1.35;
    }

    .process-nav-item.active .process-step-title {
        color: #990000;
        font-weight: 700;
    }

    /* Seta indicadora no item ativo */
    .process-nav-item .step-arrow {
        flex-shrink: 0;
        width: 20px;
        color: transparent;
        transition: color 0.25s ease;
    }

    .process-nav-item.active .step-arrow {
        color: #990000;
    }

    /* Painel de conteúdo */
    .process-content-modern {
        flex: 1;
        background: #ffffff;
        border-radius: 20px;
        padding: 2.5rem 3rem;
        border: 1px solid rgba(0,0,0,0.06);
        border-top: 4px solid #990000;
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        min-height: 450px;
        position: relative;
        overflow: hidden;
    }

    .process-content-modern::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 260px;
        height: 260px;
        background: radial-gradient(ellipse at top right, rgba(153,0,0,0.04) 0%, transparent 70%);
        pointer-events: none;
    }

    /* Label do passo ativo no topo do painel */
    .process-content-step-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid rgba(0,0,0,0.07);
        position: relative;
        z-index: 1;
    }

    .step-label-num {
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 0.3rem 0.8rem;
        border-radius: 50px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .step-label-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #111;
    }

    .process-content-inner {
        display: none;
        position: relative;
        z-index: 1;
    }

    .process-content-inner.active {
        display: block;
        animation: fadeInContent 0.35s ease;
    }

    @keyframes fadeInContent {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .process-content-text {
        font-size: 1rem;
        line-height: 1.85;
        color: #444;
        margin-bottom: 2rem;
    }

    .process-content-text p { margin-bottom: 0.75rem; }

    .process-content-text ul {
        list-style: none;
        padding-left: 0;
        margin: 1rem 0;
    }

    .process-content-text ul li {
        position: relative;
        padding-left: 1.75rem;
        margin-bottom: 0.6rem;
        color: #444;
    }

    .process-content-text ul li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.55rem;
        width: 8px;
        height: 8px;
        background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(153,0,0,0.35);
    }

    .process-content-image {
        text-align: center;
        margin-top: 2rem;
    }

    .process-content-image img {
        max-width: 400px;
        width: 100%;
        height: 280px;
        object-fit: cover;
        border-radius: 16px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        transition: transform 0.3s ease;
    }

    .process-content-image img:hover {
        transform: scale(1.02);
    }

    @media (max-width: 992px) {
        .process-steps-modern {
            flex-direction: column;
        }

        .process-nav-modern {
            flex: 1;
            width: 100%;
        }

        .process-nav-modern::before {
            display: none;
        }

        .process-content-modern {
            padding: 2rem;
        }
    }

    @media (max-width: 768px) {
        .process-nav-item {
            padding: 0.9rem 1rem;
        }

        .process-step-title {
            font-size: 0.85rem;
        }

        .process-content-modern {
            padding: 1.5rem;
        }

        .process-content-text {
            font-size: 0.95rem;
        }

        .process-content-image img {
            height: 200px;
        }
    }
</style>
@endpush

<div class="process-steps-modern">
    <div class="process-nav-modern">
        @foreach ($data as $index => $tab)
        @php $slug = \Illuminate\Support\Str::slug($tab['title']); @endphp
        <div class="process-nav-item {{ $index === 0 ? 'active' : '' }}"
             data-target="pane-import-{{ $slug }}"
             data-step="{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}"
             data-title="{{ $tab['title'] }}"
             onclick="switchProcessTab(this, 'pane-import-{{ $slug }}')">
            <div class="process-step-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="process-step-title">{{ $tab['title'] }}</div>
            <svg class="step-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </div>
        @endforeach
    </div>

    <div class="process-content-modern">
        <div class="process-content-step-label" id="processStepLabel">
            @if(!empty($data[0]))
            <span class="step-label-num">Passo {{ str_pad(1, 2, '0', STR_PAD_LEFT) }}</span>
            <span class="step-label-title">{{ $data[0]['title'] }}</span>
            @endif
        </div>

        @foreach ($data as $index => $tab)
        @php $slug = \Illuminate\Support\Str::slug($tab['title']); @endphp
        <div class="process-content-inner {{ $index === 0 ? 'active' : '' }}" id="pane-import-{{ $slug }}">
            <div class="process-content-text">
                {!! $tab['content'] !!}
            </div>
            @if(!empty($tab['image']))
            <div class="process-content-image">
                <img src="{{ asset('storage/'. $tab['image']) }}"
                     onerror="this.src='{{ asset('img/logo-simples.png') }}';"
                     alt="{{ $tab['title'] }}">
            </div>
            @else
            <div class="process-content-image">
                <img src="{{ asset('img/logo-simples.png') }}"
                     alt="{{ $tab['title'] }}">
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

<script>
function switchProcessTab(element, targetId) {
    document.querySelectorAll('.process-nav-item').forEach(function(item) {
        item.classList.remove('active');
    });
    element.classList.add('active');

    document.querySelectorAll('.process-content-inner').forEach(function(panel) {
        panel.classList.remove('active');
    });
    document.getElementById(targetId).classList.add('active');

    var label = document.getElementById('processStepLabel');
    if (label) {
        label.innerHTML =
            '<span class="step-label-num">Passo ' + element.dataset.step + '</span>' +
            '<span class="step-label-title">' + element.dataset.title + '</span>';
    }
}
</script>
