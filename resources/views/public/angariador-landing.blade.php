@extends('frontend.partials.layout')

@section('title', 'Torna-te Angariador Izzycar | Ganha Comissões a Indicar Importações')
@section('meta_description', 'Conhece pessoas interessadas em importar um carro? Torna-te angariador Izzycar: partilha o teu link pessoal e recebe uma comissão por cada negócio fechado. A Izzycar trata de todo o resto.')

@section('content')

{{-- ══════ HERO ══════ --}}
<section class="al-hero">
    <div class="al-hero__noise"></div>
    <div class="container">
        <div class="al-hero__inner">
            <span class="al-badge">
                <i class="bi bi-people-fill"></i>
                Programa de Angariadores
            </span>
            <h1 class="al-hero__title">Torna-te Angariador <span class="al-accent">Izzycar</span></h1>
            <p class="al-hero__desc">Não precisas de saber nada sobre importação de carros. Só precisas de conhecer pessoas interessadas — nós tratamos de tudo o resto, da pesquisa à entrega chave na mão.</p>
            <div class="al-hero__actions">
                <a href="{{ route('public.angariador.register') }}" class="al-btn al-btn--primary">
                    Candidatar-me a Angariador
                    <i class="bi bi-arrow-right"></i>
                </a>
                <a href="#como-funciona" class="al-btn al-btn--outline">Ver Como Funciona</a>
            </div>
        </div>
    </div>
</section>

{{-- ══════ BENEFÍCIOS ══════ --}}
<section class="al-section">
    <div class="container">
        <span class="al-eyebrow">Porque Ser Angariador</span>
        <h2 class="al-h2">Uma Forma Simples de Ganhar Dinheiro Extra</h2>
        <p class="al-lede">Angarias contactos, a Izzycar fecha o negócio.</p>

        <div class="al-grid al-grid--4">
            <div class="al-card">
                <div class="al-card__icon"><i class="bi bi-mortarboard"></i></div>
                <h3 class="al-card__title">Zero Conhecimento Técnico</h3>
                <p class="al-card__desc">Não precisas de entender de carros, impostos ou importação. A nossa equipa trata de toda a pesquisa, negociação, transporte e legalização.</p>
            </div>
            <div class="al-card">
                <div class="al-card__icon"><i class="bi bi-link-45deg"></i></div>
                <h3 class="al-card__title">O Teu Link Pessoal</h3>
                <p class="al-card__desc">Recebes um código único. Qualquer pessoa que preencha o formulário através do teu link fica associada a ti durante 30 dias.</p>
            </div>
            <div class="al-card">
                <div class="al-card__icon"><i class="bi bi-cash-coin"></i></div>
                <h3 class="al-card__title">Comissão Por Cada Negócio</h3>
                <p class="al-card__desc">Recebes uma comissão fixa por cada proposta convertida em negócio, paga até 48 horas após a entrega do veículo ao cliente.</p>
            </div>
            <div class="al-card">
                <div class="al-card__icon"><i class="bi bi-speedometer2"></i></div>
                <h3 class="al-card__title">Tudo Num Painel</h3>
                <p class="al-card__desc">Acompanhas as tuas leads, o estado de cada proposta e a tua comissão pendente e recebida, tudo num único painel.</p>
            </div>
        </div>
    </div>
</section>

{{-- ══════ COMO FUNCIONA ══════ --}}
<section class="al-section al-section--alt" id="como-funciona">
    <div class="container al-container--narrow">
        <span class="al-eyebrow" style="display:flex;width:fit-content;margin:0 auto 1rem;">Como Funciona</span>
        <h2 class="al-h2">Do Registo à Comissão em 4 Passos</h2>
        <p class="al-lede">Sem burocracia, sem necessidade de dedicação a tempo inteiro.</p>

        <div class="al-steps">
            <div class="al-step">
                <div class="al-step__num">01</div>
                <div>
                    <h3 class="al-step__title">Candidata-te</h3>
                    <p class="al-step__desc">Preenches o formulário de candidatura. A nossa equipa analisa o teu registo e ativa a tua conta.</p>
                </div>
            </div>
            <div class="al-step">
                <div class="al-step__num">02</div>
                <div>
                    <h3 class="al-step__title">Recebe o Teu Link</h3>
                    <p class="al-step__desc">Depois de aprovado, recebes um código pessoal e um link único para partilhares com quem conheces.</p>
                </div>
            </div>
            <div class="al-step">
                <div class="al-step__num">03</div>
                <div>
                    <h3 class="al-step__title">Partilha e Acompanha</h3>
                    <p class="al-step__desc">Envias o teu link a quem tem interesse em importar um carro e acompanhas cada lead e proposta no teu painel.</p>
                </div>
            </div>
            <div class="al-step">
                <div class="al-step__num">04</div>
                <div>
                    <h3 class="al-step__title">Recebe a Tua Comissão</h3>
                    <p class="al-step__desc">Quando o negócio se fecha e o carro é entregue, a tua comissão é paga até 48 horas depois.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════ COMISSÃO ══════ --}}
