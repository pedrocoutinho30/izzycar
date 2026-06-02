@extends('frontend.partials.layout')

@section('title', 'Pedido de Proposta de Importação Automóvel | Izzycar')
@section('meta_description', 'Peça já a sua proposta gratuita de importação automóvel. A Izzycar trata de tudo: pesquisa, negociação, transporte, ISV e matrícula. Resposta em 24h.')

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ContactPage",
    "name": "Pedido de Proposta de Importação Automóvel",
    "description": "Formulário para pedido de proposta gratuita de importação automóvel da Alemanha e Europa para Portugal.",
    "url": "https://izzycar.pt/formulario-importacao",
    "provider": {
        "@@type": "AutoDealer",
        "name": "Izzycar",
        "url": "https://izzycar.pt"
    },
    "breadcrumb": {
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {"@@type": "ListItem", "position": 1, "name": "Início", "item": "https://izzycar.pt"},
            {"@@type": "ListItem", "position": 2, "name": "Pedido de Proposta", "item": "https://izzycar.pt/formulario-importacao"}
        ]
    }
}
</script>
<link rel="canonical" href="https://izzycar.pt/formulario-importacao">
@endpush

@section('content')

{{-- ══════ HERO ══════ --}}
<section class="if-hero">
  <div class="if-hero__overlay"></div>
  <div class="if-hero__inner">
    <div class="if-hero__badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      Importação Automóvel
    </div>
    <h1 class="if-hero__title">Peça a sua<br>Proposta de Importação</h1>
    <p class="if-hero__sub">Preencha o formulário e entraremos em contacto em menos de 24 horas com uma proposta personalizada, sem compromisso.</p>
    <div class="if-hero__pills">
      <span class="if-pill"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Resposta em 24h</span>
      <span class="if-pill"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Sem compromisso</span>
      <span class="if-pill"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Processo chave na mão</span>
    </div>
  </div>
</section>

