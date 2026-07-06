@php
  $settings      = App\Models\Setting::get();
  $phone         = $settings->where('label', 'phone')->first()?->value ?? '';
  $email         = $settings->where('label', 'email')->first()?->value ?? '';
  $initPay       = $settings->where('label', 'init_payment')->first()?->value ?? '50%';
  $finalPay      = $settings->where('label', 'final_payment')->first()?->value ?? '50%';

  $totalCost     = $proposal->commission_cost + $proposal->inspection_commission_cost
                 + $proposal->license_plate_cost + $proposal->isv_cost
                 + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost
                 + $proposal->transport_cost + $proposal->proposed_car_value;

  $totalNoVat    = $proposal->commission_cost + $proposal->inspection_commission_cost
                 + $proposal->license_plate_cost + $proposal->isv_cost
                 + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost
                 + $proposal->transport_cost + ($proposal->proposed_car_value / 1.19);

  $serviceCost   = $proposal->commission_cost + $proposal->inspection_commission_cost
                 + $proposal->license_plate_cost + $proposal->registration_cost
                 + $proposal->imt_cost + $proposal->ipo_cost + $proposal->transport_cost;

  $isElectric    = in_array($proposal->fuel, ['Elétrico', 'Híbrido Plug-in/Gasolina', 'Híbrido Plug-in/Diesel']);
  $isAccepted    = App\Models\ConvertedProposal::where('proposal_id', $proposal->id)->exists();
  $modalId       = 'acceptModal';
@endphp

@extends('frontend.partials.layout')
<?php $no_footer = true; ?>
<meta name="robots" content="noindex, nofollow">

@section('title', $proposal->brand . ' ' . $proposal->model . ' — Cotação de Importação')

@section('content')

{{-- ══════════════════════════════════════════════
     STICKY BAR
══════════════════════════════════════════════ --}}
<div class="iz-bar" id="izBar">
  <div class="iz-bar__inner">
    <div class="iz-bar__logo">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="white"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.29l1.08 3.11H5.77L6.85 7zM19 17H5v-5h14v5z"/><circle cx="7.5" cy="14.5" r="1.5" fill="white"/><circle cx="16.5" cy="14.5" r="1.5" fill="white"/></svg>
      <span>{{$client->name}}</span>
    </div>
    <div class="iz-bar__vehicle">{{ $proposal->brand }} {{ $proposal->model }} · <strong>{{ number_format($totalCost, 0, ',', '.') }} €</strong></div>
    @if(!$isAccepted)
      <button class="iz-bar__cta" onclick="openModal()">Aceitar Cotação</button>
    @else
      <span class="iz-bar__accepted"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>Aceite</span>
    @endif
  </div>
</div>

{{-- ══════════════════════════════════════════════
     HERO
══════════════════════════════════════════════ --}}
<section class="iz-hero">
  @if($proposal->images)
  <div class="iz-hero__bg" style="background-image:url('{{ asset('storage/' . $proposal->images) }}')"></div>
  @endif
  <div class="iz-hero__overlay"></div>
  <div class="iz-hero__content">
    <div class="iz-hero__badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
      Cotação Personalizada · {{ $proposal->proposal_code }} · {{ $client->name }}
    </div>
    <h1 class="iz-hero__brand">{{ $proposal->brand }} {{ $proposal->model }}</h1>
    <p class="iz-hero__version">{{ $proposal->version }}</p>
    <div class="iz-hero__price-wrap">
      <div class="iz-hero__price-label">Total Chave na Mão</div>
      <div class="iz-hero__price" data-count="{{ (int)$totalCost }}">{{ number_format($totalCost, 0, ',', '.') }} €</div>
    </div>
    @if(!$isAccepted)
      <button class="iz-hero__cta" onclick="openModal()">
        Aceitar Esta Cotação
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </button>
    @else
      <div class="iz-hero__accepted-badge">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Cotação Aceite — Em Processamento
      </div>
    @endif
  </div>
  <div class="iz-hero__scroll">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" opacity=".6"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
  </div>
</section>