<section class="al-section">
    <div class="container al-container--narrow">
        <div class="al-commission">
            <div class="al-commission__value">100€</div>
            <p class="al-commission__label">por cada proposta convertida em negócio</p>
            <p class="al-commission__note">Valor de referência, gerado automaticamente quando a lead aceita a proposta e pago até 48h após a entrega do veículo.</p>
        </div>
    </div>
</section>

{{-- ══════ PAINEL ══════ --}}
<section class="al-section al-section--alt">
    <div class="container al-container--narrow">
        <span class="al-eyebrow" style="display:flex;width:fit-content;margin:0 auto 1rem;">O Teu Painel</span>
        <h2 class="al-h2">Tudo o Que Precisas, Num Só Lugar</h2>
        <p class="al-lede">Sem folhas de cálculo, sem perguntar "em que ponto está a minha lead?".</p>

        <div class="al-panel-list">
            <div class="al-panel-item">
                <i class="bi bi-link-45deg"></i>
                <span>O teu link de angariador</span>
            </div>
            <div class="al-panel-item">
                <i class="bi bi-people"></i>
                <span>Leads Geradas &amp; Convertidas</span>
            </div>
            <div class="al-panel-item">
                <i class="bi bi-wallet2"></i>
                <span>Comissão Pendente &amp; Recebida</span>
            </div>
        </div>
    </div>
</section>