{{-- ══════ FORM ══════ --}}
<div class="if-page">
  <div class="if-container">

    <div id="ifSuccess" class="if-success" style="display:none">
      <div class="if-success__icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <div>
        <div class="if-success__title">Pedido enviado com sucesso!</div>
        <p class="if-success__text">Entraremos em contacto em breve com a sua proposta personalizada.</p>
      </div>
    </div>

    <div id="ifError" class="if-error-box" style="display:none">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span>Ocorreu um erro. Por favor tente novamente.</span>
    </div>

    <form id="importForm">
      @csrf

      {{-- ── STEP 1: Dados pessoais ── --}}
      <div class="if-card if-reveal">
        <div class="if-card__head">
          <div class="if-card__step">1</div>
          <div>
            <h2 class="if-card__title">Informações Pessoais</h2>
            <p class="if-card__sub">Como podemos contactá-lo</p>
          </div>
        </div>

        <div class="if-grid">
          <div class="if-field">
            <label class="if-label" for="name">Nome completo <span class="if-req">*</span></label>
            <input type="text" name="name" id="name" class="if-input" placeholder="João Silva" required>
          </div>
          <div class="if-field">
            <label class="if-label" for="phone">Telemóvel <span class="if-req">*</span></label>
            <input type="text" name="phone" id="phone" class="if-input" placeholder="+351 912 345 678" required>
          </div>
          <div class="if-field">
            <label class="if-label" for="email">E-mail <span class="if-req">*</span></label>
            <input type="email" name="email" id="email" class="if-input" placeholder="joao@exemplo.com" required>
          </div>
          <div class="if-field">
            <label class="if-label" for="source">Como conheceu a Izzycar?</label>
            <select name="source" id="source" class="if-select">
              <option value="">Escolha uma opção</option>
              <option value="Google">Google</option>
              <option value="Facebook">Facebook</option>
              <option value="Instagram">Instagram</option>
              <option value="Amigo">Amigos/Família</option>
              <option value="Olx">OLX / Standvirtual</option>
              <option value="Outro">Outros</option>
            </select>
          </div>
          <div class="if-field if-field--full">
            <label class="if-label" for="message">Mensagem adicional</label>
            <textarea name="message" id="message" class="if-textarea" rows="3" placeholder="Informações adicionais que considere relevantes..."></textarea>
          </div>
        </div>
      </div>

      {{-- ── STEP 2: Preferências ── --}}
      <div class="if-card if-reveal">
        <div class="if-card__head">
          <div class="if-card__step">2</div>
          <div>
            <h2 class="if-card__title">Preferências de Compra</h2>
            <p class="if-card__sub">Ajuda-nos a perceber o seu perfil</p>
          </div>
        </div>

        <div class="if-grid if-grid--2">
          <div class="if-field">
            <label class="if-label" for="payment_type">Tipo de Pagamento <span class="if-req">*</span></label>
            <select name="payment_type" id="payment_type" class="if-select" required>
              <option value="">Escolha uma opção</option>
              <option value="pronto_pagamento">Pronto pagamento</option>
              <option value="financiamento">Financiamento</option>
            </select>
          </div>
          <div class="if-field">
            <label class="if-label" for="estimated_purchase_date">Prazo estimado de compra <span class="if-req">*</span></label>
            <select name="estimated_purchase_date" id="estimated_purchase_date" class="if-select" required>
              <option value="">Escolha uma opção</option>
              <option value="imediato">Imediato (até 30 dias)</option>
              <option value="1_3_meses">1 a 3 meses</option>
              <option value="3_6_meses">3 a 6 meses</option>
              <option value="pesquisar">Apenas a pesquisar</option>
            </select>
          </div>
        </div>
      </div>

      {{-- ── STEP 3: Veículo ── --}}
      <div class="if-card if-reveal">
        <div class="if-card__head">
          <div class="if-card__step">3</div>
          <div>
            <h2 class="if-card__title">Detalhes do Veículo</h2>
            <p class="if-card__sub">Conte-nos o que procura</p>
          </div>
        </div>

        <div class="if-grid if-grid--1 mb-if">
          <div class="if-field">
            <label class="if-label" for="ad_option">
              Tem algum anúncio identificado? <span class="if-req">*</span>
              <span class="if-hint">Forneça o máximo de informação para uma proposta mais precisa.</span>
            </label>
            <select name="ad_option" id="ad_option" class="if-select" required>
              <option value="">Escolha uma opção</option>
              <option value="sim">Sim, tenho um anúncio em mente</option>
              <option value="nao_sei">Não, mas sei o carro que procuro</option>
              <option value="nao_nao">Não, nem sei o que procuro</option>
            </select>
          </div>
        </div>

        {{-- Conditional: anúncio --}}
        <div id="ad_links_box" class="if-conditional" style="display:none">
          <div class="if-conditional__inner">
            <div class="if-field">
              <label class="if-label" for="ad_links">Links dos anúncios identificados <span class="if-req">*</span></label>
              <textarea name="ad_links" id="ad_links" class="if-textarea" rows="3" placeholder="Cole aqui os links dos anúncios (um por linha)"></textarea>
            </div>
          </div>
        </div>

        {{-- Conditional: preferências --}}
        <div id="preferences_box" class="if-conditional" style="display:none">
          <div class="if-conditional__inner">
            <div class="if-grid">
              <div class="if-field">
                <label class="if-label" for="brand">Marca <span class="if-req">*</span></label>
                <select name="brand" id="brand" class="if-select">
                  <option value="">Escolha a marca</option>
                  @foreach($brands as $b)
                  <option value="{{ $b->name }}">{{ $b->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="if-field">
                <label class="if-label" for="model">Modelo <span class="if-req">*</span></label>
                <select name="model" id="model" class="if-select" disabled>
                  <option value="">Escolha o modelo</option>
                </select>
              </div>
              <div class="if-field">
                <label class="if-label" for="submodel">Sub-modelo / Versão <span class="if-req">*</span></label>
                <input type="text" name="submodel" id="submodel" class="if-input" placeholder="ex: Sport Line, M Sport...">
              </div>
              <div class="if-field">
                <label class="if-label" for="fuel">Combustível <span class="if-req">*</span></label>
                <select name="fuel" id="fuel" class="if-select">
                  <option value="">Escolha</option>
                  <option value="gasolina">Gasolina</option>
                  <option value="diesel">Diesel</option>
                  <option value="hibrido_plugin_gasolina">Híbrido Plug-in / Gasolina</option>
                  <option value="hibrido_plugin_diesel">Híbrido Plug-in / Diesel</option>
                  <option value="eletrico">Elétrico</option>
                </select>
              </div>
              <div class="if-field">
                <label class="if-label" for="year_min">Ano mínimo <span class="if-req">*</span></label>
                <select name="year_min" id="year_min" class="if-select">
                  <option value="">Escolha um ano</option>
                  @php for($y = date('Y'); $y >= 2000; $y--): @endphp
                  <option value="{{ $y }}">{{ $y }}</option>
                  @php endfor; @endphp
                </select>
              </div>
              <div class="if-field">
                <label class="if-label" for="km_max">Quilómetros máximo <span class="if-req">*</span></label>
                <select name="km_max" id="km_max" class="if-select">
                  <option value="">Escolha uma opção</option>
                  <option value="10000">10.000 km</option>
                  <option value="20000">20.000 km</option>
                  <option value="30000">30.000 km</option>
                  <option value="50000">50.000 km</option>
                  <option value="75000">75.000 km</option>
                  <option value="100000">100.000 km</option>
                  <option value="150000">150.000 km</option>
                  <option value="200000">200.000 km</option>
                  <option value="+200000">+ de 200.000 km</option>
                </select>
              </div>
              <div class="if-field">
                <label class="if-label" for="color">Cor de preferência</label>
                <input type="text" name="color" id="color" class="if-input" placeholder="ex: Preto, Branco, Azul...">
              </div>
              <div class="if-field if-field--full">
                <label class="if-label" for="budgetSlider">Budget <span class="if-req">*</span></label>
                <div class="if-slider-wrap">
                  <input type="range" min="10000" max="100000" step="1000" value="40000" class="if-slider" name="budget" id="budgetSlider">
                  <span id="budgetValue" class="if-slider__val">40.000 €</span>
                </div>
              </div>
              <div class="if-field if-field--full">
                <label class="if-label">Caixa <span class="if-req">*</span></label>
                <div class="if-radio-group">
                  <label class="if-radio">
                    <input type="radio" name="gearbox" value="indiferente" id="gear_indiferente">
                    <span class="if-radio__box"></span>
                    Indiferente
                  </label>
                  <label class="if-radio">
                    <input type="radio" name="gearbox" value="automatica" id="gear_auto">
                    <span class="if-radio__box"></span>
                    Automática
                  </label>
                  <label class="if-radio">
                    <input type="radio" name="gearbox" value="manual" id="gear_manual">
                    <span class="if-radio__box"></span>
                    Manual
                  </label>
                </div>
              </div>
              <div class="if-field if-field--full">
                <label class="if-label" for="extras">Extras / Equipamento desejado</label>
                <textarea name="extras" id="extras" class="if-textarea" rows="2" placeholder="ex: Tecto de abrir, Pack M, Bancos em pele..."></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ── Consents + Submit ── --}}
      <div class="if-card if-reveal">
        <div class="if-consents">
          <label class="if-check">
            <input type="checkbox" name="data_processing_consent" id="data_processing_consent" value="1" checked required>
            <span class="if-check__box"></span>
            <span>Li e aceito o tratamento dos meus dados pessoais <span class="if-req">*</span></span>
          </label>
          <label class="if-check">
            <input type="checkbox" name="newsletter_consent" id="newsletter_consent" value="1" checked>
            <span class="if-check__box"></span>
            <span>Quero receber novidades e oportunidades por email</span>
          </label>
        </div>

        <div class="if-submit-wrap">
          <div class="if-privacy">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Os seus dados são tratados de forma confidencial.
          </div>
          <button type="submit" class="if-submit" id="ifSubmit">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Enviar Pedido
          </button>
        </div>
      </div>

    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