{{-- ══════════════════════════════════════════════
     MAIN CONTENT
══════════════════════════════════════════════ --}}
<div class="iz-page">

  @if(session('success'))
    <div class="iz-alert iz-alert--success">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="iz-alert iz-alert--error">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      {{ session('error') }}
    </div>
  @endif

  {{-- ── VEHICLE + COSTS ── --}}
  <section class="iz-section iz-reveal">
    <div class="iz-vehicle-grid">

      {{-- Left: image + specs --}}
      <div class="iz-vehicle-left">
        @if($proposal->images)
        <div class="iz-img-wrap">
          <img src="{{ asset('storage/' . $proposal->images) }}"
               onerror="this.src='{{ asset('img/logo-simples.png') }}';"
               alt="{{ $proposal->brand }} {{ $proposal->model }}"
               class="iz-img">
          @if($proposal->url)
          <a href="{{ $proposal->url }}" target="_blank" class="iz-img-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Ver Anúncio Original
          </a>
          @endif
        </div>
        @endif

        <div class="iz-specs">
          <div class="iz-spec">
            <div class="iz-spec__icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="iz-spec__body">
              <div class="iz-spec__label">Ano</div>
              <div class="iz-spec__value">{{ $proposal->proposed_car_year_month }}</div>
            </div>
          </div>
          <div class="iz-spec">
            <div class="iz-spec__icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 19V9a2 2 0 012-2h5l2 3h7a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="iz-spec__body">
              <div class="iz-spec__label">Combustível</div>
              <div class="iz-spec__value">{{ $proposal->fuel }}</div>
            </div>
          </div>
          <div class="iz-spec">
            <div class="iz-spec__icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="iz-spec__body">
              <div class="iz-spec__label">Quilómetros</div>
              <div class="iz-spec__value">{{ number_format($proposal->proposed_car_mileage, 0, ',', '.') }} km</div>
            </div>
          </div>
          @if($caixa)
          <div class="iz-spec">
            <div class="iz-spec__icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="8" height="8" rx="1"/><rect x="14" y="2" width="8" height="8" rx="1"/><rect x="2" y="14" width="8" height="8" rx="1"/><line x1="18" y1="14" x2="18" y2="22"/><line x1="22" y1="18" x2="14" y2="18"/></svg>
            </div>
            <div class="iz-spec__body">
              <div class="iz-spec__label">Transmissão</div>
              <div class="iz-spec__value">{{ $caixa }}</div>
            </div>
          </div>
          @endif
          @if($potencia)
          <div class="iz-spec">
            <div class="iz-spec__icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            </div>
            <div class="iz-spec__body">
              <div class="iz-spec__label">Potência</div>
              <div class="iz-spec__value">{{ $potencia }}</div>
            </div>
          </div>
          @endif
          @if($proposal->fuel !== 'Elétrico' && $proposal->engine_capacity)
          <div class="iz-spec">
            <div class="iz-spec__icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3m10-10h-3M5 12H2"/></svg>
            </div>
            <div class="iz-spec__body">
              <div class="iz-spec__label">Cilindrada</div>
              <div class="iz-spec__value">{{ $proposal->engine_capacity }} cc</div>
            </div>
          </div>
          @endif
          @if($proposal->co2)
          <div class="iz-spec">
            <div class="iz-spec__icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 4.5C15.5 3.6 13.8 3 12 3 8 3 5 6 5 10s3 7 7 7c1.8 0 3.5-.6 5-1.5"/><line x1="3" y1="9" x2="15" y2="9"/><line x1="3" y1="11" x2="15" y2="11"/></svg>
            </div>
            <div class="iz-spec__body">
              <div class="iz-spec__label">CO₂</div>
              <div class="iz-spec__value">{{ $proposal->co2 }} g/km</div>
            </div>
          </div>
          @endif
        </div>
      </div>

      {{-- Right: cost breakdown --}}
      <div class="iz-vehicle-right">
        <div class="iz-costs">
          <div class="iz-costs__header">
            <h2 class="iz-costs__title">Resumo de Custos</h2>
            <p class="iz-costs__sub">Transparência total — sem surpresas</p>
          </div>

          <div class="iz-costs__list">
            <div class="iz-cost-row">
              <div class="iz-cost-row__left">
                <div class="iz-cost-row__icon" style="background:#f0f4ff">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </div>
                <div>
                  <div class="iz-cost-row__name">Preço do Veículo</div>
                  <div class="iz-cost-row__hint">Valor no país de origem</div>
                </div>
              </div>
              <div class="iz-cost-row__value" data-count="{{ (int)$proposal->proposed_car_value }}">
                {{ number_format($proposal->proposed_car_value, 0, ',', '.') }} €
              </div>
            </div>

            <div class="iz-cost-row">
              <div class="iz-cost-row__left">
                <div class="iz-cost-row__icon" style="background:#fff4e6">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e67700" stroke-width="2"><path d="M17 4.5C15.5 3.6 13.8 3 12 3 8 3 5 6 5 10s3 7 7 7c1.8 0 3.5-.6 5-1.5"/><line x1="3" y1="9" x2="15" y2="9"/><line x1="3" y1="11" x2="15" y2="11"/></svg>
                </div>
                <div>
                  <div class="iz-cost-row__name">ISV</div>
                  <div class="iz-cost-row__hint">Imposto sobre veículos</div>
                </div>
              </div>
              <div class="iz-cost-row__value" data-count="{{ (int)$proposal->isv_cost }}">
                {{ number_format($proposal->isv_cost, 0, ',', '.') }} €
              </div>
            </div>


            <div class="iz-cost-row iz-cost-row--service">
              <div class="iz-cost-row__left">
                <div class="iz-cost-row__icon" style="background:#fdf2f8">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6e0707" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                  <div class="iz-cost-row__name">Serviço Izzycar</div>
                  <div class="iz-cost-row__hint">Inspeção na origem · Transporte · IPO · IMT · Matrícula · Gestão</div>
                </div>
              </div>
              <div class="iz-cost-row__value" data-count="{{ (int)$serviceCost }}">
                {{ number_format($serviceCost, 0, ',', '.') }} €
              </div>
            </div>
          </div>

          <div class="iz-costs__total-wrap">
            <div class="iz-costs__total-label">Total Chave na Mão</div>
            <div class="iz-costs__total" data-count="{{ (int)$totalCost }}">{{ number_format($totalCost, 0, ',', '.') }} €</div>
          </div>

          <div class="iz-iuc-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>
              <strong>Nota:</strong> O valor apresentado não inclui o IUC, uma vez que este é um imposto anual da responsabilidade do proprietário e é pago após a matrícula portuguesa, dentro do prazo legal.
              @if($proposal->iuc_cost > 0)
                Valor estimado: <strong>{{ number_format($proposal->iuc_cost, 0, ',', '.') }} €/ano</strong>.
              @endif
            </span>
          </div>

          @if($isElectric)
          <div class="iz-business-box">
            <div class="iz-business-box__badge">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
              Para Empresas
            </div>
            <div class="iz-business-box__value">{{ number_format($totalNoVat, 0, ',', '.') }} €</div>
            <div class="iz-business-box__label">Preço sem IVA (dedutível)</div>
          </div>
          @endif

          @if(!$isAccepted)
          <button class="iz-accept-btn" onclick="openModal()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Aceitar Esta Cotação
          </button>
          @else
          <div class="iz-accepted-pill">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Cotação Aceite — Em Processamento
          </div>
          @endif
        </div>
      </div>
    </div>
  </section>

  {{-- ── EQUIPMENT ── --}}
  @if(count($attributes) > 0)
  <section class="iz-section iz-reveal">
    <div class="iz-section-head">
      <div class="iz-section-head__icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><circle cx="12" cy="12" r="10"/></svg>
      </div>
      <div>
        <h2 class="iz-section-title">Equipamento Incluído</h2>
        <p class="iz-section-sub">Todas as características e extras deste veículo</p>
      </div>
    </div>

    <div class="iz-equipment">
      @foreach ($attributes as $group => $attrs)
      @php
        $filteredAttrs = collect($attrs)->filter(fn($v, $k) => !in_array($k, ['Potência','Cilindrada','Transmissão']));
      @endphp
      @if($filteredAttrs->count() > 0)
      <div class="iz-eq-group iz-reveal">
        <h3 class="iz-eq-group__title">
          <span class="iz-eq-group__dot"></span>
          {{ $group }}
        </h3>
        <div class="iz-eq-grid">
          @foreach ($filteredAttrs as $attr => $value)
          <div class="iz-eq-item">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6e0707" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ $attr == $value ? $attr : $attr . ': ' . $value }}</span>
          </div>
          @endforeach
        </div>
      </div>
      @endif
      @endforeach
    </div>
  </section>
  @endif

  {{-- ── TIMELINE ── --}}
  <section class="iz-section iz-section--dark iz-reveal">
    <div class="iz-section-head iz-section-head--light">
      <div class="iz-section-head__icon iz-section-head__icon--light">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div>
        <h2 class="iz-section-title iz-section-title--light">Da Escolha à Entrega</h2>
        <p class="iz-section-sub iz-section-sub--light">O seu veículo em 5 etapas · ~22 dias no total</p>
      </div>
    </div>

    <div class="iz-timeline">
      @php
        $steps = [
          ['num'=>1, 'title'=>'Aprovação',      'desc'=>'Análise da cotação e confirmação de disponibilidade do veículo no stand',              'days'=>'~3 dias'],
          ['num'=>2, 'title'=>'Pagamento',       'desc'=>'Processamento seguro do pagamento e aquisição formal do veículo',                     'days'=>'~4 dias'],
          ['num'=>3, 'title'=>'Transporte',      'desc'=>'Transporte seguro e segurado do veículo desde o país de origem até Portugal',          'days'=>'~12 dias'],
          ['num'=>4, 'title'=>'Legalização ISV', 'desc'=>'Inspeção técnica, cálculo e pagamento do Imposto Sobre Veículos (ISV)',                'days'=>'~3 dias'],
          ['num'=>5, 'title'=>'Entrega',         'desc'=>'Emissão de matrícula portuguesa, documentação final e entrega do veículo',            'days'=>null],
        ];
      @endphp

      @foreach($steps as $i => $step)
      <div class="iz-step iz-reveal" style="--delay:{{ $i * 0.1 }}s">
        <div class="iz-step__connector @if($loop->last) iz-step__connector--last @endif"></div>
        <div class="iz-step__num">{{ $step['num'] }}</div>
        <div class="iz-step__body">
          <div class="iz-step__top">
            <span class="iz-step__title">{{ $step['title'] }}</span>
            @if($step['days'])
              <span class="iz-step__days">{{ $step['days'] }}</span>
            @endif
          </div>
          <p class="iz-step__desc">{{ $step['desc'] }}</p>
        </div>
      </div>
      @endforeach
    </div>

    <p class="iz-timeline-note">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
      Os prazos são aproximados e podem variar por motivos logísticos ou administrativos.
    </p>
  </section>

  {{-- ── CONTRACTS + PAYMENT ── --}}
  <section class="iz-section iz-reveal">
    <div class="iz-two-col">

      <div class="iz-info-card iz-reveal">
        <div class="iz-info-card__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6e0707" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <h3 class="iz-info-card__title">Formalização Contratual</h3>
        <p class="iz-info-card__desc">O processo é oficializado com dois contratos que garantem total segurança jurídica.</p>
        <ul class="iz-contract-list">
          <li>
            <div class="iz-contract-list__bullet">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
              <strong>Contrato de Prestação de Serviços</strong>
              <p>Define todos os serviços incluídos, prazos e responsabilidades da Izzycar</p>
            </div>
          </li>
          <li>
            <div class="iz-contract-list__bullet">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
              <strong>Contrato de Compra e Venda</strong>
              <p>Formaliza a aquisição do veículo e a sua legalização em Portugal</p>
            </div>
          </li>
        </ul>
      </div>

      <div class="iz-info-card iz-reveal" style="--delay:.1s">
        <div class="iz-info-card__icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6e0707" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <h3 class="iz-info-card__title">Modelo de Pagamento</h3>
        <p class="iz-info-card__desc">Pagamentos simples e seguros, divididos em etapas claras e transparentes.</p>

        <div class="iz-payment-cards">
          <div class="iz-payment-card">
            <div class="iz-payment-card__label">Serviço Izzycar</div>
            <div class="iz-payment-card__steps">
              <div class="iz-payment-step">
                <span class="iz-payment-step__pct">{{ $initPay }}</span>
                <span class="iz-payment-step__when">Na assinatura do contrato</span>
              </div>
              <div class="iz-payment-step__divider">+</div>
              <div class="iz-payment-step">
                <span class="iz-payment-step__pct">{{ $finalPay }}</span>
                <span class="iz-payment-step__when">Na entrega do veículo</span>
              </div>
            </div>
          </div>
          <div class="iz-payment-card iz-payment-card--secondary">
            <div class="iz-payment-card__label">Veículo + ISV</div>
            <p class="iz-payment-card__note">Pagamento directo ao stand e ao Estado por transferência bancária</p>
          </div>
        </div>
      </div>

    </div>
  </section>

  {{-- ── FINAL CTA ── --}}
  @if(!$isAccepted)
  <section class="iz-cta-section iz-reveal">
    <div class="iz-cta">
      <h2 class="iz-cta__title">Pronto para Avançar?</h2>
      <p class="iz-cta__sub">A nossa equipa está disponível para responder a todas as suas questões.</p>
      <div class="iz-cta__contacts">
        @if($phone)
        <a href="tel:{{ $phone }}" class="iz-cta__contact">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
          {{ $phone }}
        </a>
        @endif
        @if($email)
        <a href="mailto:{{ $email }}" class="iz-cta__contact">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          {{ $email }}
        </a>
        @endif
      </div>
      <button class="iz-accept-btn iz-accept-btn--large" onclick="openModal()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Aceitar Esta Cotação
      </button>
    </div>
  </section>
  @endif

