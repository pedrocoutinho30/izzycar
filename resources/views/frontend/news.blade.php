@extends('frontend.partials.layout')

@include('frontend.partials.seo', ['seo' => $seo])

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Blog",
    "name": "Blog Izzycar — Importação Automóvel",
    "description": "Guias, análises e notícias sobre importação automóvel, ISV e mercado de carros em Portugal.",
    "url": "https://izzycar.pt/noticias",
    "publisher": {
        "@@type": "AutoDealer",
        "name": "Izzycar",
        "url": "https://izzycar.pt"
    }
}
</script>
@endpush

@section('content')

{{-- ══════ HERO ══════ --}}
<section class="nb-hero">
  <div class="nb-hero__bg"></div>
  <div class="container">
  <div class="nb-hero__inner">
    <nav class="nb-hero__breadcrumb" aria-label="breadcrumb">
      <a href="{{ route('frontend.home') }}">Início</a>
      <span>/</span>
      <span>Guias & Notícias</span>
    </nav>
    <div class="nb-hero__badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
      Guias & Notícias
    </div>
    <h1 class="nb-hero__title">Tudo sobre <span class="nb-hero__accent">o mercado automóvel</span></h1>
    <p class="nb-hero__sub">Guias práticos, análises de ISV e as últimas novidades do mercado automóvel europeu.</p>
  </div>
  </div>
</section>

{{-- ══════ ARTICLES ══════ --}}
<div class="nb-page">
  <div class="nb-container">

    @if($news->isEmpty())
      <div class="nb-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2"/></svg>
        <p>Ainda não há artigos publicados.</p>
      </div>
    @else

    @php $featured = $news->first(); $rest = $news->skip(1); @endphp

    {{-- Featured article --}}
    <a href="{{ route('frontend.news-details', $featured->slug) }}" class="nb-featured nb-reveal">
      <div class="nb-featured__img-wrap">
        @if(!empty($featured->cover_image))
          <img src="{{ asset('storage/' . $featured->cover_image) }}"
               onerror="this.src='{{ asset('img/logo-simples.png') }}';"
               alt="{{ $featured->title }}"
               class="nb-featured__img" loading="lazy">
        @else
          <div class="nb-featured__img-placeholder"></div>
        @endif
        <div class="nb-featured__overlay"></div>
        <div class="nb-featured__label">Destaque</div>
      </div>
      <div class="nb-featured__body">
        <div class="nb-featured__meta">
          @if(!empty($featured->published_at))
          <span class="nb-date">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            {{ $featured->published_at->format('d M Y') }}
          </span>
          @endif
          <span class="nb-read-time">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            {{ $featured->read_time }} min de leitura
          </span>
        </div>
        <h2 class="nb-featured__title">{{ $featured->title }}</h2>
        <p class="nb-featured__excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($featured->subtitle ?? ''), 180) }}</p>
        <div class="nb-featured__cta">
          Ler artigo completo
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </div>
      </div>
    </a>

    {{-- Grid of remaining articles --}}
    @if($rest->isNotEmpty())
    <div class="nb-grid">
      @foreach($rest as $i => $article)
      <a href="{{ route('frontend.news-details', $article->slug) }}"
         class="nb-card nb-reveal"
         style="--delay: {{ ($i % 3) * 0.1 }}s">
        <div class="nb-card__img-wrap">
          @if(!empty($article->cover_image))
            <img src="{{ asset('storage/' . $article->cover_image) }}"
                 onerror="this.src='{{ asset('img/logo-simples.png') }}';"
                 alt="{{ $article->title }}"
                 class="nb-card__img" loading="lazy">
          @else
            <div class="nb-card__img-placeholder">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2"/></svg>
            </div>
          @endif
          <div class="nb-card__overlay"></div>
        </div>
        <div class="nb-card__body">
          <div class="nb-card__meta">
            @if(!empty($article->published_at))
            <span class="nb-date">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              {{ $article->published_at->format('d M Y') }}
            </span>
            @endif
            <span class="nb-read-time">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              {{ $article->read_time }} min
            </span>
          </div>
          <h3 class="nb-card__title">{{ $article->title }}</h3>
          <p class="nb-card__excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($article->subtitle ?? ''), 110) }}</p>
          <div class="nb-card__link">
            Ler mais
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </div>
        </div>
      </a>
      @endforeach
    </div>
    @endif

    @endif

    {{-- CTA Simulator --}}
    <div class="nb-cta nb-reveal">
      <div class="nb-cta__content">
        <div class="nb-cta__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div>
          <h3 class="nb-cta__title">Calcule os custos da sua importação</h3>
          <p class="nb-cta__sub">Simule ISV, transporte e todos os encargos em menos de 2 minutos — grátis e sem compromisso.</p>
        </div>
      </div>
      <a href="{{ route('frontend.cost-simulator') }}" class="nb-cta__btn">
        Usar o simulador
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('is-visible'); });
  }, { threshold: 0.07, rootMargin: '0px 0px -40px 0px' });
  document.querySelectorAll('.nb-reveal').forEach(el => obs.observe(el));
});
</script>