window.brands = @json($brands);

document.addEventListener('DOMContentLoaded', function () {

  /* ── Brand/Model cascade ── */
  const brandSel = document.getElementById('brand');
  const modelSel = document.getElementById('model');
  if (brandSel && modelSel) {
    brandSel.addEventListener('change', function () {
      const brandObj = window.brands.find(b => b.name === this.value);
      modelSel.innerHTML = '<option value="">Escolha o modelo</option>';
      modelSel.disabled = !brandObj;
      if (brandObj) {
        (brandObj.models || []).forEach(m => {
          const o = document.createElement('option');
          o.value = o.textContent = m.name;
          modelSel.appendChild(o);
        });
        modelSel.disabled = false;
      }
    });
  }

  /* ── ad_option conditional sections ── */
  const adOpt        = document.getElementById('ad_option');
  const adLinksBox   = document.getElementById('ad_links_box');
  const prefBox      = document.getElementById('preferences_box');
  const adLinksInput = document.getElementById('ad_links');
  const reqPref      = ['brand','model','submodel','fuel','year_min','km_max','budget'];

  function clearRequired() {
    if (adLinksInput) adLinksInput.removeAttribute('required');
    reqPref.forEach(id => { const el = document.getElementById(id); if(el) el.removeAttribute('required'); });
    document.getElementsByName('gearbox').forEach(g => g.removeAttribute('required'));
  }

  adOpt.addEventListener('change', function () {
    adLinksBox.style.display = 'none';
    prefBox.style.display = 'none';
    clearRequired();

    if (this.value === 'sim') {
      adLinksBox.style.display = '';
      if (adLinksInput) adLinksInput.setAttribute('required','required');
    } else if (this.value === 'nao_sei') {
      prefBox.style.display = '';
      reqPref.forEach(id => { const el = document.getElementById(id); if(el) el.setAttribute('required','required'); });
      document.getElementsByName('gearbox').forEach(g => g.setAttribute('required','required'));
    }
  });

  /* ── Budget slider ── */
  const slider = document.getElementById('budgetSlider');
  const valEl  = document.getElementById('budgetValue');
  if (slider && valEl) {
    function updateSlider() {
      const v   = parseInt(slider.value);
      const pct = (v - slider.min) / (slider.max - slider.min);
      slider.style.setProperty('--pct', pct);
      valEl.textContent = (v >= parseInt(slider.max)) ? '+100.000 €' : v.toLocaleString('pt-PT') + ' €';
      valEl.style.left = `calc(${pct * 100}%)`;
    }
    slider.addEventListener('input', updateSlider);
    updateSlider();
  }

  /* ── AJAX submit ── */
  const form    = document.getElementById('importForm');
  const btn     = document.getElementById('ifSubmit');
  const success = document.getElementById('ifSuccess');
  const errBox  = document.getElementById('ifError');

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    btn.disabled = true;
    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:if-spin .8s linear infinite"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" opacity=".25"/><path d="M21 12A9 9 0 003 12"/></svg> A enviar...';
    errBox.style.display = 'none';

    fetch('{{ route('frontend.import-submit') }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value },
      body: new FormData(form)
    })
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success') {
        form.reset();
        adLinksBox.style.display = 'none';
        prefBox.style.display = 'none';
        success.style.display = 'flex';
        success.scrollIntoView({ behavior:'smooth', block:'center' });
      } else {
        throw new Error();
      }
    })
    .catch(() => {
      errBox.style.display = 'flex';
    })
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Enviar Pedido';
    });
  });

  /* ── Reveal ── */
  const obs = new IntersectionObserver(es => {
    es.forEach(e => { if (e.isIntersecting) e.target.classList.add('is-visible'); });
  }, { threshold: 0.08 });
  document.querySelectorAll('.if-reveal').forEach(el => obs.observe(el));
});
</script>