</div>{{-- /iz-page --}}

{{-- ══════════════════════════════════════════════
     ACCEPT MODAL
══════════════════════════════════════════════ --}}
@if(!$isAccepted)
<div class="iz-modal-backdrop" id="modalBackdrop" onclick="closeModal()"></div>
<div class="iz-modal" id="acceptModal" role="dialog" aria-labelledby="modalTitle">
  <div class="iz-modal__card">

    <div class="iz-modal__header">
      <div>
        <h2 class="iz-modal__title" id="modalTitle">Confirmar Aceitação</h2>
        <p class="iz-modal__sub">{{ $proposal->brand }} {{ $proposal->model }} · {{ $proposal->version }}</p>
      </div>
      <button class="iz-modal__close" onclick="closeModal()" aria-label="Fechar">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <div class="iz-modal__body">
      <div class="iz-modal__info">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b5bdb" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>Ao aceitar confirma que leu e concordou com todos os termos desta cotação.</span>
      </div>

      <form id="acceptForm" action="{{ route('proposals.accept', $proposal->id) }}" method="POST">
        @csrf

        @php
          $missingFields = [];
          if(!$client->phone || !$client->email || !$client->address ||
             !$client->postal_code || !$client->city || !$client->vat_number ||
             !$client->identification_number) {
            $missingFields = true;
          }
        @endphp

        @if($client->phone == null || $client->phone == '')
        <div class="iz-modal__field">
          <label class="iz-modal__label">Telemóvel</label>
          <input type="tel" name="phone" class="iz-modal__input" placeholder="9XX XXX XXX" required>
        </div>
        @endif

        @if($client->email == null || $client->email == '')
        <div class="iz-modal__field">
          <label class="iz-modal__label">Email</label>
          <input type="email" name="email" class="iz-modal__input" placeholder="email@exemplo.com" required>
        </div>
        @endif

        @if($client->address == null || $client->address == '')
        <div class="iz-modal__field">
          <label class="iz-modal__label">Morada</label>
          <input type="text" name="address" class="iz-modal__input" placeholder="Rua, nº, andar" required>
        </div>
        @endif

        @if($client->postal_code == null || $client->postal_code == '')
        <div class="iz-modal__field">
          <label class="iz-modal__label">Código Postal</label>
          <input type="text" name="postal_code" class="iz-modal__input" placeholder="0000-000" required>
        </div>
        @endif

        @if($client->city == null || $client->city == '')
        <div class="iz-modal__field">
          <label class="iz-modal__label">Cidade</label>
          <input type="text" name="city" class="iz-modal__input" placeholder="Cidade" required>
        </div>
        @endif

        @if($client->vat_number == null || $client->vat_number == '')
        <div class="iz-modal__field">
          <label class="iz-modal__label">NIF</label>
          <input type="text" name="vat_number" class="iz-modal__input" placeholder="000 000 000" required>
        </div>
        @endif

        @if($client->identification_number == null || $client->identification_number == '')
        <div class="iz-modal__field">
          <label class="iz-modal__label">Nº Cartão de Cidadão</label>
          <input type="text" name="identification_number" class="iz-modal__input" placeholder="00000000 0 ZZ0" required>
        </div>
        @endif

        <div class="iz-modal__summary">
          <div class="iz-modal__summary-row">
            <span>{{ $proposal->brand }} {{ $proposal->model }}</span>
            <span>{{ number_format($proposal->proposed_car_value, 0, ',', '.') }} €</span>
          </div>
          <div class="iz-modal__summary-row">
            <span>Impostos (ISV)</span>
            <span>{{ number_format($proposal->isv_cost, 0, ',', '.') }} €</span>
          </div>
          <div class="iz-modal__summary-row">
            <span>Serviço Izzycar</span>
            <span>{{ number_format($serviceCost, 0, ',', '.') }} €</span>
          </div>
          <div class="iz-modal__summary-row iz-modal__summary-row--total">
            <span>Total Chave na Mão</span>
            <span>{{ number_format($totalCost, 0, ',', '.') }} €</span>
          </div>
        </div>

        <button type="submit" class="iz-modal__submit" id="modalSubmit">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Confirmar Aceitação
        </button>
      </form>
    </div>
  </div>
