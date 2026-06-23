@extends('frontend.partials.layout')

@section('content')

{{-- ══════ HERO ══════ --}}
<section class="sr-hero">
  <div class="sr-hero__overlay"></div>
  <div class="sr-hero__inner">
    <div class="sr-hero__badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      Simulação concluída
    </div>
    <h1 class="sr-hero__title">Olá, {{ $name }}!</h1>
    <p class="sr-hero__sub">Aqui está a estimativa de custos para importar o seu veículo para Portugal.</p>
    <div class="sr-hero__total-wrap">
      <div class="sr-hero__total-label">Total Chave na Mão (estimativa)</div>
      <div class="sr-hero__total" data-count="{{ (int)$custoTotal }}">{{ number_format($custoTotal, 0, ',', '.') }} €</div>
    </div>
  </div>
</section>

{{-- ══════ CONTENT ══════ --}}
<div class="sr-page">
  <div class="sr-container">

    {{-- ── COSTS BREAKDOWN ── --}}
    <div class="sr-card sr-reveal">
      <div class="sr-card__head">
        <div class="sr-card__icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div>
          <h2 class="sr-card__title">Decomposição de Custos</h2>
          <p class="sr-card__sub">Valores estimados com base nos dados fornecidos</p>
        </div>
      </div>

      <div class="sr-costs">
        <div class="sr-cost-row">
          <div class="sr-cost-row__left">
            <div class="sr-cost-row__icon" style="background:#f0f4ff">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            </div>
            <div>
              <div class="sr-cost-row__name">Valor do Carro</div>
              <div class="sr-cost-row__hint">Preço de aquisição no país de origem</div>
            </div>
          </div>
          <div class="sr-cost-row__value" data-count="{{ (int)$valorCarro }}">{{ number_format($valorCarro, 0, ',', '.') }} €</div>
        </div>

        <div class="sr-cost-row">
          <div class="sr-cost-row__left">
            <div class="sr-cost-row__icon" style="background:#fff4e6">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e67700" stroke-width="2"><path d="M17 4.5C15.5 3.6 13.8 3 12 3 8 3 5 6 5 10s3 7 7 7c1.8 0 3.5-.6 5-1.5"/><line x1="3" y1="9" x2="15" y2="9"/><line x1="3" y1="11" x2="15" y2="11"/></svg>
            </div>
            <div>
              <div class="sr-cost-row__name">ISV</div>
              <div class="sr-cost-row__hint">Imposto sobre veículos — calculado automaticamente</div>
            </div>
          </div>
          <div class="sr-cost-row__value" data-count="{{ (int)$isv }}">{{ number_format($isv, 0, ',', '.') }} €</div>
        </div>

        <div class="sr-cost-row sr-cost-row--service">
          <div class="sr-cost-row__left">
            <div class="sr-cost-row__icon" style="background:#fdf2f8">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6e0707" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
              <div class="sr-cost-row__name">Serviços Izzycar</div>
              <div class="sr-cost-row__hint">Transporte · IPO · IMT · Matrícula · Gestão do processo</div>
            </div>
          </div>
          <div class="sr-cost-row__value" data-count="{{ (int)$servicos }}">{{ number_format($servicos, 0, ',', '.') }} €</div>
        </div>

        <div class="sr-total">
          <div class="sr-total__label">Total Chave na Mão</div>
          <div class="sr-total__value" data-count="{{ (int)$custoTotal }}">{{ number_format($custoTotal, 0, ',', '.') }} €</div>
        </div>

        <p class="sr-disclaimer">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          Valores estimados. Para uma cotação vinculativa e personalizada, contacte-nos.
        </p>
      </div>
    </div>

    {{-- ── ACTIONS ── --}}
    <div class="sr-actions sr-reveal">
      <a href="{{ route('frontend.cost-simulator') }}" class="sr-btn sr-btn--ghost">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
        Nova Simulação
      </a>
      <a href="{{ route('frontend.form-import') }}" class="sr-btn sr-btn--primary">
        Quero Importar
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>

    {{-- ── ISV TABLE ── --}}
    @if($isv > 0)
    <div class="sr-card sr-reveal">
      <div class="sr-card__head">
        <div class="sr-card__icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div>
          <h2 class="sr-card__title">Detalhe do Cálculo ISV</h2>
          <p class="sr-card__sub">Decomposição das componentes do Imposto sobre Veículos</p>
        </div>
      </div>
      <div class="sr-isv-table">
        {!! $tableIsv !!}
      </div>
    </div>
    @endif

  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  /* ── Counter animation ── */
  function animateCount(el) {
    const target = parseInt(el.dataset.count);
    if (!target) return;
    const dur = 1100;
    const start = performance.now();
    function tick(now) {
      const p = Math.min((now - start) / dur, 1);
      const e = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(e * target).toLocaleString('pt-PT') + ' €';
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }
  const cObs = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting && e.target.dataset.count) {
        animateCount(e.target);
        cObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('[data-count]').forEach(el => cObs.observe(el));

  /* ── Reveal ── */
  const rObs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('is-visible'); });
  }, { threshold: 0.08 });
  document.querySelectorAll('.sr-reveal').forEach(el => rObs.observe(el));
});
</script>