<style>
/* ════════════════════════════════════════
   FORMULÁRIO DE IMPORTAÇÃO
════════════════════════════════════════ */
:root {
  --if-brand:  #6e0707;
  --if-dark:   #111111;
  --if-gray:   #6b7280;
  --if-light:  #f9fafb;
  --if-border: #e5e7eb;
  --if-shadow: 0 2px 12px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.04);
}
@keyframes if-fadeUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:none; } }
@keyframes if-spin   { to { transform:rotate(360deg); } }

/* ── Hero ── */
.if-hero {
  position:relative; background: var(--if-dark);
  padding: 5rem 1.5rem 4rem; overflow:hidden;
}
.if-hero__overlay {
  position:absolute; inset:0; pointer-events:none;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%236e0707' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
}
.if-hero__inner {
  position:relative; max-width:760px; margin:0 auto; text-align:center;
  animation: if-fadeUp .7s ease-out both;
}
.if-hero__badge {
  display:inline-flex; align-items:center; gap:.45rem;
  background:rgba(110,7,7,.2); border:1px solid rgba(110,7,7,.35);
  color:rgba(255,255,255,.9); font-size:.78rem; font-weight:600;
  padding:.4rem 1rem; border-radius:100px; margin-bottom:1.5rem;
}
.if-hero__title {
  font-size: clamp(1.8rem, 4.5vw, 3rem); font-weight:900;
  color:#fff; line-height:1.15; margin-bottom:1rem; letter-spacing:-.02em;
}
.if-hero__sub {
  font-size: clamp(.9rem, 2vw, 1.05rem); color:rgba(255,255,255,.65);
  line-height:1.7; max-width:580px; margin:0 auto 1.75rem;
}
.if-hero__pills { display:flex; gap:.6rem; justify-content:center; flex-wrap:wrap; }
.if-pill {
  display:inline-flex; align-items:center; gap:.4rem;
  background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12);
  color:rgba(255,255,255,.7); font-size:.75rem; font-weight:500;
  padding:.35rem .8rem; border-radius:100px;
}