<style>
/* ════════════════════════════════════════
   NEWS BLOG — LISTAGEM
════════════════════════════════════════ */
:root {
  --nb-brand:  #6e0707;
  --nb-dark:   #111111;
  --nb-gray:   #6b7280;
  --nb-light:  #f9fafb;
  --nb-border: #e5e7eb;
  --nb-shadow: 0 4px 24px rgba(0,0,0,.08);
}
@keyframes nb-fadeUp { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:none; } }

/* ── Hero ── */
.nb-hero {
  position:relative; background: var(--nb-dark);
  padding: 5rem 0 4rem; overflow:hidden;
}
.nb-hero__bg {
  position:absolute; inset:0; pointer-events:none;
  background: radial-gradient(ellipse at 30% 0%, rgba(110,7,7,.25) 0%, transparent 65%);
}
.nb-hero__inner {
  position:relative; max-width:700px;
  animation: nb-fadeUp .7s ease-out both;
}
.nb-hero__breadcrumb {
  display:flex; align-items:center; gap:.5rem;
  font-size:.82rem; color:rgba(255,255,255,.45); margin-bottom:1rem;
}
.nb-hero__breadcrumb a { color:rgba(255,255,255,.45); text-decoration:none; }
.nb-hero__breadcrumb a:hover { color:#fff; }
.nb-hero__breadcrumb span { color:rgba(255,255,255,.25); }
.nb-hero__breadcrumb span:last-child { color:rgba(255,255,255,.7); }
.nb-hero__badge {
  display:inline-flex; align-items:center; gap:.45rem;
  background:rgba(110,7,7,.2); border:1px solid rgba(110,7,7,.35);
  color:rgba(255,255,255,.9); font-size:.78rem; font-weight:600;
  padding:.4rem 1rem; border-radius:100px; margin-bottom:1.5rem;
}
.nb-hero__title {
  font-size: clamp(1.6rem, 4vw, 2.8rem); font-weight:900;
  color:#fff; line-height:1.15; margin-bottom:.85rem; letter-spacing:-.02em;
  white-space: nowrap;
}
@media(max-width:640px) { .nb-hero__title { white-space: normal; font-size: clamp(1.4rem, 7vw, 2rem); } }
.nb-hero__accent { color: var(--nb-brand); }
.nb-hero__sub {
  font-size:1rem; color:rgba(255,255,255,.6); line-height:1.7; max-width:820px;
}

/* ── Page ── */
.nb-page { background: var(--nb-light); }
.nb-container { max-width:1160px; margin:0 auto; padding:3.5rem 1.5rem 4rem; }

.nb-empty { text-align:center; padding:4rem; color: var(--nb-gray); }
.nb-empty svg { display:block; margin:0 auto 1rem; }

/* ── Featured ── */
.nb-featured {
  display:grid; grid-template-columns:1fr;
  background:#fff; border-radius:20px; overflow:hidden;
  border:1px solid var(--nb-border); text-decoration:none;
  margin-bottom:2rem; transition:box-shadow .3s, transform .3s;
}
@media(min-width:860px) {
  .nb-featured { grid-template-columns:1fr 1fr; }
}
.nb-featured:hover { box-shadow: 0 16px 48px rgba(0,0,0,.12); transform:translateY(-3px); }

.nb-featured__img-wrap {
  position:relative; overflow:hidden; min-height:300px;
  background: var(--nb-dark);
}
.nb-featured__img {
  position:absolute; inset:0;
  width:100%; height:100%; object-fit:cover; display:block;
  transition:transform .6s ease;
}
.nb-featured:hover .nb-featured__img { transform:scale(1.05); }
.nb-featured__img-placeholder { width:100%; height:100%; min-height:300px; background:linear-gradient(135deg,#1a1a1a,#2a2a2a); }
.nb-featured__overlay {
  position:absolute; inset:0;
  background:linear-gradient(to right, rgba(0,0,0,.15) 0%, transparent 60%);
}
.nb-featured__label {
  position:absolute; top:1.25rem; left:1.25rem;
  background: var(--nb-brand); color:#fff;
  font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em;
  padding:.3rem .8rem; border-radius:100px;
}
.nb-featured__body {
  padding:2.5rem; display:flex; flex-direction:column; justify-content:center;
}
.nb-featured__meta { display:flex; align-items:center; gap:1rem; margin-bottom:1rem; flex-wrap:wrap; }
.nb-featured__title {
  font-size: clamp(1.3rem,2.5vw,1.8rem); font-weight:800; color:#111;
  line-height:1.25; margin-bottom:.85rem;
  transition:color .2s;
}
.nb-featured:hover .nb-featured__title { color: var(--nb-brand); }
.nb-featured__excerpt { font-size:.9rem; color: var(--nb-gray); line-height:1.7; margin-bottom:1.5rem; flex:1; }
.nb-featured__cta {
  display:inline-flex; align-items:center; gap:.5rem;
  color: var(--nb-brand); font-weight:700; font-size:.88rem;
}
.nb-featured__cta svg { transition:transform .2s; }
.nb-featured:hover .nb-featured__cta svg { transform:translateX(4px); }

/* ── Grid ── */
.nb-grid {
  display:grid; gap:1.5rem;
  grid-template-columns:1fr;
}
@media(min-width:640px)  { .nb-grid { grid-template-columns:1fr 1fr; } }
@media(min-width:1024px) { .nb-grid { grid-template-columns:1fr 1fr 1fr; } }

/* ── Card ── */
.nb-card {
  background:#fff; border-radius:16px; overflow:hidden;
  border:1px solid var(--nb-border); text-decoration:none;
  display:flex; flex-direction:column;
  transition:box-shadow .3s, transform .3s;
  animation-delay: var(--delay, 0s);
}
.nb-card:hover { box-shadow: var(--nb-shadow); transform:translateY(-4px); }

.nb-card__img-wrap {
  position:relative; overflow:hidden; height:200px;
  background: var(--nb-dark);
}
.nb-card__img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
.nb-card:hover .nb-card__img { transform:scale(1.07); }
.nb-card__img-placeholder {
  width:100%; height:100%;
  background:linear-gradient(135deg,#f3f4f6,#e5e7eb);
  display:flex; align-items:center; justify-content:center;
}
.nb-card__overlay {
  position:absolute; inset:0;
  background:linear-gradient(to top, rgba(0,0,0,.15) 0%, transparent 50%);
}
.nb-card__body { padding:1.5rem; flex:1; display:flex; flex-direction:column; }
.nb-card__meta { display:flex; align-items:center; gap:.75rem; margin-bottom:.75rem; flex-wrap:wrap; }
.nb-card__title {
  font-size:1rem; font-weight:800; color:#111; line-height:1.35;
  margin-bottom:.6rem; transition:color .2s;
  display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;
}
.nb-card:hover .nb-card__title { color: var(--nb-brand); }
.nb-card__excerpt {
  font-size:.82rem; color: var(--nb-gray); line-height:1.6; flex:1; margin-bottom:1rem;
  display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;
}
.nb-card__link {
  display:inline-flex; align-items:center; gap:.4rem;
  color: var(--nb-brand); font-weight:700; font-size:.8rem; margin-top:auto;
}
.nb-card__link svg { transition:transform .2s; }
.nb-card:hover .nb-card__link svg { transform:translateX(3px); }

/* ── Shared date/time ── */
.nb-date {
  display:inline-flex; align-items:center; gap:.35rem;
  font-size:.75rem; font-weight:500; color: var(--nb-gray);
}
.nb-read-time {
  display:inline-flex; align-items:center; gap:.3rem;
  font-size:.72rem; font-weight:600; color: var(--nb-brand);
  background:rgba(110,7,7,.07); border-radius:100px;
  padding:.2rem .6rem;
}

/* ── CTA ── */
.nb-cta {
  display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1.5rem;
  background: var(--nb-dark); border-radius:20px;
  padding:2rem 2.5rem; margin-top:3rem;
}
.nb-cta__content { display:flex; align-items:flex-start; gap:1rem; }
.nb-cta__icon {
  width:52px; height:52px; border-radius:14px; flex-shrink:0;
  background:rgba(255,255,255,.08); color:#fff;
  display:flex; align-items:center; justify-content:center;
}
.nb-cta__title { font-size:1.1rem; font-weight:800; color:#fff; margin:0 0 .3rem; }
.nb-cta__sub   { font-size:.83rem; color:rgba(255,255,255,.55); margin:0; }
.nb-cta__btn {
  display:inline-flex; align-items:center; gap:.55rem; flex-shrink:0;
  padding:.85rem 1.75rem; border-radius:12px;
  background: var(--nb-brand); color:#fff; font-weight:700; font-size:.9rem;
  text-decoration:none; transition:background .2s, transform .15s;
  white-space:nowrap;
}
.nb-cta__btn:hover { background:#9b1111; transform:translateY(-1px); color:#fff; }
.nb-cta__btn svg { transition:transform .2s; }
.nb-cta__btn:hover svg { transform:translateX(3px); }

/* ── Reveal ── */
.nb-reveal { opacity:0; transform:translateY(24px); transition:opacity .55s ease, transform .55s ease; transition-delay: var(--delay,0s); }
.nb-reveal.is-visible { opacity:1; transform:none; }

@media(max-width:859px) {
  .nb-featured__img-wrap {
    min-height:0;
    padding-top:56.25%; /* 16:9 */
  }
}
@media(max-width:640px) {
  .nb-featured__body { padding:1.5rem; }
  .nb-cta { flex-direction:column; padding:1.5rem; }
  .nb-cta__btn { width:100%; justify-content:center; }
}
</style>
@endpush
