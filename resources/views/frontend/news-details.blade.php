@extends('frontend.partials.layout')

@php
  $newsSeo = (object)[
    'title'            => $news->seo_title ?: $news->title . ' | Izzycar',
    'meta_description' => $news->seo_description ?: '',
    'meta_keywords'    => $news->seo_keywords ?: '',
    'canonical_url'    => url()->current(),
    'og_title'         => $news->seo_title ?: $news->title,
    'og_description'   => $news->seo_description ?: '',
    'og_image'         => $news->cover_image ? asset('storage/'.$news->cover_image) : '',
    'og_url'           => url()->current(),
    'og_type'          => 'article',
  ];
@endphp
@include('frontend.partials.seo', ['seo' => $newsSeo])

@push('head')
@php
  $articleTitle = $news->title ?? '';
  $articleDate  = !empty($news->published_at)
    ? \Carbon\Carbon::parse($news->published_at)->toIso8601String()
    : now()->toIso8601String();
  $articleImg   = !empty($news->cover_image)
    ? asset('storage/' . $news->cover_image)
    : asset('img/logo-simples.png');
@endphp
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Article",
    "headline": "{{ addslashes($articleTitle) }}",
    "description": "{{ addslashes(strip_tags($news->seo_description ?: ($news->subtitle ?? ''))) }}",
    "image": {"@@type": "ImageObject", "url": "{{ $articleImg }}", "width": 1200, "height": 630},
    "datePublished": "{{ $articleDate }}",
    "dateModified": "{{ !empty($news->updated_at) ? \Carbon\Carbon::parse($news->updated_at)->toIso8601String() : $articleDate }}",
    "author": {
        "@@type": "Organization",
        "name": "Izzycar",
        "url": "https://izzycar.pt"
    },
    "publisher": {
        "@@type": "Organization",
        "name": "Izzycar",
        "url": "https://izzycar.pt",
        "logo": {
            "@@type": "ImageObject",
            "url": "https://izzycar.pt/storage/settings/logo_redondo.png",
            "width": 192,
            "height": 192
        }
    },
    "mainEntityOfPage": {
        "@@type": "WebPage",
        "@@id": "{{ url()->current() }}"
    }
}
</script>

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {"@@type": "ListItem", "position": 1, "name": "Início", "item": "https://izzycar.pt"},
        {"@@type": "ListItem", "position": 2, "name": "Guias & Notícias", "item": "https://izzycar.pt/noticias"},
        {"@@type": "ListItem", "position": 3, "name": "{{ addslashes($articleTitle) }}", "item": "{{ url()->current() }}"}
    ]
}
</script>
@endpush

@section('content')

@php
  $readTime = $news->read_time;
@endphp

{{-- ══════ PROGRESS BAR ══════ --}}
<div class="nd-progress" id="ndProgress"></div>

{{-- ══════ HEADER ══════ --}}
<header class="nd-header">
  <div class="nd-header__inner">
    <nav class="nd-breadcrumb" aria-label="breadcrumb">
      <a href="{{ route('frontend.home') }}">Início</a>
      <span>/</span>
      <a href="{{ route('frontend.news') }}">Guias & Notícias</a>
      <span>/</span>
      <span>{{ \Illuminate\Support\Str::limit($news->title, 50) }}</span>
    </nav>
    <a href="{{ route('frontend.news') }}" class="nd-back">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
      Todas as notícias
    </a>

    <div class="nd-header__meta">
      @if(!empty($news->published_at))
      <span class="nd-meta-item">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        {{ \Carbon\Carbon::parse($news->published_at)->locale('pt')->isoFormat('D [de] MMMM [de] Y') }}
      </span>
      @endif
      <span class="nd-meta-item">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        {{ $readTime }} min de leitura
      </span>
    </div>

    <h1 class="nd-header__title">{{ $news->title }}</h1>

    @if(!empty($news->subtitle))
    <div class="nd-header__subtitle">{!! $news->subtitle !!}</div>
    @endif
  </div>
</header>

{{-- ══════ HERO IMAGE ══════ --}}
@if(!empty($news->cover_image))
<div class="nd-hero-img-wrap">
  <div class="nd-hero-img-inner">
    <img src="{{ asset('storage/' . $news->cover_image) }}"
         onerror="this.src='{{ asset('img/logo-simples.png') }}';"
         alt="{{ $news->title }}"
         class="nd-hero-img" loading="eager">
  </div>
</div>
@endif