</div>
@endif

@endsection

@push('scripts')
<script>
/* ── Modal ── */
function openModal() {
  document.getElementById('modalBackdrop').classList.add('is-open');
  document.getElementById('acceptModal').classList.add('is-open');
  document.body.style.overflow = 'hidden';
}
function closeModal() {
  document.getElementById('modalBackdrop').classList.remove('is-open');
  document.getElementById('acceptModal').classList.remove('is-open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if(e.key==='Escape') closeModal(); });

/* ── Form submit lock ── */
const form = document.getElementById('acceptForm');
if(form) {
  form.addEventListener('submit', function() {
    const btn = document.getElementById('modalSubmit');
    btn.disabled = true;
    btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin .8s linear infinite"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" opacity=".25"/><path d="M21 12A9 9 0 003 12"/></svg> A enviar...';
  });
}

/* ── Floating bar: appears after scrolling past the hero ── */
const bar = document.getElementById('izBar');
const heroEl = document.querySelector('.iz-hero');
function updateBar() {
  const heroBottom = heroEl ? heroEl.getBoundingClientRect().bottom : 300;
  bar.classList.toggle('is-scrolled', heroBottom < 0);
}
window.addEventListener('scroll', updateBar, { passive: true });

/* ── Reveal on scroll ── */
const reveal = new IntersectionObserver((entries) => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('is-visible'); });
}, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.iz-reveal').forEach(el => reveal.observe(el));

/* ── Counter animation ── */
function animateCount(el) {
  const target = parseInt(el.dataset.count);
  if(!target) return;
  const duration = 1200;
  const start = performance.now();
  function update(now) {
    const elapsed = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - elapsed, 3);
    const val = Math.round(eased * target);
    el.textContent = val.toLocaleString('pt-PT') + ' €';
    if(elapsed < 1) requestAnimationFrame(update);
  }
  requestAnimationFrame(update);
}
const counterObs = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if(e.isIntersecting && e.target.dataset.count) {
      animateCount(e.target);
      counterObs.unobserve(e.target);
    }
  });
}, { threshold: 0.5 });
document.querySelectorAll('[data-count]').forEach(el => counterObs.observe(el));
</script>