<style>
/* ════════════════════════════════════════
   RESULTADO DA SIMULAÇÃO
════════════════════════════════════════ */
:root {
  --sr-brand:  #6e0707;
  --sr-dark:   #111111;
  --sr-gray:   #6b7280;
  --sr-light:  #f9fafb;
  --sr-border: #e5e7eb;
  --sr-shadow: 0 2px 12px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.04);
}
@keyframes sr-fadeUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:none; } }

/* ── Hero ── */
.sr-hero {
  position:relative; background: var(--sr-dark);
  padding: 5rem 1.5rem 4rem; overflow:hidden;
}
.sr-hero__overlay {
  position:absolute; inset:0; pointer-events:none;
  background: radial-gradient(ellipse at 60% 0%, rgba(110,7,7,.2) 0%, transparent 65%);
}
.sr-hero__inner {
  position:relative; max-width:680px; margin:0 auto; text-align:center;
  animation: sr-fadeUp .7s ease-out both;
}
.sr-hero__badge {
  display:inline-flex; align-items:center; gap:.45rem;
  background:rgba(74,222,128,.12); border:1px solid rgba(74,222,128,.25);
  color:#4ade80; font-size:.78rem; font-weight:600;
  padding:.4rem 1rem; border-radius:100px; margin-bottom:1.5rem;
}
.sr-hero__title {
  font-size: clamp(1.8rem, 4vw, 2.8rem); font-weight:900;
  color:#fff; line-height:1.15; margin-bottom:.75rem; letter-spacing:-.02em;
}
.sr-hero__sub {
  font-size:1rem; color:rgba(255,255,255,.6); margin-bottom:2rem; line-height:1.6;
}
.sr-hero__total-wrap { display:inline-block; }
.sr-hero__total-label {
  font-size:.75rem; font-weight:600; color:rgba(255,255,255,.5);
  text-transform:uppercase; letter-spacing:.08em; margin-bottom:.35rem;
}
.sr-hero__total {
  font-size: clamp(2rem, 5vw, 3rem); font-weight:900; color:#fff;
  letter-spacing:-.02em;
}

/* ── Page ── */
.sr-page { background: var(--sr-light); }
.sr-container { max-width:820px; margin:0 auto; padding:3rem 1.5rem 4rem; }