{{-- ══════ BODY ══════ --}}
<div class="nd-layout">
  <div class="nd-layout__inner">

    {{-- Main content --}}
    <main class="nd-article" id="ndArticle">

      <div class="nd-article__body nd-prose">
        {!! $news->content !!}
      </div>

      {{-- Gallery --}}
      @if(!empty($news->gallery) && count($news->gallery) > 0)
      @php $gallery = $news->gallery; @endphp
      @if(count($gallery) > 0)
      <div class="nd-gallery">
        <h3 class="nd-gallery__title">Galeria</h3>
        <div class="nd-gallery__grid">
          @foreach($gallery as $gImg)
          <div class="nd-gallery__item">
            <img src="{{ asset('storage/' . $gImg) }}"
                 onerror="this.src='{{ asset('img/logo-simples.png') }}';"
                 alt="{{ $news->title }}" loading="lazy"
                 class="nd-gallery__img">
          </div>
          @endforeach
        </div>
      </div>
      @endif
      @endif

      {{-- Summary --}}
      @if(!empty($news->summary))
      <div class="nd-summary">
        <div class="nd-summary__icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="nd-summary__content nd-prose">
          {!! $news->summary !!}
        </div>
      </div>
      @endif

      {{-- Share --}}
      <div class="nd-share">
        <span class="nd-share__label">Partilhar:</span>
        <a href="https://wa.me/?text={{ urlencode($news->title . ' — ' . url()->current()) }}"
           target="_blank" class="nd-share__btn nd-share__btn--wa" aria-label="WhatsApp">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          WhatsApp
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
           target="_blank" class="nd-share__btn nd-share__btn--fb" aria-label="Facebook">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
          Facebook
        </a>
        <button onclick="navigator.clipboard.writeText('{{ url()->current() }}').then(()=>this.textContent='Copiado ✓')" class="nd-share__btn nd-share__btn--copy">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
          Copiar link
        </button>
      </div>

      {{-- Back --}}
      <div class="nd-back-wrap">
        <a href="{{ route('frontend.news') }}" class="nd-back-btn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Ver todos os artigos
        </a>
      </div>
    </main>

    {{-- Sidebar --}}
    <aside class="nd-sidebar">

      {{-- Simulador CTA --}}
      <div class="nd-sb-card nd-sb-card--dark">
        <div class="nd-sb-card__icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <h4 class="nd-sb-card__title">Simule os custos</h4>
        <p class="nd-sb-card__desc">Calcule ISV, transporte e todos os encargos da sua importação — grátis.</p>
        <a href="{{ route('frontend.cost-simulator') }}" class="nd-sb-card__btn">
          Usar o simulador
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </a>
      </div>

      {{-- Serviços --}}
      <div class="nd-sb-card">
        <h4 class="nd-sb-card__header">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          Serviços
        </h4>
        <nav class="nd-sb-nav">
          <a href="{{ route('frontend.import') }}" class="nd-sb-nav__item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            Importação automóvel
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="nd-sb-nav__arrow"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
          <a href="{{ route('frontend.legalization') }}" class="nd-sb-nav__item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Legalização automóvel
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="nd-sb-nav__arrow"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
          <a href="{{ route('frontend.form-import') }}" class="nd-sb-nav__item nd-sb-nav__item--cta">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Pedir cotação grátis
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="nd-sb-nav__arrow"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </nav>
      </div>

      {{-- Artigos recentes --}}
      @if(isset($recentNews) && $recentNews->isNotEmpty())
      <div class="nd-sb-card">
        <h4 class="nd-sb-card__header">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2"/></svg>
          Artigos Recentes
        </h4>
        <div class="nd-sb-recent">
          @foreach($recentNews as $recent)
          <a href="{{ route('frontend.news-details', $recent->slug) }}" class="nd-sb-recent__item">
            @if(!empty($recent->cover_image))
            <div class="nd-sb-recent__img-wrap">
              <img src="{{ asset('storage/' . $recent->cover_image) }}"
                   onerror="this.src='{{ asset('img/logo-simples.png') }}';"
                   alt="{{ $recent->title }}" loading="lazy"
                   class="nd-sb-recent__img">
            </div>
            @endif
            <div class="nd-sb-recent__body">
              <p class="nd-sb-recent__title">{{ \Illuminate\Support\Str::limit($recent->title ?? '', 65) }}</p>
              @if(!empty($recent->published_at))
              <span class="nd-sb-recent__date">{{ \Carbon\Carbon::parse($recent->published_at)->format('d M Y') }}</span>
              @endif
            </div>
          </a>
          @endforeach
        </div>
      </div>
      @endif

    </aside>

  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  /* Reading progress bar */
  const bar     = document.getElementById('ndProgress');
  const article = document.getElementById('ndArticle');
  if (bar && article) {
    window.addEventListener('scroll', function () {
      const rect = article.getBoundingClientRect();
      const total = article.offsetHeight - window.innerHeight;
      const scrolled = -rect.top;
      const pct = Math.min(Math.max(scrolled / total, 0), 1) * 100;
      bar.style.width = pct + '%';
    }, { passive: true });
  }

  /* Gallery lightbox — click to expand */
  document.querySelectorAll('.nd-gallery__img').forEach(img => {
    img.addEventListener('click', function () {
      const ov = document.createElement('div');
      ov.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.92);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:zoom-out;';
      const i = document.createElement('img');
      i.src = this.src;
      i.style.cssText = 'max-width:92vw;max-height:90vh;border-radius:12px;';
      ov.appendChild(i);
      ov.addEventListener('click', () => ov.remove());
      document.body.appendChild(ov);
    });
  });
});
</script>

