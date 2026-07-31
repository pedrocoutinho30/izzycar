@extends('frontend.partials.layout')

@section('title', __('legalization.page_title') . ' — ' . $legalization->marca . ' ' . $legalization->modelo . ' | Izzycar')
@section('robots', 'noindex, nofollow')

@section('content')

@php
    $progress   = $legalization->progressPercent();
    $stepsTotal = count($passos);
    $stepsDone  = count($legalization->steps_completed ?? []);
    $docsTotal    = count($documentos);
    $docsUploaded = collect($documentos)->keys()->filter(fn ($slug) => $legalization->hasDocument($slug))->count();
@endphp

{{-- ══════ HERO ══════ --}}
<section class="lzt-hero">
  <div class="lzt-hero__overlay"></div>
  <div class="lzt-hero__inner">
    <div class="lzt-hero__badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      {{ __('legalization.badge') }}
    </div>
    <h1 class="lzt-hero__title">{{ $legalization->marca }} {{ $legalization->modelo }}</h1>
    <p class="lzt-hero__sub">
      @if($legalization->matricula)
        {{ __('legalization.matricula_label') }} <strong>{{ $legalization->matricula }}</strong> ·
      @endif
      {{ $legalization->combustivel }}
    </p>
    <div class="lzt-hero__progress-wrap">
      <div class="lzt-hero__progress-label">{{ __('legalization.progress_done', ['done' => $stepsDone, 'total' => $stepsTotal]) }}</div>
      <div class="lzt-hero__progress-bar">
        <div class="lzt-hero__progress-fill" style="width:{{ $progress }}%"></div>
      </div>
      <div class="lzt-hero__progress-pct">{{ $progress }}%</div>
    </div>
  </div>
</section>

{{-- ══════ CONTENT ══════ --}}
<div class="lzt-page">
  <div class="lzt-container">

    {{-- ── PASSOS ── --}}
    <div class="lzt-card lzt-reveal">
      <div class="lzt-card__head">
        <div class="lzt-card__icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
        </div>
        <div>
          <h2 class="lzt-card__title">{{ __('legalization.steps_title') }}</h2>
          <p class="lzt-card__sub">{{ __('legalization.steps_sub') }}</p>
        </div>
      </div>

      <div class="lzt-timeline">
        @foreach($passos as $num => $passo)
        @php
          $done      = $legalization->isStepCompleted($num);
          $isCurrent = !$done && ($num === 1 || $legalization->isStepCompleted($num - 1));
        @endphp
        <div class="lzt-tl-item {{ $done ? 'is-done' : '' }} {{ $isCurrent ? 'is-current' : '' }}" style="--i:{{ $loop->index }}">
          <div class="lzt-tl-marker">
            @if($done)
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            @else
              <span>{{ $num }}</span>
            @endif
          </div>
          <div class="lzt-tl-content">
            <div class="lzt-tl-title">{{ $passo['titulo'] }}</div>
            @if($isCurrent)
              <span class="lzt-tl-badge">{{ __('legalization.current_badge') }}</span>
            @endif
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- ── DOCUMENTOS ── --}}
    <div class="lzt-card lzt-reveal">
      <div class="lzt-card__head">
        <div class="lzt-card__icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div>
          <h2 class="lzt-card__title">{{ __('legalization.docs_title') }}</h2>
          <p class="lzt-card__sub">{{ __('legalization.docs_sub', ['uploaded' => $docsUploaded, 'total' => $docsTotal]) }}</p>
        </div>
      </div>

      <div class="lzt-docs">
        @foreach($documentos as $slug => $label)
        @php $received = $legalization->hasDocument($slug); @endphp
        <div class="lzt-doc {{ $received ? 'lzt-doc--ok' : '' }}">
          @if($received)
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          @else
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" opacity=".4"><circle cx="12" cy="12" r="9"/></svg>
          @endif
          <span>{{ $label }}</span>
        </div>
        @endforeach
      </div>
    </div>

    {{-- ── FATURA ── --}}
    @if($legalization->invoice_path)
    <div class="lzt-card lzt-reveal lzt-invoice">
      <div class="lzt-invoice__left">
        <div class="lzt-card__icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div>
          <h2 class="lzt-card__title">{{ __('legalization.invoice_title') }}</h2>
          <p class="lzt-card__sub">{{ __('legalization.invoice_sub') }}</p>
        </div>
      </div>
      <a href="{{ route('frontend.legalization.status.invoice', $legalization->token) }}" class="lzt-btn lzt-btn--primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        {{ __('legalization.invoice_download') }}
      </a>
    </div>
    @endif

    <p class="lzt-footer-note">
      {{ __('legalization.footer_question') }} <a href="{{ route('frontend.contact') }}">{{ __('legalization.footer_contact') }}</a>.
    </p>

  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const rObs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('is-visible'); });
  }, { threshold: 0.08 });
  document.querySelectorAll('.lzt-reveal').forEach(el => rObs.observe(el));
});
</script>