{{-- ══════ CTA FINAL ══════ --}}
<section class="al-section">
    <div class="container al-container--narrow">
        <div class="al-cta-band">
            <h2 class="al-h2" style="color:#fff;">Pronto Para Começares a Ganhar?</h2>
            <p>Candidata-te agora — a nossa equipa analisa o teu registo e entra em contacto para ativar a tua conta.</p>
            <div class="al-btn-row">
                <a href="{{ route('public.angariador.register') }}" class="al-btn al-btn--light">Candidatar-me a Angariador</a>
                <a href="{{ route('public.manual.angariador') }}" class="al-btn al-btn--outline">Já Sou Angariador — Ver Manual</a>
            </div>
            <p class="al-cta-login">Já tens conta? <a href="{{ route('login') }}">Iniciar Sessão</a></p>
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
:root {
    --al-red: #990000;
    --al-bordo: #6e0707;
    --al-gradient: linear-gradient(135deg, #6e0707 0%, #9b1111 100%);
}

.al-badge, .al-eyebrow {
    display: inline-flex; align-items: center; gap: .5rem;
    font-size: .78rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
    padding: .45rem 1.1rem; border-radius: var(--radius-sharp-sm);
}
.al-badge { background: rgba(153,0,0,.2); border: 1px solid rgba(153,0,0,.3); color: #fff; margin-bottom: 1.75rem; }
.al-eyebrow { background: rgba(110,7,7,.08); border: 1px solid rgba(110,7,7,.18); color: var(--al-bordo); margin-bottom: 1.25rem; }

.al-hero { position: relative; background: #0a0a0a; padding: 6rem 0 5rem; overflow: hidden; }
.al-hero__noise {
    position: absolute; inset: 0; pointer-events: none; opacity: .5;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23990000' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
}
.al-hero__inner { position: relative; max-width: 720px; text-align: center; margin: 0 auto; }
.al-hero__title { font-size: clamp(2.1rem, 5vw, 3.4rem); font-weight: 900; color: #fff; line-height: 1.15; margin-bottom: 1.25rem; }
.al-accent { color: #ff5555; }
.al-hero__desc { font-size: clamp(1rem, 1.6vw, 1.15rem); color: rgba(255,255,255,.7); line-height: 1.7; margin-bottom: 2.25rem; }
.al-hero__actions { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }

.al-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: .6rem;
    font-weight: 700; font-size: .95rem; padding: .95rem 2.1rem; border-radius: var(--radius-sharp-sm);
    text-decoration: none; border: none; cursor: pointer; transition: transform .15s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
    white-space: nowrap;
}
.al-btn--primary { background: var(--al-gradient); color: #fff; box-shadow: 0 8px 24px rgba(110,7,7,.35); }
.al-btn--primary:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(110,7,7,.42); color: #fff; }
.al-btn--outline { background: transparent; color: #fff; border: 1.5px solid rgba(255,255,255,.3); }
.al-btn--outline:hover { border-color: #fff; background: rgba(255,255,255,.08); color: #fff; }
.al-btn--light { background: #fff; color: var(--al-bordo); }
.al-btn--light:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(0,0,0,.25); color: var(--al-bordo); }
.al-btn-row { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }

.al-section { padding: 5rem 0; }
.al-section--alt { background: #f9fafb; }
.al-container--narrow { max-width: 900px; margin: 0 auto; }

.al-h2 { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; color: #111; text-align: center; margin-bottom: .75rem; }
.al-lede { font-size: 1.05rem; color: #6b7280; text-align: center; max-width: 560px; margin: 0 auto 3rem; line-height: 1.7; }

.al-grid { display: grid; gap: 1.75rem; }
.al-grid--4 { grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); }

.al-card { background: #fff; border: 1px solid #e5e7eb; border-radius: var(--radius-sharp-md); padding: 2rem; box-shadow: 0 2px 14px rgba(17,17,17,.06); }
.al-card__icon {
    width: 52px; height: 52px; border-radius: var(--radius-sharp-sm);
    background: var(--al-gradient); color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; margin-bottom: 1.25rem;
}
.al-card__title { font-size: 1.1rem; font-weight: 700; color: #111; margin-bottom: .6rem; }
.al-card__desc { font-size: .92rem; color: #6b7280; line-height: 1.65; margin: 0; }

.al-steps { background: #fff; border: 1px solid #e5e7eb; border-radius: var(--radius-sharp-md); box-shadow: 0 2px 14px rgba(17,17,17,.06); }
.al-step { display: flex; gap: 1.25rem; padding: 1.75rem 2rem; border-bottom: 1px solid #e5e7eb; }
.al-step:last-child { border-bottom: none; }
.al-step__num {
    flex-shrink: 0; width: 48px; height: 48px; border-radius: var(--radius-sharp-sm);
    background: var(--al-gradient); color: #fff; font-weight: 800; font-size: 1.05rem;
    display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 16px rgba(110,7,7,.28);
}
.al-step__title { font-size: 1.08rem; font-weight: 700; color: #111; margin: 0 0 .35rem; }
.al-step__desc { color: #6b7280; line-height: 1.65; margin: 0; font-size: .95rem; }

.al-commission {
    background: var(--al-gradient); border-radius: var(--radius-sharp-md);
    padding: 3.5rem 2rem; text-align: center; color: #fff;
    box-shadow: 0 20px 50px rgba(110,7,7,.3);
}
.al-commission__value { font-size: clamp(3rem, 7vw, 4.5rem); font-weight: 900; line-height: 1; letter-spacing: -.02em; }
.al-commission__label { font-size: 1.1rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; margin: .5rem 0 1.25rem; opacity: .95; }
.al-commission__note { font-size: .9rem; color: rgba(255,255,255,.8); max-width: 480px; margin: 0 auto; line-height: 1.6; }

.al-panel-list { display: flex; flex-direction: column; gap: .85rem; }
.al-panel-item {
    display: flex; align-items: center; gap: 1rem;
    background: #fff; border: 1px solid #e5e7eb; border-radius: var(--radius-sharp-sm);
    padding: 1.1rem 1.4rem; font-weight: 700; color: #111; font-size: 1rem;
}
.al-panel-item i { color: var(--al-bordo); font-size: 1.3rem; flex-shrink: 0; }

.al-cta-band {
    background: #111; border-radius: var(--radius-sharp-md);
    padding: 3.5rem 2.5rem; text-align: center; color: #fff;
}
.al-cta-band p { color: rgba(255,255,255,.75); max-width: 520px; margin: 0 auto 2rem; }
.al-cta-login { font-size: .88rem; color: rgba(255,255,255,.6); margin: 1.5rem 0 0; }
.al-cta-login a { color: #fff; font-weight: 700; text-decoration: underline; }
.al-cta-login a:hover { color: rgba(255,255,255,.85); }

@media (max-width: 768px) {
    .al-hero { padding: 4.5rem 0 3.5rem; }
    .al-section { padding: 3.25rem 0; }
    .al-step { padding: 1.5rem; }
    .al-commission { padding: 2.5rem 1.5rem; }
    .al-cta-band { padding: 2.5rem 1.5rem; }
    .al-btn-row, .al-hero__actions { flex-direction: column; }
    .al-btn-row .al-btn, .al-hero__actions .al-btn { width: 100%; }
}
</style>
@endpush
