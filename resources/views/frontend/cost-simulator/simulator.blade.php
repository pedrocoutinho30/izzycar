@extends('frontend.partials.layout')

@include('frontend.partials.seo', [
'seo' => (object)[
  'meta_image'       => '',
  'title'            => 'Simulador de Custos de Importação',
  'meta_description' => 'Simule os custos de importar o seu carro para Portugal. Estimativa detalhada e transparente, incluindo ISV, transporte e todos os encargos. Sem surpresas.',
]])

@section('content')

{{-- ══════ HERO ══════ --}}
<section class="sc-hero">
  <div class="sc-hero__overlay"></div>
  <div class="sc-hero__inner">
    <div class="sc-hero__badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
      Simulador de Custos
    </div>
    <h1 class="sc-hero__title">Quanto custa importar<br>o seu carro?</h1>
    <p class="sc-hero__sub">Preencha os dados do veículo e receba uma estimativa completa no seu email — ISV, transporte e todos os custos incluídos.</p>
    <div class="sc-hero__pills">
      <span class="sc-pill"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Cálculo ISV automático</span>
      <span class="sc-pill"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Enviado por email</span>
      <span class="sc-pill"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Sem compromisso</span>
    </div>
  </div>
</section>

{{-- ══════ MAIN ══════ --}}
<div class="sc-page">
  <div class="sc-container">

    @if(session('pending_email'))
    <div class="sc-email-sent">
      <div class="sc-email-sent__icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </div>
      <div>
        <div class="sc-email-sent__title">Email enviado!</div>
        <p class="sc-email-sent__text">Enviámos o resultado para <strong>{{ session('pending_email') }}</strong>. Verifique também a pasta de spam se não receber em breve.</p>
      </div>
    </div>
    @endif

    <form method="POST" action="{{ route('frontend.cost-simulator.calculate') }}" id="scForm">
      @csrf

      {{-- ── STEP 1: Veículo ── --}}
      <div class="sc-card sc-reveal">
        <div class="sc-card__head">
          <div class="sc-card__step">1</div>
          <div>
            <h2 class="sc-card__title">Detalhes do Veículo</h2>
            <p class="sc-card__sub">Informação técnica necessária para o cálculo do ISV</p>
          </div>
        </div>

        <div class="sc-grid">
          <div class="sc-field">
            <label class="sc-label" for="pais_matricula">País da matrícula <span class="sc-req">*</span></label>
            <select name="pais_matricula" id="pais_matricula" class="sc-select" required>
              <option value="uniao-europeia" {{ old('pais_matricula','uniao-europeia')=='uniao-europeia'?'selected':'' }}>Estado-Membro da União Europeia</option>
              <option value="outro" {{ old('pais_matricula')=='outro'?'selected':'' }}>Outro país</option>
            </select>
          </div>

          <div class="sc-field">
            <label class="sc-label" for="estado_viatura">Estado da viatura <span class="sc-req">*</span></label>
            <select name="estado_viatura" id="estado_viatura" class="sc-select" required>
              <option value="usado" {{ old('estado_viatura','usado')=='usado'?'selected':'' }}>Usado</option>
              <option value="novo" {{ old('estado_viatura')=='novo'?'selected':'' }}>Novo</option>
            </select>
          </div>

          <div class="sc-field">
            <label class="sc-label" for="brand">Marca <span class="sc-req">*</span></label>
            <select name="brand" id="brand" class="sc-select" required>
              <option value="">Selecione a marca</option>
              @foreach($brands as $brand)
              <option value="{{ $brand->name }}" data-models="{{ json_encode($brand->models->pluck('name')) }}" {{ old('brand')==$brand->name?'selected':'' }}>{{ $brand->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="sc-field">
            <label class="sc-label" for="model">Modelo <span class="sc-req">*</span></label>
            <select name="model" id="model" class="sc-select" required disabled>
              <option value="">Primeiro selecione a marca</option>
            </select>
          </div>

          <div class="sc-field">
            <label class="sc-label" for="data_matricula">Data da matrícula <span class="sc-req">*</span></label>
            <input type="date" name="data_matricula" id="data_matricula" class="sc-input" value="{{ old('data_matricula') }}" required>
          </div>

          <div class="sc-field">
            <label class="sc-label" for="combustivel">Combustível <span class="sc-req">*</span></label>
            <select name="combustivel" id="combustivel" class="sc-select" required>
              <option value="gasolina" {{ old('combustivel','gasolina')=='gasolina'?'selected':'' }}>Gasolina</option>
              <option value="diesel" {{ old('combustivel')=='diesel'?'selected':'' }}>Diesel</option>
              <option value="eletrico" {{ old('combustivel')=='eletrico'?'selected':'' }}>Elétrico</option>
            </select>
          </div>

          <div class="sc-field sc-field--conditional" id="wrap_cilindrada">
            <label class="sc-label" for="cilindrada">Cilindrada (cc) <span class="sc-req">*</span></label>
            <input type="number" name="cilindrada" id="cilindrada" class="sc-input" placeholder="ex: 1998" value="{{ old('cilindrada') }}">
          </div>

          <div class="sc-field sc-field--conditional" id="wrap_tipo_medicao">
            <label class="sc-label" for="tipo_medicao">Método de homologação CO₂ <span class="sc-req">*</span></label>
            <select name="tipo_medicao" id="tipo_medicao" class="sc-select">
              <option value="WLTP" {{ old('tipo_medicao','WLTP')=='WLTP'?'selected':'' }}>WLTP (após 2018)</option>
              <option value="NEDC" {{ old('tipo_medicao')=='NEDC'?'selected':'' }}>NEDC (antes de 2018)</option>
            </select>
          </div>

          <div class="sc-field sc-field--conditional" id="wrap_co2">
            <label class="sc-label" for="co2">CO₂ (g/km) <span class="sc-req">*</span></label>
            <input type="number" name="co2" id="co2" class="sc-input" placeholder="ex: 120" value="{{ old('co2') }}">
          </div>

          <div class="sc-field sc-field--conditional" id="emissao_particulas_container" style="display:none">
            <label class="sc-label" for="emissao_particulas">Emissão de partículas (g/km) <span class="sc-req">*</span></label>
            <select name="emissao_particulas" id="emissao_particulas" class="sc-select">
              <option value="+0.0001" {{ old('emissao_particulas','+0.0001')=='+0.0001'?'selected':'' }}>&gt; 0.0001 g/km</option>
              <option value="-0.0001" {{ old('emissao_particulas')=='-0.0001'?'selected':'' }}>&le; 0.0001 g/km</option>
            </select>
          </div>

          <div class="sc-field sc-field--conditional" id="wrap_tipo_veiculo">
            <label class="sc-label" for="tipo_veiculo">Tipo de veículo <span class="sc-req">*</span></label>
            <select name="tipo_veiculo" id="tipo_veiculo" class="sc-select">
              <option value="passageiros" {{ old('tipo_veiculo','passageiros')=='passageiros'?'selected':'' }}>Ligeiro de passageiros</option>
              <option value="hibrido" {{ old('tipo_veiculo')=='hibrido'?'selected':'' }}>Ligeiro Híbrido</option>
              <option value="hibrido_plug_in" {{ old('tipo_veiculo')=='hibrido_plug_in'?'selected':'' }}>Ligeiro Híbrido Plug-in</option>
            </select>
          </div>

          <div class="sc-field sc-field--conditional" id="autonomia_container" style="display:none">
            <label class="sc-label" for="autonomia">Autonomia da bateria <span class="sc-req">*</span></label>
            <select name="autonomia" id="autonomia" class="sc-select">
              <option value="igual_superior" {{ old('autonomia','igual_superior')=='igual_superior'?'selected':'' }}>&ge; 50 km</option>
              <option value="inferior" {{ old('autonomia')=='inferior'?'selected':'' }}>&lt; 50 km</option>
            </select>
          </div>

          <div class="sc-field sc-field--highlight">
            <label class="sc-label" for="valor_carro">Valor do carro (€) <span class="sc-req">*</span></label>
            <div class="sc-input-prefix">
              <span class="sc-prefix">€</span>
              <input type="number" name="valor_carro" id="valor_carro" class="sc-input sc-input--prefixed" placeholder="25 000" value="{{ old('valor_carro') }}" required>
            </div>
          </div>
        </div>
      </div>

      {{-- ── STEP 2: Dados pessoais ── --}}
      <div class="sc-card sc-reveal">
        <div class="sc-card__head">
          <div class="sc-card__step">2</div>
          <div>
            <h2 class="sc-card__title">Os seus dados</h2>
            <p class="sc-card__sub">Enviamos o resultado detalhado para o seu email</p>
          </div>
        </div>

        <div class="sc-grid sc-grid--3">
          <div class="sc-field">
            <label class="sc-label" for="name">Nome completo <span class="sc-req">*</span></label>
            <input type="text" name="name" id="name" class="sc-input @error('name') sc-input--error @enderror" placeholder="O seu nome" value="{{ old('name') }}" required>
            @error('name')<p class="sc-error">{{ $message }}</p>@enderror
          </div>
          <div class="sc-field">
            <label class="sc-label" for="email">Email <span class="sc-req">*</span></label>
            <input type="email" name="email" id="email" class="sc-input @error('email') sc-input--error @enderror" placeholder="email@exemplo.com" value="{{ old('email') }}" required>
            @error('email')<p class="sc-error">{{ $message }}</p>@enderror
          </div>
          <div class="sc-field">
            <label class="sc-label" for="phone">Telefone <span class="sc-req">*</span></label>
            <input type="text" name="phone" id="phone" class="sc-input @error('phone') sc-input--error @enderror" placeholder="+351 9XX XXX XXX" value="{{ old('phone') }}" required>
            @error('phone')<p class="sc-error">{{ $message }}</p>@enderror
          </div>
        </div>
      </div>

      {{-- ── SUBMIT ── --}}
      <div class="sc-submit-wrap sc-reveal">
        <div class="sc-privacy">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Os seus dados são usados apenas para enviar o resultado. Não serão partilhados.
        </div>
        <button type="submit" class="sc-submit" id="scSubmit">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
          Simular Custos
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
      </div>
    </form>

  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

  /* ── Brand/Model cascade ── */
  const brandSel = document.getElementById('brand');
  const modelSel = document.getElementById('model');
  brandSel.addEventListener('change', function () {
    const models = JSON.parse(this.options[this.selectedIndex].dataset.models || '[]');
    modelSel.innerHTML = '<option value="">Selecione o modelo</option>';
    models.forEach(m => {
      const o = document.createElement('option');
      o.value = o.textContent = m;
      modelSel.appendChild(o);
    });
    modelSel.disabled = !models.length;
  });

  /* ── Field visibility ── */
  const combustivel   = document.getElementById('combustivel');
  const tipoVeiculo   = document.getElementById('tipo_veiculo');
  const fields = {
    cilindrada:          { wrap: 'wrap_cilindrada',        input: 'cilindrada' },
    tipo_medicao:        { wrap: 'wrap_tipo_medicao',      input: 'tipo_medicao' },
    co2:                 { wrap: 'wrap_co2',               input: 'co2' },
    emissao_particulas:  { wrap: 'emissao_particulas_container', input: 'emissao_particulas' },
    tipo_veiculo:        { wrap: 'wrap_tipo_veiculo',      input: 'tipo_veiculo' },
    autonomia:           { wrap: 'autonomia_container',    input: 'autonomia' },
  };

  function show(id, required) {
    const f = fields[id];
    const wrap = document.getElementById(f.wrap);
    const inp  = document.getElementById(f.input);
    if (!wrap) return;
    wrap.style.display = '';
    wrap.classList.add('sc-field--visible');
    if (required) inp.setAttribute('required','required');
    else inp.removeAttribute('required');
  }
  function hide(id) {
    const f = fields[id];
    const wrap = document.getElementById(f.wrap);
    const inp  = document.getElementById(f.input);
    if (!wrap) return;
    wrap.style.display = 'none';
    wrap.classList.remove('sc-field--visible');
    inp.removeAttribute('required');
  }

  function update() {
    const c = combustivel.value;
    const t = tipoVeiculo.value;
    // Hide everything first
    ['cilindrada','tipo_medicao','co2','emissao_particulas','tipo_veiculo','autonomia'].forEach(hide);

    if (c === 'eletrico') {
      // nothing extra
    } else if (c === 'diesel') {
      show('cilindrada', true); show('tipo_medicao', true); show('co2', true);
      show('emissao_particulas', true); show('tipo_veiculo', true);
    } else {
      show('cilindrada', true); show('tipo_medicao', true); show('co2', true);
      show('tipo_veiculo', true);
    }
    if (t === 'hibrido_plug_in') show('autonomia', true);
  }

  combustivel.addEventListener('change', update);
  tipoVeiculo.addEventListener('change', update);
  update();

  /* ── Submit loading state ── */
  document.getElementById('scForm').addEventListener('submit', function () {
    const btn = document.getElementById('scSubmit');
    btn.disabled = true;
    btn.classList.add('is-loading');
    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:sc-spin .8s linear infinite"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" opacity=".25"/><path d="M21 12A9 9 0 003 12"/></svg> A calcular...';
  });

  /* ── Reveal on scroll ── */
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('is-visible'); });
  }, { threshold: 0.08 });
  document.querySelectorAll('.sc-reveal').forEach(el => obs.observe(el));
});
</script>