<style>
/* ════════════════════════════════════════
   NEWS DETAIL
════════════════════════════════════════ */
:root {
  --nd-brand:  #6e0707;
  --nd-dark:   #111111;
  --nd-gray:   #6b7280;
  --nd-light:  #f9fafb;
  --nd-border: #e5e7eb;
}

/* ── Progress bar ── */
.nd-progress {
  position:fixed; top:0; left:0; height:3px; width:0; z-index:9999;
  background: linear-gradient(90deg, var(--nd-brand), #e53e3e);
  transition:width .1s linear;
  pointer-events:none;
}

/* ── Header ── */
.nd-header {
  background: var(--nd-dark); padding: 6rem 1.5rem 3.5rem;
  position:relative; overflow:hidden;
}
.nd-header::before {
  content:''; position:absolute; inset:0; pointer-events:none;
  background: radial-gradient(ellipse at 30% 0%, rgba(110,7,7,.25) 0%, transparent 65%);
}
.nd-header__inner { max-width:820px; margin:0 auto; position:relative; }

.nd-breadcrumb {
  display:flex; align-items:center; gap:.45rem; flex-wrap:wrap;
  font-size:.78rem; color:rgba(255,255,255,.4); margin-bottom:1.25rem;
}
.nd-breadcrumb a { color:rgba(255,255,255,.4); text-decoration:none; transition:color .2s; }
.nd-breadcrumb a:hover { color:#fff; }
.nd-breadcrumb span { color:rgba(255,255,255,.25); }
.nd-breadcrumb span:last-child { color:rgba(255,255,255,.6); }

.nd-back {
  display:inline-flex; align-items:center; gap:.45rem;
  color:rgba(255,255,255,.55); font-size:.78rem; font-weight:600;
  text-decoration:none; margin-bottom:1.75rem;
  transition:color .2s;
}
.nd-back:hover { color:#fff; }
.nd-back svg { transition:transform .2s; }
.nd-back:hover svg { transform:translateX(-3px); }

.nd-header__meta { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem; }
.nd-meta-item {
  display:inline-flex; align-items:center; gap:.4rem;
  font-size:.75rem; color:rgba(255,255,255,.45); font-weight:500;
}

.nd-header__title {
  font-size: clamp(1.6rem, 4vw, 2.6rem); font-weight:900;
  color:#fff; line-height:1.2; margin:0 0 1.25rem; letter-spacing:-.02em;
}
.nd-header__subtitle {
  font-size:1.05rem; color:rgba(255,255,255,.75) !important; line-height:1.7;
  border-left:3px solid var(--nd-brand); padding-left:1rem;
}
.nd-header__subtitle p,
.nd-header__subtitle span,
.nd-header__subtitle * { margin:0; color:rgba(255,255,255,.75) !important; }

/* ── Hero image ── */
.nd-hero-img-wrap {
  max-width:1000px; margin:0 auto;
  padding: 0 1.5rem;
  margin-top:2rem;
}
.nd-hero-img-inner {
  border-radius:16px; overflow:hidden;
  box-shadow:0 16px 48px rgba(0,0,0,.2);
  line-height:0;
}
.nd-hero-img {
  width:100%; height:auto;
  display:block;
}

/* ── Layout ── */
.nd-layout { background: var(--nd-light); padding: 3rem 1.5rem 4rem; }
.nd-layout__inner {
  max-width:1120px; margin:0 auto;
  display:grid; grid-template-columns:1fr; gap:2.5rem;
}
@media(min-width:1024px) {
  .nd-layout__inner { grid-template-columns:1fr 320px; align-items:start; }
}

/* ── Article ── */
.nd-article {
  background:#fff; border-radius:20px; padding:2.5rem;
  border:1px solid var(--nd-border);
}

/* ── Prose typography ── */
.nd-prose { font-size:1.05rem; line-height:1.85; color:#2d3748; }
.nd-prose h2 {
  font-size:1.45rem; font-weight:800; color:#111; margin:2.25rem 0 .85rem;
  padding-bottom:.5rem; border-bottom:2px solid var(--nd-border);
}
.nd-prose h3 {
  font-size:1.15rem; font-weight:700; color:#111; margin:1.75rem 0 .6rem;
}
.nd-prose p { margin:0 0 1.25rem; }
.nd-prose ul, .nd-prose ol {
  margin:0 0 1.25rem 1.25rem; display:flex; flex-direction:column; gap:.4rem;
}
.nd-prose li { line-height:1.6; }
.nd-prose ul li::marker { color: var(--nd-brand); }
.nd-prose strong { font-weight:700; }
.nd-prose a { color: var(--nd-brand); font-weight:600; text-decoration:underline; text-underline-offset:3px; }
.nd-prose a:hover { color:#9b1111; }
.nd-prose blockquote {
  border-left:4px solid var(--nd-brand); padding:1rem 1.5rem;
  background:#fdf2f8; border-radius:0 12px 12px 0;
  margin:1.5rem 0; font-style:italic; color:#4a1010;
}
.nd-prose table {
  width:100%; border-collapse:separate; border-spacing:0;
  margin:1.5rem 0; font-size:.9rem; border-radius:12px; overflow:hidden;
  border:1px solid var(--nd-border);
}
.nd-prose table th {
  background: var(--nd-brand); color:#fff;
  padding:.75rem 1rem; font-weight:700; text-align:left;
}
.nd-prose table td { padding:.7rem 1rem; border-bottom:1px solid var(--nd-border); }
.nd-prose table tr:last-child td { border-bottom:none; }
.nd-prose table tr:hover td { background:#f9fafb; }
.nd-prose hr { border:none; border-top:1px solid var(--nd-border); margin:2rem 0; }

/* ── Gallery ── */
.nd-gallery { margin:2.5rem 0; }
.nd-gallery__title { font-size:1rem; font-weight:700; color:#111; margin-bottom:1rem; }
.nd-gallery__grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:.75rem; }
.nd-gallery__item { border-radius:10px; overflow:hidden; aspect-ratio:4/3; }
.nd-gallery__img { width:100%; height:100%; object-fit:cover; cursor:zoom-in; transition:transform .3s; }
.nd-gallery__img:hover { transform:scale(1.04); }

/* ── Summary ── */
.nd-summary {
  display:flex; gap:1rem; align-items:flex-start;
  background:linear-gradient(135deg,#fdf2f8,#fff);
  border:1px solid #fecdd3; border-radius:16px;
  padding:1.5rem; margin:2.5rem 0;
}
.nd-summary__icon {
  width:38px; height:38px; border-radius:10px; flex-shrink:0;
  background: var(--nd-brand); color:#fff;
  display:flex; align-items:center; justify-content:center;
}
.nd-summary__content { font-size:.92rem; }
.nd-summary__content p { margin:0; }

/* ── Share ── */
.nd-share {
  display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;
  padding:1.5rem 0; border-top:1px solid var(--nd-border);
  margin-top:2rem;
}
.nd-share__label { font-size:.82rem; font-weight:700; color: var(--nd-gray); }
.nd-share__btn {
  display:inline-flex; align-items:center; gap:.45rem;
  padding:.45rem 1rem; border-radius:9px;
  font-size:.78rem; font-weight:600; cursor:pointer;
  text-decoration:none; border:none; transition:opacity .2s, transform .15s;
}
.nd-share__btn:hover { opacity:.85; transform:translateY(-1px); }
.nd-share__btn--wa   { background:#25D366; color:#fff; }
.nd-share__btn--fb   { background:#1877F2; color:#fff; }
.nd-share__btn--copy { background:var(--nd-light); color:#374151; border:1px solid var(--nd-border); }

/* ── Back ── */
.nd-back-wrap { margin-top:1.5rem; }
.nd-back-btn {
  display:inline-flex; align-items:center; gap:.5rem;
  padding:.7rem 1.4rem; border-radius:10px;
  border:1.5px solid var(--nd-border); color:#374151;
  font-size:.85rem; font-weight:600; text-decoration:none;
  transition:border-color .2s, color .2s;
}
.nd-back-btn:hover { border-color: var(--nd-brand); color: var(--nd-brand); }
.nd-back-btn svg { transition:transform .2s; }
.nd-back-btn:hover svg { transform:translateX(-3px); }

/* ── Sidebar ── */
.nd-sidebar { display:flex; flex-direction:column; gap:1.5rem; }
@media(min-width:1024px) {
  .nd-sidebar { position:sticky; top:100px; }
}

.nd-sb-card {
  background:#fff; border:1px solid var(--nd-border);
  border-radius:16px; padding:1.5rem;
}
.nd-sb-card--dark {
  background: var(--nd-dark); border-color: var(--nd-dark);
}
.nd-sb-card__icon {
  width:46px; height:46px; border-radius:12px;
  background:rgba(255,255,255,.1); color:#fff;
  display:flex; align-items:center; justify-content:center; margin-bottom:1rem;
}
.nd-sb-card__title { font-size:1rem; font-weight:800; color:#fff; margin:0 0 .35rem; }
.nd-sb-card__desc  { font-size:.8rem; color:rgba(255,255,255,.55); margin:0 0 1.25rem; line-height:1.5; }
.nd-sb-card__btn {
  display:flex; align-items:center; justify-content:center; gap:.5rem;
  padding:.75rem; border-radius:10px;
  background: var(--nd-brand); color:#fff; font-size:.85rem; font-weight:700;
  text-decoration:none; transition:background .2s;
}
.nd-sb-card__btn:hover { background:#9b1111; color:#fff; }
.nd-sb-card__btn svg { transition:transform .2s; }
.nd-sb-card__btn:hover svg { transform:translateX(3px); }

.nd-sb-card__header {
  display:flex; align-items:center; gap:.6rem;
  font-size:.82rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.06em; color: var(--nd-gray);
  margin:0 0 1rem; padding-bottom:.75rem;
  border-bottom:1px solid var(--nd-border);
}

.nd-sb-nav { display:flex; flex-direction:column; gap:.5rem; }
.nd-sb-nav__item {
  display:flex; align-items:center; gap:.65rem;
  padding:.7rem .9rem; border-radius:10px;
  background: var(--nd-light); border:1px solid transparent;
  font-size:.83rem; font-weight:600; color:#374151; text-decoration:none;
  transition:background .2s, border-color .2s, color .2s;
}
.nd-sb-nav__item:hover { background:#fff; border-color: var(--nd-border); color:#111; }
.nd-sb-nav__item--cta {
  background: var(--nd-brand); color:#fff; border-color: var(--nd-brand);
}
.nd-sb-nav__item--cta:hover { background:#9b1111; border-color:#9b1111; color:#fff; }
.nd-sb-nav__arrow { margin-left:auto; flex-shrink:0; }

.nd-sb-recent { display:flex; flex-direction:column; gap:.75rem; }
.nd-sb-recent__item {
  display:flex; gap:.75rem; align-items:flex-start;
  text-decoration:none; padding:.6rem; border-radius:10px;
  transition:background .2s; border:1px solid transparent;
}
.nd-sb-recent__item:hover { background: var(--nd-light); border-color: var(--nd-border); }
.nd-sb-recent__img-wrap {
  width:60px; height:50px; border-radius:8px; overflow:hidden; flex-shrink:0;
}
.nd-sb-recent__img { width:100%; height:100%; object-fit:cover; }
.nd-sb-recent__body { flex:1; min-width:0; }
.nd-sb-recent__title {
  font-size:.8rem; font-weight:600; color:#111; margin:0 0 .25rem;
  line-height:1.4; display:-webkit-box; -webkit-line-clamp:2;
  -webkit-box-orient:vertical; overflow:hidden;
}
.nd-sb-recent__date { font-size:.72rem; color: var(--nd-gray); }

@media(max-width:640px) {
  .nd-article { padding:1.5rem; }
  .nd-header { padding:2.5rem 1.25rem 2.5rem; }
  .nd-hero-img-wrap { padding:0 1rem; }
  .nd-hero-img-inner { padding-top:0; }
  .nd-layout { padding:1.5rem 1rem 3rem; }
}
</style>
@endpush