/* ── Page ── */
.if-page { background: var(--if-light); }
.if-container { max-width:920px; margin:0 auto; padding:3rem 1.5rem 4rem; }

/* ── Alerts ── */
.if-success {
  display:flex; align-items:flex-start; gap:1rem;
  background:#f0fdf4; border:1px solid #86efac;
  border-radius:14px; padding:1.5rem; margin-bottom:2rem;
}
.if-success__icon {
  width:52px; height:52px; border-radius:12px; flex-shrink:0;
  background:#dcfce7; color:#16a34a;
  display:flex; align-items:center; justify-content:center;
}
.if-success__title { font-weight:700; color:#15803d; font-size:1.05rem; margin-bottom:.3rem; }
.if-success__text  { font-size:.85rem; color:#166534; margin:0; }
.if-error-box {
  display:flex; align-items:center; gap:.65rem;
  background:#fee2e2; border:1px solid #fecaca;
  border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.5rem;
  font-size:.85rem; color:#991b1b;
}

/* ── Card ── */
.if-card {
  background:#fff; border:1px solid var(--if-border);
  border-radius:20px; padding:2.5rem;
  box-shadow: var(--if-shadow); margin-bottom:1.5rem;
}
.if-card__head {
  display:flex; align-items:flex-start; gap:1rem; margin-bottom:2rem;
  padding-bottom:1.25rem; border-bottom:1px solid var(--if-border);
}
.if-card__step {
  width:40px; height:40px; border-radius:12px; flex-shrink:0;
  background: linear-gradient(135deg, var(--if-brand), #9b1111);
  color:#fff; font-weight:800; font-size:.9rem;
  display:flex; align-items:center; justify-content:center;
  box-shadow:0 4px 12px rgba(110,7,7,.3);
}
.if-card__title { font-size:1.2rem; font-weight:800; color:#111; margin:0 0 .2rem; }
.if-card__sub   { font-size:.82rem; color: var(--if-gray); margin:0; }
.mb-if          { margin-bottom:1.25rem; }

/* ── Grid ── */
.if-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px,1fr)); gap:1.25rem; }
.if-grid--2 { grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); }
.if-grid--1 { grid-template-columns:1fr; }
.if-field--full { grid-column: 1 / -1; }

/* ── Fields ── */
.if-field { display:flex; flex-direction:column; }
.if-label {
  font-size:.75rem; font-weight:700; color:#374151;
  text-transform:uppercase; letter-spacing:.05em; margin-bottom:.45rem;
  display:block;
}
.if-hint { display:block; font-size:.72rem; color: var(--if-gray); font-weight:400; text-transform:none; letter-spacing:0; margin-top:.2rem; }
.if-req  { color: var(--if-brand); }
.if-input, .if-select, .if-textarea {
  padding:.75rem 1rem; border-radius:10px;
  border:1.5px solid var(--if-border); background:#fff;
  font-size:.9rem; color:#111; outline:none;
  transition:border-color .2s, box-shadow .2s;
  width:100%;
}
.if-input:focus, .if-select:focus, .if-textarea:focus {
  border-color: var(--if-brand);
  box-shadow:0 0 0 3px rgba(110,7,7,.1);
}
.if-textarea { resize:vertical; min-height:90px; }

/* ── Conditional boxes ── */
.if-conditional { margin-top:.25rem; }
.if-conditional__inner {
  background: var(--if-light); border:1.5px dashed var(--if-border);
  border-radius:14px; padding:1.5rem;
}