<style>
/* ════════════════════════════════════════
   ESTADO DA LEGALIZAÇÃO
════════════════════════════════════════ */
:root {
  --lzt-brand:  #6e0707;
  --lzt-dark:   #111111;
  --lzt-gray:   #6b7280;
  --lzt-light:  #f9fafb;
  --lzt-border: #e5e7eb;
  --lzt-shadow: 0 2px 12px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.04);
}
@keyframes lzt-fadeUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:none; } }

/* ── Hero ── */
.lzt-hero {
  position:relative; background: var(--lzt-dark);
  padding: 4.5rem 1.5rem 3.5rem; overflow:hidden;
}
.lzt-hero__overlay {
  position:absolute; inset:0; pointer-events:none;
  background: radial-gradient(ellipse at 60% 0%, rgba(110,7,7,.2) 0%, transparent 65%);
}
.lzt-hero__inner {
  position:relative; max-width:680px; margin:0 auto; text-align:center;
  animation: lzt-fadeUp .7s ease-out both;
}
.lzt-hero__badge {
  display:inline-flex; align-items:center; gap:.45rem;
  background:rgba(74,222,128,.12); border:1px solid rgba(74,222,128,.25);
  color:#4ade80; font-size:.78rem; font-weight:600;
  padding:.4rem 1rem; border-radius:100px; margin-bottom:1.5rem;
}
.lzt-hero__title {
  font-size: clamp(1.8rem, 4vw, 2.6rem); font-weight:900;
  color:#fff; line-height:1.15; margin-bottom:.5rem; letter-spacing:-.02em;
}
.lzt-hero__sub {
  font-size:1rem; color:rgba(255,255,255,.6); margin-bottom:2rem; line-height:1.6;
}
.lzt-hero__progress-wrap { max-width:360px; margin:0 auto; }
.lzt-hero__progress-label {
  font-size:.75rem; font-weight:600; color:rgba(255,255,255,.5);
  text-transform:uppercase; letter-spacing:.08em; margin-bottom:.6rem;
}
.lzt-hero__progress-bar {
  height:10px; border-radius:100px; background:rgba(255,255,255,.12); overflow:hidden;
}
.lzt-hero__progress-fill {
  height:100%; border-radius:100px;
  background: linear-gradient(90deg, var(--lzt-brand), #9b1111);
  transition: width .6s ease;
}
.lzt-hero__progress-pct { font-size:1.4rem; font-weight:900; color:#fff; margin-top:.6rem; }

/* ── Page ── */
.lzt-page { background: var(--lzt-light); }
.lzt-container { max-width:820px; margin:0 auto; padding:3rem 1.5rem 4rem; }

/* ── Card ── */
.lzt-card {
  background:#fff; border:1px solid var(--lzt-border);
  border-radius:20px; padding:2.5rem;
  box-shadow: var(--lzt-shadow); margin-bottom:1.5rem;
}
.lzt-card__head {
  display:flex; align-items:flex-start; gap:1rem; margin-bottom:2rem;
  padding-bottom:1.25rem; border-bottom:1px solid var(--lzt-border);
}
.lzt-card__icon {
  width:46px; height:46px; border-radius:12px; flex-shrink:0;
  background:#fdf2f8; border:1px solid #fecdd3;
  color: var(--lzt-brand);
  display:flex; align-items:center; justify-content:center;
}
.lzt-card__title { font-size:1.15rem; font-weight:800; color:#111; margin:0 0 .2rem; }
.lzt-card__sub   { font-size:.82rem; color: var(--lzt-gray); margin:0; }

/* ── Timeline de passos ── */
.lzt-timeline { display:flex; flex-direction:column; }

.lzt-tl-item {
  position:relative; display:flex; gap:1.1rem; padding-bottom:1.85rem;
  opacity:0; transform:translateX(-14px);
  transition:opacity .5s ease, transform .5s ease;
  transition-delay: calc(var(--i) * 90ms);
}
.lzt-reveal.is-visible .lzt-tl-item { opacity:1; transform:none; }
.lzt-tl-item:last-child { padding-bottom:0; }

.lzt-tl-item::before {
  content:''; position:absolute; left:19px; top:40px; bottom:-1.85rem; width:2px;
  background: var(--lzt-border); transition: background .6s ease .2s;
}
.lzt-tl-item:last-child::before { display:none; }
.lzt-tl-item.is-done::before { background: linear-gradient(180deg, var(--lzt-brand), #9b1111); }

.lzt-tl-marker {
  position:relative; z-index:1; flex-shrink:0;
  width:40px; height:40px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  background:#fff; border:2px solid var(--lzt-border);
  font-size:.9rem; font-weight:800; color: var(--lzt-gray);
  transition: background .4s ease, border-color .4s ease, color .4s ease;
}
.lzt-tl-item.is-done .lzt-tl-marker {
  background: linear-gradient(135deg, var(--lzt-brand), #9b1111); border-color:transparent;
  animation: lzt-pop .45s ease-out;
}
.lzt-tl-item.is-current .lzt-tl-marker {
  border-color: var(--lzt-brand); color: var(--lzt-brand);
  animation: lzt-pulse-ring 2.2s ease-out infinite;
}

.lzt-tl-content { flex:1; display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; padding-top:.65rem; }
.lzt-tl-title { font-size:.95rem; font-weight:600; color:#111; transition: color .3s ease; }
.lzt-tl-item.is-done .lzt-tl-title { color: var(--lzt-gray); text-decoration: line-through; }
.lzt-tl-item.is-current .lzt-tl-title { color: var(--lzt-brand); }

.lzt-tl-badge {
  display:inline-flex; align-items:center; gap:.35rem;
  background: rgba(110,7,7,.08); color: var(--lzt-brand);
  font-size:.66rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em;
  padding:.28rem .65rem; border-radius:100px;
}
.lzt-tl-badge::before {
  content:''; width:6px; height:6px; border-radius:50%; background: var(--lzt-brand); flex-shrink:0;
  animation: lzt-blink 1.4s ease-in-out infinite;
}

@keyframes lzt-pop { 0% { transform:scale(.6); } 60% { transform:scale(1.12); } 100% { transform:scale(1); } }
@keyframes lzt-pulse-ring {
  0%   { box-shadow: 0 0 0 0 rgba(110,7,7,.28); }
  70%  { box-shadow: 0 0 0 9px rgba(110,7,7,0); }
  100% { box-shadow: 0 0 0 0 rgba(110,7,7,0); }
}
@keyframes lzt-blink { 0%,100% { opacity:1; } 50% { opacity:.25; } }

/* ── Docs ── */
.lzt-docs { display:flex; flex-direction:column; gap:.6rem; }
.lzt-doc {
  display:flex; align-items:center; gap:.75rem;
  padding:.7rem 1rem; border-radius:12px;
  background: var(--lzt-light); color: var(--lzt-gray);
  font-size:.85rem; font-weight:600;
}
.lzt-doc svg { flex-shrink:0; }
.lzt-doc--ok { background:#f0fdf4; color:#15803d; }

/* ── Invoice ── */
.lzt-invoice { display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap; }
.lzt-invoice__left { display:flex; align-items:center; gap:1rem; }

/* ── Buttons ── */
.lzt-btn {
  display:inline-flex; align-items:center; gap:.55rem;
  padding:.85rem 1.75rem; border-radius:12px;
  font-size:.95rem; font-weight:700; text-decoration:none; white-space:nowrap;
  transition:transform .15s, box-shadow .2s;
}
.lzt-btn--primary {
  background: linear-gradient(135deg, var(--lzt-brand), #9b1111);
  color:#fff; border:none;
  box-shadow:0 4px 16px rgba(110,7,7,.3);
}
.lzt-btn--primary:hover {
  color:#fff; transform:translateY(-2px);
  box-shadow:0 8px 24px rgba(110,7,7,.4);
}

.lzt-footer-note { text-align:center; font-size:.85rem; color: var(--lzt-gray); }
.lzt-footer-note a { color: var(--lzt-brand); font-weight:600; text-decoration:none; }

/* ── Reveal ── */
.lzt-reveal { opacity:0; transform:translateY(22px); transition:opacity .5s ease, transform .5s ease; }
.lzt-reveal.is-visible { opacity:1; transform:none; }

@media(max-width:640px) {
  .lzt-card { padding:1.75rem 1.25rem; }
  .lzt-invoice { flex-direction:column; align-items:flex-start; }
}
</style>
@endpush