<style>
/* ════════════════════════════════════════
   IZZYCAR PROPOSAL V4
   Self-contained — no Bootstrap dependency
════════════════════════════════════════ */
:root {
  --iz-brand:   #6e0707;
  --iz-brand-d: #4a0505;
  --iz-black:   #0d0d0d;
  --iz-dark:    #111827;
  --iz-gray:    #6b7280;
  --iz-light:   #f9fafb;
  --iz-border:  #e5e7eb;
  --iz-white:   #ffffff;
  --iz-radius:  16px;
  --iz-shadow:  0 2px 12px rgba(0,0,0,.07), 0 1px 3px rgba(0,0,0,.05);
  --iz-shadow-md: 0 8px 32px rgba(0,0,0,.12);
}

@keyframes fadeUp   { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:none; } }
@keyframes fadeIn   { from { opacity:0; } to { opacity:1; } }
@keyframes spin     { to { transform:rotate(360deg); } }
@keyframes pulse-brand { 0%,100%{ box-shadow:0 0 0 0 rgba(110,7,7,.3); } 50%{ box-shadow:0 0 0 10px rgba(110,7,7,0); } }

/* ── Sticky bar (bottom floating, appears after hero) ── */
.iz-bar {
  position: fixed; bottom: 1.5rem; left:50%; transform:translateX(-50%) translateY(120%);
  z-index: 1050; max-width:680px; width:calc(100% - 2rem);
  background: rgba(13,13,13,.92);
  backdrop-filter: saturate(180%) blur(16px);
  -webkit-backdrop-filter: saturate(180%) blur(16px);
  border:1px solid rgba(255,255,255,.1);
  border-radius:16px;
  box-shadow:0 16px 48px rgba(0,0,0,.35);
  transition: transform .4s cubic-bezier(0.34,1.56,0.64,1), opacity .3s;
  opacity:0;
}
.iz-bar.is-scrolled { transform:translateX(-50%) translateY(0); opacity:1; }
.iz-bar__inner {
  display: flex; align-items: center; gap: 1rem;
  padding: .75rem 1rem;
}
.iz-bar__logo { display:flex; align-items:center; gap:.45rem; color:#fff; font-weight:700; font-size:.85rem; flex-shrink:0; }
.iz-bar__vehicle { flex:1; color:rgba(255,255,255,.65); font-size:.78rem; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.iz-bar__vehicle strong { color:#fff; }
.iz-bar__cta {
  flex-shrink:0; padding:.5rem 1.1rem; border-radius:10px;
  background: var(--iz-brand); color:#fff; font-size:.8rem; font-weight:600;
  border:none; cursor:pointer; transition: background .2s, transform .15s;
}
.iz-bar__cta:hover { background: var(--iz-brand-d); transform:scale(1.03); }
.iz-bar__accepted { flex-shrink:0; display:flex; align-items:center; gap:.4rem; color:#4ade80; font-size:.78rem; font-weight:600; }

/* ── Hero ── */
.iz-hero {
  position: relative; min-height: 65vh;
  display:flex; align-items:center; justify-content:center;
  overflow:hidden; padding: 100px 1.5rem 3rem;
}
.iz-hero__bg {
  position:absolute; inset:0;
  background-size:cover; background-position:center;
  filter: blur(3px) brightness(.5) saturate(1.2);
  transform: scale(1.05);
}
.iz-hero__overlay {
  position:absolute; inset:0;
  background: linear-gradient(to bottom, rgba(0,0,0,.3) 0%, rgba(13,13,13,.85) 100%);
}
.iz-hero__content {
  position:relative; text-align:center; max-width:700px; width:100%;
  animation: fadeUp .8s ease-out both;
}
.iz-hero__badge {
  display:inline-flex; align-items:center; gap:.5rem;
  background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
  color:rgba(255,255,255,.9); font-size:.78rem; font-weight:500;
  padding:.4rem 1rem; border-radius:100px; margin-bottom:1.5rem;
  backdrop-filter:blur(8px);
}
.iz-hero__brand {
  font-size: clamp(2.2rem, 6vw, 4rem); font-weight:800;
  color:#fff; line-height:1.1; margin-bottom:.5rem; letter-spacing:-.02em;
}
.iz-hero__version {
  font-size: clamp(.9rem, 2vw, 1.15rem);
  color:rgba(255,255,255,.6); margin-bottom:2rem;
}
.iz-hero__price-wrap { margin-bottom:2rem; }
.iz-hero__price-label { color:rgba(255,255,255,.5); font-size:.8rem; font-weight:500; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.3rem; }
.iz-hero__price {
  font-size: clamp(2rem,5vw,3.2rem); font-weight:800; color:#fff;
  letter-spacing:-.02em; line-height:1;
}
.iz-hero__cta {
  display:inline-flex; align-items:center; gap:.6rem;
  padding:.9rem 2.2rem; border-radius:12px;
  background: var(--iz-brand); color:#fff; font-size:1rem; font-weight:700;
  border:none; cursor:pointer; transition: background .2s, transform .15s, box-shadow .2s;
  animation: pulse-brand 2.5s ease-in-out 1.5s infinite;
}
.iz-hero__cta:hover { background: var(--iz-brand-d); transform:translateY(-2px); box-shadow:0 8px 24px rgba(110,7,7,.4); }
.iz-hero__accepted-badge {
  display:inline-flex; align-items:center; gap:.5rem;
  background:rgba(74,222,128,.15); border:1px solid rgba(74,222,128,.3);
  color:#4ade80; padding:.7rem 1.4rem; border-radius:12px; font-weight:600;
}
.iz-hero__scroll {
  position:absolute; bottom:2rem; left:50%; transform:translateX(-50%);
  animation: fadeUp 1s ease-out .8s both;
}

/* ── Page wrapper ── */
.iz-page {
  background: var(--iz-light); position:relative;
}

/* ── Alert ── */
.iz-alert {
  display:flex; align-items:center; gap:.75rem;
  max-width:1200px; margin:1.5rem auto 0; padding:1rem 1.5rem;
  border-radius:12px; font-size:.9rem;
}
.iz-alert--success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
.iz-alert--error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }

/* ── Sections ── */
.iz-section {
  max-width:1200px; margin:0 auto; padding: 5rem 1.5rem;
}
.iz-section--dark {
  max-width:100%; background: #111111;
  padding: 5rem 1.5rem;
}
.iz-section--dark .iz-section {
  max-width:1200px; margin:0 auto; padding:0;
}

.iz-section-head {
  display:flex; align-items:flex-start; gap:1rem; margin-bottom:2.5rem;
}
.iz-section-head--light .iz-section-title,
.iz-section-title--light { color:#fff; }
.iz-section-head--light .iz-section-sub,
.iz-section-sub--light { color:rgba(255,255,255,.5); }

.iz-section-head__icon {
  width:48px; height:48px; border-radius:14px;
  background:#fff; border:1px solid var(--iz-border);
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
  color: var(--iz-brand);
}
.iz-section-head__icon--light {
  background:rgba(255,255,255,.1); border-color:rgba(255,255,255,.12); color:#fff;
}
.iz-section-title { font-size:1.6rem; font-weight:800; color: var(--iz-dark); margin:0 0 .25rem; }
.iz-section-sub   { color: var(--iz-gray); font-size:.9rem; margin:0; }

/* ── Vehicle grid ── */
.iz-vehicle-grid {
  display:grid; gap:2rem;
  grid-template-columns: 1fr;
}
@media(min-width:1024px) {
  .iz-vehicle-grid { grid-template-columns: 1fr 1fr; }
}

/* ── Image ── */
.iz-img-wrap {
  position:relative; border-radius: var(--iz-radius); overflow:hidden;
  background:#000; aspect-ratio:16/10; margin-bottom:1.25rem;
}
.iz-img {
  width:100%; height:100%; object-fit:cover; display:block;
  transition:transform .4s ease;
}
.iz-img-wrap:hover .iz-img { transform:scale(1.03); }
.iz-img-link {
  position:absolute; bottom:1rem; right:1rem;
  display:inline-flex; align-items:center; gap:.4rem;
  background:rgba(0,0,0,.7); backdrop-filter:blur(8px);
  color:#fff; font-size:.75rem; font-weight:600;
  padding:.45rem .9rem; border-radius:9px; text-decoration:none;
  transition:background .2s;
}
.iz-img-link:hover { background:rgba(0,0,0,.9); color:#fff; }

/* ── Specs ── */
.iz-specs { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
.iz-spec {
  display:flex; align-items:center; gap:.75rem;
  background:#fff; border:1px solid var(--iz-border);
  border-radius:12px; padding:.9rem 1rem;
  transition:box-shadow .2s;
}
.iz-spec:hover { box-shadow: var(--iz-shadow); }
.iz-spec__icon { color: var(--iz-brand); flex-shrink:0; }
.iz-spec__label { font-size:.7rem; color: var(--iz-gray); font-weight:500; text-transform:uppercase; letter-spacing:.05em; }
.iz-spec__value { font-size:.9rem; font-weight:700; color: var(--iz-dark); margin-top:.15rem; }

/* ── Costs ── */
.iz-costs {
  background:#fff; border:1px solid var(--iz-border);
  border-radius: var(--iz-radius); padding:2rem;
  box-shadow: var(--iz-shadow);
  height:100%;
}
.iz-costs__header { margin-bottom:1.5rem; }
.iz-costs__title { font-size:1.15rem; font-weight:800; color: var(--iz-dark); margin:0 0 .25rem; }
.iz-costs__sub   { font-size:.8rem; color: var(--iz-gray); margin:0; }

.iz-costs__list { display:flex; flex-direction:column; gap:1rem; margin-bottom:1.5rem; }
.iz-cost-row {
  display:flex; align-items:center; justify-content:space-between; gap:1rem;
  padding:.75rem 1rem; border-radius:12px;
  background: var(--iz-light); border:1px solid transparent;
  transition:border-color .2s, box-shadow .2s;
}
.iz-cost-row:hover { border-color:var(--iz-border); box-shadow:var(--iz-shadow); }
.iz-cost-row--service { background:#fdf2f8; }
.iz-cost-row__left { display:flex; align-items:center; gap:.75rem; min-width:0; }
.iz-cost-row__icon {
  width:36px; height:36px; border-radius:10px; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
}
.iz-cost-row__name  { font-size:.85rem; font-weight:600; color: var(--iz-dark); }
.iz-cost-row__hint  { font-size:.72rem; color: var(--iz-gray); margin-top:.1rem; }
.iz-cost-row__value { font-size:1rem; font-weight:800; color: var(--iz-dark); white-space:nowrap; }

.iz-costs__total-wrap {
  padding:1.25rem; border-radius:12px;
  background: linear-gradient(135deg, var(--iz-brand) 0%, #9b1111 100%);
  margin-bottom:1.25rem;
  display:flex; align-items:center; justify-content:space-between;
  box-shadow: 0 4px 16px rgba(110,7,7,.35);
}
.iz-costs__total-label { font-size:.8rem; font-weight:500; color:rgba(255,255,255,.75); }
.iz-costs__total { font-size:1.6rem; font-weight:800; color:#fff; text-shadow: 0 1px 4px rgba(0,0,0,.25); }

.iz-iuc-note {
  display:flex; gap:.6rem; align-items:flex-start;
  background:#fafafa; border:1px solid #e5e7eb; border-radius:10px;
  padding:.9rem 1rem; margin-bottom:1.25rem;
  font-size:.8rem; color:#6b7280; line-height:1.5;
}
.iz-iuc-note svg { flex-shrink:0; margin-top:2px; color:#9ca3af; }
.iz-iuc-note strong { color:#374151; }

.iz-business-box {
  background:linear-gradient(135deg,#f0fdf4,#dcfce7);
  border:1px solid #86efac; border-radius:12px;
  padding:1rem 1.25rem; margin-bottom:1.25rem;
  display:flex; align-items:center; gap:1rem; flex-wrap:wrap;
}
.iz-business-box__badge {
  display:inline-flex; align-items:center; gap:.4rem;
  background:#fff; border:1px solid #86efac;
  color:#166534; font-size:.72rem; font-weight:700;
  padding:.3rem .7rem; border-radius:100px; white-space:nowrap;
}
.iz-business-box__value { font-size:1.3rem; font-weight:800; color:#166534; }
.iz-business-box__label { font-size:.72rem; color:#15803d; }

.iz-accept-btn {
  width:100%; padding:1rem; border-radius:12px;
  background: var(--iz-brand); color:#fff;
  font-size:.95rem; font-weight:700;
  border:none; cursor:pointer;
  display:flex; align-items:center; justify-content:center; gap:.6rem;
  transition: background .2s, transform .15s, box-shadow .2s;
}
.iz-accept-btn:hover { background: var(--iz-brand-d); transform:translateY(-1px); box-shadow:0 6px 20px rgba(110,7,7,.35); }
.iz-accept-btn--large { display:inline-flex; padding:1.1rem 2.5rem; width:auto; font-size:1.05rem; margin-top:1.5rem; }
.iz-accepted-pill {
  display:flex; align-items:center; justify-content:center; gap:.5rem;
  padding:1rem; border-radius:12px;
  background:#dcfce7; border:1px solid #86efac;
  color:#166534; font-weight:600; font-size:.9rem;
}

/* ── Equipment ── */
.iz-equipment { display:flex; flex-direction:column; gap:1.5rem; }
.iz-eq-group {
  background:#fff; border:1px solid var(--iz-border);
  border-radius: var(--iz-radius); padding:1.5rem;
}
.iz-eq-group__title {
  display:flex; align-items:center; gap:.6rem;
  font-size:.85rem; font-weight:700; color: var(--iz-dark);
  text-transform:uppercase; letter-spacing:.06em; margin:0 0 1rem;
}
.iz-eq-group__dot {
  width:8px; height:8px; border-radius:50%; background: var(--iz-brand); flex-shrink:0;
}
.iz-eq-grid {
  display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr)); gap:.5rem;
}
.iz-eq-item {
  display:flex; align-items:center; gap:.5rem;
  font-size:.82rem; color:#374151; padding:.35rem 0;
}

/* ── Timeline ── */
.iz-timeline {
  max-width:1200px; margin:0 auto;
  display:flex; flex-direction:column; gap:0;
}
.iz-step {
  display:flex; gap:1.5rem; position:relative;
  animation-delay: var(--delay,0s);
}
.iz-step__connector {
  position:absolute; left:20px; top:44px; bottom:-20px;
  width:2px; background:rgba(255,255,255,.15);
}
.iz-step__connector--last { display:none; }
.iz-step__num {
  width:40px; height:40px; border-radius:12px; flex-shrink:0;
  background: var(--iz-brand); color:#fff;
  font-size:.8rem; font-weight:800;
  display:flex; align-items:center; justify-content:center;
  position:relative; z-index:1; margin-top:.2rem;
  box-shadow:0 0 0 4px rgba(110,7,7,.2);
}
.iz-step__body { padding-bottom:2.5rem; flex:1; }
.iz-step__top { display:flex; align-items:center; gap:1rem; margin-bottom:.4rem; }
.iz-step__title { font-size:1.05rem; font-weight:700; color:#fff; }
.iz-step__days {
  font-size:.72rem; font-weight:600;
  background:rgba(255,255,255,.1); color:rgba(255,255,255,.7);
  padding:.25rem .65rem; border-radius:100px;
}
.iz-step__desc { font-size:.85rem; color:rgba(255,255,255,.55); line-height:1.6; margin:0; }

.iz-timeline-note {
  display:flex; align-items:center; gap:.5rem;
  color:rgba(255,255,255,.35); font-size:.78rem;
  max-width:1200px; margin:1rem auto 0; padding:0 1.5rem;
}
@media(min-width:768px) {
  .iz-timeline { flex-direction:row; flex-wrap:nowrap; gap:0; }
  .iz-step { flex-direction:column; flex:1; gap:.75rem; }
  .iz-step__connector {
    top:20px; left:44px; right:-20px; bottom:auto; height:2px; width:auto;
  }
  .iz-step__body { padding-bottom:0; }
  .iz-step__top { flex-direction:column; align-items:flex-start; gap:.25rem; }
}

/* ── Contracts & Payment ── */
.iz-two-col {
  display:grid; gap:1.5rem; grid-template-columns:1fr;
}
@media(min-width:768px) { .iz-two-col { grid-template-columns:1fr 1fr; } }

.iz-info-card {
  background:#fff; border:1px solid var(--iz-border);
  border-radius: var(--iz-radius); padding:2rem;
  box-shadow: var(--iz-shadow);
  animation-delay: var(--delay,0s);
}
.iz-info-card__icon {
  width:52px; height:52px; border-radius:14px;
  background:#fdf2f8; border:1px solid #fecdd3;
  display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;
}
.iz-info-card__title { font-size:1.1rem; font-weight:800; color: var(--iz-dark); margin:0 0 .5rem; }
.iz-info-card__desc  { font-size:.85rem; color: var(--iz-gray); margin:0 0 1.25rem; line-height:1.6; }

.iz-contract-list { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:.9rem; }
.iz-contract-list li { display:flex; gap:.9rem; }
.iz-contract-list__bullet {
  width:22px; height:22px; border-radius:7px; flex-shrink:0;
  background: var(--iz-brand); color:#fff;
  display:flex; align-items:center; justify-content:center; margin-top:.1rem;
}
.iz-contract-list strong { font-size:.88rem; color: var(--iz-dark); display:block; margin-bottom:.2rem; }
.iz-contract-list p { font-size:.8rem; color: var(--iz-gray); margin:0; line-height:1.5; }

.iz-payment-cards { display:flex; flex-direction:column; gap:.75rem; }
.iz-payment-card {
  background: var(--iz-light); border:1px solid var(--iz-border);
  border-radius:12px; padding:1rem 1.25rem;
}
.iz-payment-card--secondary { background:#f8f9fa; }
.iz-payment-card__label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color: var(--iz-brand); margin-bottom:.6rem; }
.iz-payment-card__steps { display:flex; align-items:center; gap:1rem; flex-wrap:wrap; }
.iz-payment-step { display:flex; flex-direction:column; }
.iz-payment-step__pct  { font-size:1.3rem; font-weight:800; color: var(--iz-dark); }
.iz-payment-step__when { font-size:.75rem; color: var(--iz-gray); }
.iz-payment-step__divider { font-size:1rem; color: var(--iz-gray); font-weight:300; }
.iz-payment-card__note { font-size:.82rem; color: var(--iz-gray); margin:0; line-height:1.5; }

/* ── Final CTA ── */
.iz-cta-section { padding:4rem 1.5rem; }
.iz-cta {
  max-width:640px; margin:0 auto; text-align:center;
  background:#fff; border:1px solid var(--iz-border);
  border-radius:24px; padding:3rem 2rem;
  box-shadow: var(--iz-shadow-md);
}
.iz-cta__title { font-size:1.8rem; font-weight:800; color: var(--iz-dark); margin:0 0 .5rem; }
.iz-cta__sub   { font-size:.9rem; color: var(--iz-gray); margin:0 0 1.5rem; }
.iz-cta__contacts { display:flex; gap:.75rem; justify-content:center; flex-wrap:wrap; margin-bottom:.5rem; }
.iz-cta__contact {
  display:inline-flex; align-items:center; gap:.5rem;
  padding:.65rem 1.2rem; border-radius:10px;
  background: var(--iz-light); border:1px solid var(--iz-border);
  color: var(--iz-dark); font-size:.85rem; font-weight:600; text-decoration:none;
  transition:background .2s, border-color .2s;
}
.iz-cta__contact:hover { background:#e9e9ea; border-color:#ccc; color: var(--iz-dark); }

/* ── Modal ── */
.iz-modal-backdrop {
  position:fixed; inset:0; background:rgba(0,0,0,.6);
  backdrop-filter:blur(4px); z-index:2000;
  opacity:0; pointer-events:none;
  transition:opacity .25s;
}
.iz-modal-backdrop.is-open { opacity:1; pointer-events:all; }

.iz-modal {
  position:fixed; inset:0; z-index:2001;
  display:flex; align-items:center; justify-content:center;
  padding:1rem; pointer-events:none;
  opacity:0; transform:translateY(20px) scale(.97);
  transition:opacity .25s, transform .25s;
}
.iz-modal.is-open { opacity:1; transform:none; pointer-events:all; }

.iz-modal__card {
  background:#fff; border-radius:20px; width:100%; max-width:480px;
  box-shadow:0 24px 64px rgba(0,0,0,.25);
  overflow:hidden; max-height:90vh; display:flex; flex-direction:column;
}
.iz-modal__header {
  display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;
  padding:1.5rem 1.75rem 1.25rem;
  border-bottom:1px solid var(--iz-border);
}
.iz-modal__title { font-size:1.2rem; font-weight:800; color: var(--iz-dark); margin:0 0 .2rem; }
.iz-modal__sub   { font-size:.8rem; color: var(--iz-gray); margin:0; }
.iz-modal__close {
  width:34px; height:34px; border-radius:9px; border:1px solid var(--iz-border);
  background:transparent; color: var(--iz-gray); cursor:pointer; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  transition:background .2s, color .2s;
}
.iz-modal__close:hover { background: var(--iz-light); color: var(--iz-dark); }

.iz-modal__body { padding:1.5rem 1.75rem; overflow-y:auto; }

.iz-modal__info {
  display:flex; align-items:flex-start; gap:.6rem;
  background:#eff6ff; border:1px solid #bfdbfe;
  border-radius:10px; padding:.75rem 1rem;
  font-size:.8rem; color:#1e40af; line-height:1.5; margin-bottom:1.25rem;
}
.iz-modal__field { margin-bottom:1rem; }
.iz-modal__label { display:block; font-size:.75rem; font-weight:600; color:#374151; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.4rem; }
.iz-modal__input {
  width:100%; padding:.7rem 1rem; border-radius:10px;
  border:1.5px solid var(--iz-border); background:#fff;
  font-size:.9rem; color: var(--iz-dark); outline:none;
  transition:border-color .2s, box-shadow .2s;
}
.iz-modal__input:focus { border-color: var(--iz-brand); box-shadow:0 0 0 3px rgba(110,7,7,.1); }

.iz-modal__summary {
  background: var(--iz-light); border:1px solid var(--iz-border);
  border-radius:12px; padding:1rem 1.25rem; margin:1.25rem 0;
  display:flex; flex-direction:column; gap:.5rem;
}
.iz-modal__summary-row {
  display:flex; justify-content:space-between;
  font-size:.83rem; color: var(--iz-gray);
}
.iz-modal__summary-row--total {
  font-size:.95rem; font-weight:800; color: var(--iz-dark);
  padding-top:.5rem; margin-top:.25rem;
  border-top:1px solid var(--iz-border);
}

.iz-modal__submit {
  width:100%; padding:1rem; border-radius:12px;
  background: var(--iz-brand); color:#fff;
  font-size:.95rem; font-weight:700;
  border:none; cursor:pointer;
  display:flex; align-items:center; justify-content:center; gap:.6rem;
  transition:background .2s;
}
.iz-modal__submit:hover:not(:disabled) { background: var(--iz-brand-d); }
.iz-modal__submit:disabled { opacity:.7; cursor:not-allowed; }

/* ── Reveal animation ── */
.iz-reveal { opacity:0; transform:translateY(24px); transition:opacity .55s ease, transform .55s ease; }
.iz-reveal.is-visible { opacity:1; transform:none; }
</style>
@endpush