/* ── Card ── */
.sr-card {
  background:#fff; border:1px solid var(--sr-border);
  border-radius:20px; padding:2.5rem;
  box-shadow: var(--sr-shadow); margin-bottom:1.5rem;
}
.sr-card__head {
  display:flex; align-items:flex-start; gap:1rem; margin-bottom:2rem;
  padding-bottom:1.25rem; border-bottom:1px solid var(--sr-border);
}
.sr-card__icon {
  width:46px; height:46px; border-radius:12px; flex-shrink:0;
  background:#fdf2f8; border:1px solid #fecdd3;
  color: var(--sr-brand);
  display:flex; align-items:center; justify-content:center;
}
.sr-card__title { font-size:1.15rem; font-weight:800; color:#111; margin:0 0 .2rem; }
.sr-card__sub   { font-size:.82rem; color: var(--sr-gray); margin:0; }

/* ── Costs ── */
.sr-costs { display:flex; flex-direction:column; gap:.9rem; }
.sr-cost-row {
  display:flex; align-items:center; justify-content:space-between; gap:1rem;
  padding:.85rem 1rem; border-radius:12px;
  background: var(--sr-light); border:1px solid transparent;
  transition:border-color .2s, box-shadow .2s;
}
.sr-cost-row:hover { border-color:var(--sr-border); box-shadow:var(--sr-shadow); }
.sr-cost-row--service { background:#fdf2f8; }
.sr-cost-row__left { display:flex; align-items:center; gap:.75rem; min-width:0; }
.sr-cost-row__icon {
  width:36px; height:36px; border-radius:10px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
}
.sr-cost-row__name  { font-size:.88rem; font-weight:600; color:#111; }
.sr-cost-row__hint  { font-size:.72rem; color: var(--sr-gray); margin-top:.1rem; }
.sr-cost-row__value { font-size:1rem; font-weight:800; color:#111; white-space:nowrap; }

.sr-total {
  display:flex; align-items:center; justify-content:space-between;
  padding:1.25rem 1.5rem; border-radius:12px; margin-top:.25rem;
  background: linear-gradient(135deg, var(--sr-brand) 0%, #9b1111 100%);
  box-shadow:0 4px 16px rgba(110,7,7,.3);
}
.sr-total__label { font-size:.82rem; font-weight:500; color:rgba(255,255,255,.75); }
.sr-total__value { font-size:1.6rem; font-weight:900; color:#fff; }

.sr-disclaimer {
  display:flex; align-items:center; gap:.5rem;
  font-size:.75rem; color: var(--sr-gray); margin:.75rem 0 0;
}
.sr-disclaimer svg { flex-shrink:0; }

/* ── Actions ── */
.sr-actions {
  display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; margin-bottom:1.5rem;
}
.sr-btn {
  display:inline-flex; align-items:center; gap:.55rem;
  padding:.85rem 1.75rem; border-radius:12px;
  font-size:.95rem; font-weight:700; text-decoration:none;
  transition:transform .15s, box-shadow .2s, background .2s;
}
.sr-btn--ghost {
  background:#fff; border:1.5px solid var(--sr-border); color:#111;
}
.sr-btn--ghost:hover {
  border-color:#aaa; color:#111; transform:translateY(-1px); box-shadow: var(--sr-shadow);
}
.sr-btn--primary {
  background: linear-gradient(135deg, var(--sr-brand), #9b1111);
  color:#fff; border:none;
  box-shadow:0 4px 16px rgba(110,7,7,.3);
}
.sr-btn--primary:hover {
  color:#fff; transform:translateY(-2px);
  box-shadow:0 8px 24px rgba(110,7,7,.4);
}
.sr-btn--primary svg { transition:transform .2s; }
.sr-btn--primary:hover svg { transform:translateX(3px); }

/* ── ISV Table ── */
.sr-isv-table { overflow-x:auto; margin-top:.5rem; }
.sr-isv-table table {
  width:100%; border-collapse:separate; border-spacing:0;
  font-size:.85rem;
}
.sr-isv-table table th {
  background: linear-gradient(135deg, var(--sr-brand), #9b1111);
  color:#fff; padding:.85rem 1rem; font-weight:700; text-align:left;
}
.sr-isv-table table th:first-child { border-radius:10px 0 0 0; }
.sr-isv-table table th:last-child  { border-radius:0 10px 0 0; }
.sr-isv-table table td {
  padding:.8rem 1rem; border-bottom:1px solid var(--sr-border); color:#374151;
}
.sr-isv-table table tr:last-child td { border-bottom:none; }
.sr-isv-table table tr:hover td { background:#f9fafb; }

/* ── Reveal ── */
.sr-reveal { opacity:0; transform:translateY(22px); transition:opacity .5s ease, transform .5s ease; }
.sr-reveal.is-visible { opacity:1; transform:none; }

@media(max-width:640px) {
  .sr-card { padding:1.75rem 1.25rem; }
  .sr-total { flex-direction:column; gap:.5rem; text-align:center; }
  .sr-cost-row { flex-direction:column; gap:.5rem; }
  .sr-actions { flex-direction:column; }
  .sr-btn { justify-content:center; }
}
</style>
@endpush