<style>
/* ════════════════════════════════════════
   SIMULADOR DE CUSTOS
════════════════════════════════════════ */
:root {
  --sc-brand:  #6e0707;
  --sc-dark:   #111111;
  --sc-gray:   #6b7280;
  --sc-light:  #f9fafb;
  --sc-border: #e5e7eb;
  --sc-radius: 16px;
  --sc-shadow: 0 2px 12px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.04);
}
@keyframes sc-fadeUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:none; } }
@keyframes sc-spin   { to { transform:rotate(360deg); } }

/* ── Hero ── */
.sc-hero {
  position:relative; background: var(--sc-dark);
  padding: 5rem 1.5rem 4rem;
  overflow:hidden;
}
.sc-hero__overlay {
  position:absolute; inset:0; pointer-events:none;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%236e0707' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
}
.sc-hero__inner {
  position:relative; max-width:760px; margin:0 auto; text-align:center;
  animation: sc-fadeUp .7s ease-out both;
}
.sc-hero__badge {
  display:inline-flex; align-items:center; gap:.45rem;
  background:rgba(110,7,7,.2); border:1px solid rgba(110,7,7,.35);
  color:rgba(255,255,255,.9); font-size:.78rem; font-weight:600;
  padding:.4rem 1rem; border-radius:100px; margin-bottom:1.5rem;
}
.sc-hero__title {
  font-size: clamp(1.8rem, 4.5vw, 3rem); font-weight:900;
  color:#fff; line-height:1.15; margin-bottom:1rem; letter-spacing:-.02em;
}
.sc-hero__sub {
  font-size: clamp(.9rem,2vw,1.1rem); color:rgba(255,255,255,.65);
  line-height:1.7; max-width:580px; margin:0 auto 1.75rem;
}
.sc-hero__pills { display:flex; gap:.6rem; justify-content:center; flex-wrap:wrap; }
.sc-pill {
  display:inline-flex; align-items:center; gap:.4rem;
  background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12);
  color:rgba(255,255,255,.7); font-size:.75rem; font-weight:500;
  padding:.35rem .8rem; border-radius:100px;
}