/* ── Slider ── */
.if-slider-wrap { position:relative; padding-bottom:2.5rem; }
.if-slider {
  width:100%; height:6px; border-radius:10px; outline:none;
  background: linear-gradient(to right, var(--if-brand) calc(var(--pct,0.3) * 100%), var(--if-border) 0);
  appearance:none; cursor:pointer;
}
.if-slider::-webkit-slider-thumb {
  appearance:none; width:22px; height:22px;
  background:#fff; border:3px solid var(--if-brand);
  border-radius:50%; cursor:pointer;
  box-shadow:0 2px 8px rgba(110,7,7,.25);
  transition:transform .15s;
}
.if-slider::-webkit-slider-thumb:hover { transform:scale(1.2); }
.if-slider::-moz-range-thumb {
  width:22px; height:22px; background:#fff;
  border:3px solid var(--if-brand); border-radius:50%;
  cursor:pointer; box-shadow:0 2px 8px rgba(110,7,7,.25);
}
.if-slider__val {
  position:absolute; top:18px;
  transform:translateX(-50%);
  background: var(--if-brand); color:#fff;
  padding:.3rem .8rem; border-radius:8px;
  font-size:.8rem; font-weight:700; white-space:nowrap;
  pointer-events:none;
}

/* ── Radio group ── */
.if-radio-group { display:flex; gap:1rem; flex-wrap:wrap; }
.if-radio {
  display:flex; align-items:center; gap:.6rem;
  cursor:pointer; font-size:.88rem; color:#374151; font-weight:500;
}
.if-radio input { display:none; }
.if-radio__box {
  width:20px; height:20px; border-radius:50%; flex-shrink:0;
  border:2px solid var(--if-border); background:#fff;
  transition:border-color .2s, background .2s;
  position:relative;
}
.if-radio__box::after {
  content:''; position:absolute; inset:4px;
  border-radius:50%; background: var(--if-brand);
  transform:scale(0); transition:transform .15s;
}
.if-radio input:checked ~ .if-radio__box { border-color: var(--if-brand); }
.if-radio input:checked ~ .if-radio__box::after { transform:scale(1); }

/* ── Consents ── */
.if-consents { display:flex; flex-direction:column; gap:.75rem; margin-bottom:2rem; }
.if-check {
  display:flex; align-items:flex-start; gap:.7rem;
  cursor:pointer; font-size:.85rem; color:#374151; line-height:1.5;
}
.if-check input { display:none; }
.if-check__box {
  width:18px; height:18px; border-radius:5px; flex-shrink:0; margin-top:.1rem;
  border:2px solid var(--if-border); background:#fff;
  transition:border-color .2s, background .2s;
  display:flex; align-items:center; justify-content:center;
}
.if-check__box::after {
  content:''; width:10px; height:10px;
  background:url("data:image/svg+xml,%3Csvg viewBox='0 0 12 9' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 4l3.5 3.5L11 1' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat center / contain;
  opacity:0; transition:opacity .15s;
}
.if-check input:checked ~ .if-check__box { background: var(--if-brand); border-color: var(--if-brand); }
.if-check input:checked ~ .if-check__box::after { opacity:1; }

/* ── Submit ── */
.if-submit-wrap { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; }
.if-privacy {
  display:flex; align-items:center; gap:.4rem;
  color: var(--if-gray); font-size:.75rem;
}
.if-privacy svg { flex-shrink:0; color:#9ca3af; }
.if-submit {
  display:inline-flex; align-items:center; gap:.65rem;
  padding:1rem 2.5rem; border-radius:12px;
  background: linear-gradient(135deg, var(--if-brand), #9b1111);
  color:#fff; font-size:1rem; font-weight:700;
  border:none; cursor:pointer;
  box-shadow:0 6px 20px rgba(110,7,7,.35);
  transition:transform .15s, box-shadow .2s;
}
.if-submit:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 10px 28px rgba(110,7,7,.4); }
.if-submit:disabled { opacity:.7; cursor:not-allowed; }

/* ── Reveal ── */
.if-reveal { opacity:0; transform:translateY(22px); transition:opacity .5s ease, transform .5s ease; }
.if-reveal.is-visible { opacity:1; transform:none; }

@media(max-width:640px) {
  .if-card { padding:1.75rem 1.25rem; }
  .if-submit-wrap { flex-direction:column; align-items:stretch; }
  .if-submit { justify-content:center; }
}
</style>
@endpush