/* ── Page ── */
.sc-page { background: var(--sc-light); }
.sc-container { max-width:860px; margin:0 auto; padding:3rem 1.5rem 4rem; }

/* ── Email sent ── */
.sc-email-sent {
  display:flex; align-items:flex-start; gap:1rem;
  background:#f0fdf4; border:1px solid #86efac;
  border-radius:14px; padding:1.25rem 1.5rem; margin-bottom:2rem;
}
.sc-email-sent__icon {
  width:48px; height:48px; border-radius:12px; flex-shrink:0;
  background:#dcfce7; color:#16a34a;
  display:flex; align-items:center; justify-content:center;
}
.sc-email-sent__title { font-weight:700; color:#15803d; margin-bottom:.25rem; }
.sc-email-sent__text  { font-size:.85rem; color:#166534; margin:0; }

/* ── Card ── */
.sc-card {
  background:#fff; border:1px solid var(--sc-border);
  border-radius:20px; padding:2.5rem;
  box-shadow: var(--sc-shadow); margin-bottom:1.5rem;
}
.sc-card__head {
  display:flex; align-items:flex-start; gap:1rem; margin-bottom:2rem;
  padding-bottom:1.25rem; border-bottom:1px solid var(--sc-border);
}
.sc-card__step {
  width:40px; height:40px; border-radius:12px; flex-shrink:0;
  background: linear-gradient(135deg, var(--sc-brand), #9b1111);
  color:#fff; font-weight:800; font-size:.9rem;
  display:flex; align-items:center; justify-content:center;
  box-shadow:0 4px 12px rgba(110,7,7,.3);
}
.sc-card__title { font-size:1.2rem; font-weight:800; color:#111; margin:0 0 .2rem; }
.sc-card__sub   { font-size:.82rem; color: var(--sc-gray); margin:0; }

/* ── Grid ── */
.sc-grid {
  display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:1.25rem;
}
.sc-grid--3 { grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); }

/* ── Field ── */
.sc-field { display:flex; flex-direction:column; }
.sc-field--highlight .sc-input-prefix { position:relative; }
.sc-label {
  font-size:.75rem; font-weight:700; color:#374151;
  text-transform:uppercase; letter-spacing:.05em; margin-bottom:.45rem;
}
.sc-req { color: var(--sc-brand); }
.sc-input, .sc-select {
  padding:.75rem 1rem; border-radius:10px;
  border:1.5px solid var(--sc-border); background:#fff;
  font-size:.9rem; color:#111; outline:none;
  transition:border-color .2s, box-shadow .2s;
  width:100%;
}
.sc-input:focus, .sc-select:focus {
  border-color: var(--sc-brand);
  box-shadow:0 0 0 3px rgba(110,7,7,.1);
}
.sc-input--error { border-color:#ef4444; }
.sc-input--error:focus { box-shadow:0 0 0 3px rgba(239,68,68,.12); }
.sc-error { font-size:.75rem; color:#ef4444; margin:.3rem 0 0; }
.sc-prefix {
  position:absolute; left:.9rem; top:50%; transform:translateY(-50%);
  color: var(--sc-gray); font-weight:600; pointer-events:none;
}
.sc-input--prefixed { padding-left:2.2rem; }

/* ── Submit ── */
.sc-submit-wrap { text-align:center; }
.sc-privacy {
  display:inline-flex; align-items:center; gap:.45rem;
  color: var(--sc-gray); font-size:.78rem; margin-bottom:1.25rem;
}
.sc-privacy svg { flex-shrink:0; color:#9ca3af; }
.sc-submit {
  display:inline-flex; align-items:center; gap:.7rem;
  padding:1rem 2.5rem; border-radius:12px;
  background: linear-gradient(135deg, var(--sc-brand), #9b1111);
  color:#fff; font-size:1rem; font-weight:700;
  border:none; cursor:pointer;
  box-shadow:0 6px 20px rgba(110,7,7,.35);
  transition:transform .15s, box-shadow .2s;
}
.sc-submit:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 10px 28px rgba(110,7,7,.4); }
.sc-submit:disabled { opacity:.7; cursor:not-allowed; }

/* ── Reveal ── */
.sc-reveal { opacity:0; transform:translateY(22px); transition:opacity .5s ease, transform .5s ease; }
.sc-reveal.is-visible { opacity:1; transform:none; }

@media(max-width:640px) {
  .sc-card { padding:1.75rem 1.25rem; }
  .sc-submit { width:100%; justify-content:center; }
}
</style>
@endpush
