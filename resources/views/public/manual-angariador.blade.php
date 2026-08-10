@extends('frontend.partials.layout')

@section('title', 'Manual do Angariador - Izzycar')
@section('meta_description', 'Guia completo do programa de angariadores Izzycar: como apresentar o serviço, como funciona o teu link pessoal e como e quando recebes a tua comissão.')
@section('robots', 'noindex, follow')

@section('content')

<section class="manual-page">
    <div class="manual-page__inner">

        <div class="manual-hero">
            <h1 class="manual-hero__title">Manual do Angariador</h1>
            <p class="manual-hero__subtitle">Guia completo do programa de angariadores Izzycar</p>
        </div>

        <div class="manual-accordion">

            {{-- ═══════════ COMECE AQUI ═══════════ --}}
            <details class="manual-group" open>
                <summary class="manual-group__summary">
                    <h2 class="manual-group__title">Comece Aqui</h2>
                    <span class="material-symbols-outlined manual-group__chevron">expand_more</span>
                </summary>
                <div class="manual-group__body">

                    <section class="manual-topic-block">
                        <h3 class="manual-topic-block__title">
                            <span class="material-symbols-outlined">waving_hand</span>
                            Bem-vindo à Izzycar
                        </h3>
                        <p>Este manual explica tudo o que precisas de saber para trabalhar como <strong>angariador Izzycar</strong>: como apresentar o serviço, como funciona o teu link pessoal, como acompanhar as tuas leads dentro da plataforma e como e quando recebes a tua comissão.</p>
                        <p>Como angariador, o teu papel é <strong>encontrar pessoas interessadas em importar um carro e colocá-las em contacto com a Izzycar através do teu link pessoal</strong>. A partir daí, a equipa Izzycar trata de todo o processo — pesquisa, negociação, transporte, legalização e entrega. A ti cabe angariar, acompanhar o contacto com a lead e não a deixar esfriar.</p>
                    </section>

                    <section class="manual-topic-block">
                        <h3 class="manual-topic-block__title">
                            <span class="material-symbols-outlined">campaign</span>
                            Como Apresentar a Izzycar
                        </h3>
                        <p>Quando falares com um potencial cliente, usa sempre a mesma mensagem — já testada e pronta a usar. Podes adaptar o tom (WhatsApp, email, conversa presencial), mas <strong>mantém a informação sobre o serviço e o valor sempre igual</strong>, para que não haja expectativas erradas.</p>

                        <div class="manual-quote-box">
                            <span class="manual-quote-box__label">Texto de Apresentação</span>
                            <p>"Trabalho com um serviço completo de importação automóvel, acompanhado por mim do início ao fim, para que todo o processo seja simples e transparente.</p>
                            <p>O custo da importação varia consoante o veículo e a localização, mas o serviço inclui sempre: verificação do histórico, transporte, IPO, legalização completa e entrega. O valor ronda, em média, os <strong>2.500 €</strong>, acrescendo ISV e IUC apenas quando aplicável.</p>
                            <p>Se fizer sentido, posso enviar-lhe uma proposta sem compromisso. Basta preencher este formulário: [O TEU LINK]"</p>
                        </div>

                        <div class="manual-callout">
                            <span class="material-symbols-outlined manual-callout__icon">warning</span>
                            <div>
                                <h4 class="manual-callout__title">Regras Importantes</h4>
                                <ul>
                                    <li><strong>Nunca garantas um preço final</strong> antes de a Izzycar enviar a proposta.</li>
                                    <li><strong>Nunca inventes informações</strong> sobre prazos ou garantias.</li>
                                    <li>O preenchimento do formulário <strong>não é um compromisso de compra</strong>.</li>
                                    <li>A negociação final é feita pela equipa Izzycar, nunca por ti.</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <section class="manual-topic-block">
                        <h3 class="manual-topic-block__title">
                            <span class="material-symbols-outlined">link</span>
                            O Teu Link de Angariador
                        </h3>
                        <p>Cada angariador tem um <strong>código pessoal e único</strong>. Esse código garante que qualquer lead que chegue através de ti fica associada a ti.</p>

                        <div class="manual-link-box">
                            <span class="manual-link-box__label">O Teu Link (exemplo)</span>
                            <code class="manual-link-box__code">https://izzycar.pt/formulario-importacao?angariador=O_TEU_CODIGO</code>
                        </div>

                        <div class="manual-callout manual-callout--gold">
                            <span class="material-symbols-outlined manual-callout__icon">star</span>
                            <div>
                                <h4 class="manual-callout__title">Regra de Ouro</h4>
                                <p>Deves <strong>sempre</strong> enviar o formulário através deste link. Se enviares o link errado (sem o teu código), a lead não fica associada a ti e não gera comissão.</p>
                            </div>
                        </div>

                        <p class="manual-topic-block__note">Quando alguém abre o teu link, o código fica guardado 30 dias — mesmo que a pessoa não preencha o formulário de imediato. Se o link de outro angariador já tiver sido aberto antes do teu, a lead fica associada ao <strong>primeiro</strong>.</p>
                    </section>

                </div>
            </details>

            {{-- ═══════════ COMO FUNCIONA ═══════════ --}}
            <details class="manual-group">
                <summary class="manual-group__summary">
                    <h2 class="manual-group__title">Como Funciona</h2>
                    <span class="material-symbols-outlined manual-group__chevron">expand_more</span>
                </summary>
                <div class="manual-group__body">

                    <section class="manual-topic-block">
                        <h3 class="manual-topic-block__title">Como a Lead é Criada</h3>
                        <ol>
                            <li>É criado um registo na plataforma, com o teu código associado.</li>
                            <li>O cliente recebe automaticamente um email de confirmação.</li>
                            <li>A equipa Izzycar é notificada e começa a tratar o pedido.</li>
                            <li>A lead aparece de imediato em <strong>As Minhas Leads</strong>.</li>
                        </ol>
                        <div class="manual-callout manual-callout--info">
                            <span class="material-symbols-outlined manual-callout__icon">info</span>
                            <p>Se a pessoa já for cliente, o sistema não cria duplicados. A administração analisa manualmente e decide a atribuição.</p>
                        </div>
                    </section>

                    <section class="manual-topic-block">
                        <h3 class="manual-topic-block__title">O Meu Painel</h3>
                        <p>A tua página inicial mostra um resumo de tudo o que precisas:</p>
                        <ul class="manual-feature-list">
                            <li><span class="material-symbols-outlined">link</span> O teu link de angariador</li>
                            <li><span class="material-symbols-outlined">group</span> Leads Geradas &amp; Convertidas</li>
                            <li><span class="material-symbols-outlined">payments</span> Comissão Pendente &amp; Recebida</li>
                        </ul>
                        <figure class="manual-screenshot">
                            <img src="{{ asset('img/manual/painel.png') }}" alt="O Meu Painel do angariador">
                        </figure>
                    </section>

                    <section class="manual-topic-block">
                        <h3 class="manual-topic-block__title">As Minhas Leads</h3>
                        <p>Lista todas as pessoas que preencheram o formulário através do teu link. O tratamento comercial (pesquisa, negociação, proposta) é sempre feito pela equipa Izzycar — não pelo angariador.</p>
                        <figure class="manual-screenshot">
                            <img src="{{ asset('img/manual/leads.png') }}" alt="Lista de leads do angariador">
                        </figure>
                    </section>

                    <section class="manual-topic-block">
                        <h3 class="manual-topic-block__title">Contactos e Follow-ups</h3>
                        <p>Dentro de cada lead, regista cada contacto (chamada, WhatsApp, email...) na Timeline &amp; Notas, e agenda sempre o próximo follow-up quando um assunto fica em aberto. Recebes um email automático no momento exato do follow-up agendado.</p>
                        <figure class="manual-screenshot">
                            <img src="{{ asset('img/manual/contactos.png') }}" alt="Timeline e follow-up de uma lead">
                        </figure>
                    </section>

                </div>
            </details>

            {{-- ═══════════ PROPOSTAS & COMISSÕES ═══════════ --}}
            <details class="manual-group">
                <summary class="manual-group__summary">
                    <h2 class="manual-group__title">Propostas &amp; Comissões</h2>
                    <span class="material-symbols-outlined manual-group__chevron">expand_more</span>
                </summary>
                <div class="manual-group__body">

                    <section class="manual-topic-block">
                        <h3 class="manual-topic-block__title">Propostas</h3>
                        <p>Quando a equipa Izzycar envia uma proposta a uma das tuas leads, ela aparece aqui — com o mesmo documento/link que o cliente recebeu. Não vês valores internos como margens ou custos de compra.</p>
                    </section>

                    <section class="manual-topic-block">
                        <h3 class="manual-topic-block__title">Comissões</h3>
                        <p>A tua comissão tem um <strong>valor fixo</strong> (referência de 100 € por proposta convertida, ajustável caso a caso). É gerada automaticamente quando uma lead aceita a proposta.</p>
                        <div class="manual-info-box">
                            <h4 class="manual-info-box__title">Quando é Paga</h4>
                            <ul>
                                <li>Fica <strong>pendente</strong> desde a aceitação da proposta.</li>
                                <li>É paga quando o processo chega ao estado <strong>"Entrega"</strong>.</li>
                                <li>Pagamento efetuado até <strong>48 horas</strong> após a entrega.</li>
                            </ul>
                        </div>
                        <figure class="manual-screenshot">
                            <img src="{{ asset('img/manual/comissoes.png') }}" alt="Página de comissões do angariador">
                        </figure>
                    </section>

                    <section class="manual-topic-block">
                        <h3 class="manual-topic-block__title">Formulários</h3>
                        <p>Lista todos os pedidos de importação submetidos através do teu link — o registo em bruto de cada formulário preenchido.</p>
                        <figure class="manual-screenshot">
                            <img src="{{ asset('img/manual/formularios.png') }}" alt="Formulários recebidos através do link">
                        </figure>
                    </section>

                </div>
            </details>

            {{-- ═══════════ REGRAS ═══════════ --}}
            <details class="manual-group">
                <summary class="manual-group__summary">
                    <h2 class="manual-group__title">Deveres &amp; Regras</h2>
                    <span class="material-symbols-outlined manual-group__chevron">expand_more</span>
                </summary>
                <div class="manual-group__body">
                    <ol class="manual-rules-list">
                        <li><strong>Usar sempre o teu link pessoal</strong> ao partilhar o formulário.</li>
                        <li><strong>Apresentar a Izzycar</strong> exatamente como descrito neste manual.</li>
                        <li><strong>Registar todos os contactos</strong> com cada lead na Timeline &amp; Notas.</li>
                        <li><strong>Agendar sempre um follow-up</strong> quando um assunto fica em aberto.</li>
                        <li><strong>Não tratar comercialmente</strong> a lead por conta própria.</li>
                        <li><strong>Respeitar a confidencialidade</strong> dos dados dos clientes.</li>
                    </ol>
                </div>
            </details>

        </div>

        <div class="manual-cta">
            <span class="material-symbols-outlined manual-cta__icon">handshake</span>
            <h2 class="manual-cta__title">Quero Ser Angariador Izzycar</h2>
            <p class="manual-cta__text">Se leste este manual e queres começar a angariar para a Izzycar, candidata-te — a administração analisa o teu registo e ativa a tua conta.</p>
            <a href="{{ route('public.angariador.register') }}" class="manual-cta__btn">
                Candidatar-me a Angariador
                <span class="material-symbols-outlined">arrow_forward</span>
            </a>
        </div>

    </div>
</section>

@endsection

@push('styles')
<style>
.manual-page {
    background: #0A0A0A;
    padding: 3rem 0 5rem;
}
.manual-page__inner {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 20px;
}
.manual-hero {
    text-align: center;
    margin-bottom: 3rem;
}
.manual-hero__title {
    font-family: var(--title-font-family);
    font-size: 2.25rem;
    font-weight: 700;
    color: #990000;
    margin-bottom: .75rem;
}
.manual-hero__subtitle {
    color: rgba(255,255,255,0.65);
    font-size: 1.05rem;
}

.manual-accordion {
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.manual-group {
    background: #1c1b1b;
    border-radius: 8px;
    overflow: hidden;
}
.manual-group__summary {
    list-style: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    background: #201f1f;
    transition: background .2s;
}
.manual-group__summary::-webkit-details-marker { display: none; }
.manual-group__summary:hover { background: #262525; }
.manual-group__title {
    font-family: var(--title-font-family);
    font-size: 1.15rem;
    font-weight: 700;
    color: #cf1c1c;
    margin: 0;
}
.manual-group__chevron {
    color: #990000;
    transition: transform .3s;
}
.manual-group[open] .manual-group__chevron { transform: rotate(180deg); }
.manual-group__body {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.manual-topic-block__title {
    font-family: var(--title-font-family);
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: .75rem;
}
.manual-topic-block__title .material-symbols-outlined { color: #990000; }
.manual-topic-block p,
.manual-topic-block li {
    color: rgba(255,255,255,0.75);
    font-size: .92rem;
    line-height: 1.7;
}
.manual-topic-block p { margin-bottom: .75rem; }
.manual-topic-block ol,
.manual-topic-block ul { padding-left: 1.3rem; margin-bottom: .75rem; }
.manual-topic-block strong { color: #fff; }
.manual-topic-block__note {
    font-size: .85rem;
    color: rgba(255,255,255,0.55);
}

.manual-quote-box {
    background: #131313;
    border: 1px solid #353534;
    border-radius: 6px;
    padding: 1.1rem 1.25rem;
    margin-bottom: 1.25rem;
    position: relative;
}
.manual-quote-box__label {
    display: inline-block;
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: rgba(255,255,255,0.5);
    background: #131313;
    padding: 0 .4rem;
    position: absolute;
    top: -.6rem;
    left: 1rem;
}
.manual-quote-box p { font-style: italic; color: rgba(255,255,255,0.7); font-size: .88rem; }
.manual-quote-box p:last-child { margin-bottom: 0; }

.manual-callout {
    border: 1px solid #990000;
    background: rgba(153,0,0,0.08);
    border-radius: 6px;
    padding: 1.1rem 1.25rem;
    display: flex;
    gap: .9rem;
    margin-bottom: 1.25rem;
}
.manual-callout--gold {
    border-color: #d4af37;
    background: rgba(212,175,55,0.08);
}
.manual-callout--info {
    border-color: #454545;
    background: rgba(255,255,255,0.03);
    align-items: center;
}
.manual-callout__icon {
    color: #990000;
    flex-shrink: 0;
}
.manual-callout--gold .manual-callout__icon { color: #d4af37; }
.manual-callout--info .manual-callout__icon { color: rgba(255,255,255,0.5); }
.manual-callout__title {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #cf1c1c;
    font-weight: 700;
    margin-bottom: .5rem;
}
.manual-callout--gold .manual-callout__title { color: #d4af37; }
.manual-callout ul,
.manual-callout p { margin-bottom: 0; }

.manual-link-box {
    background: #131313;
    border: 1px solid #353534;
    border-radius: 6px;
    padding: 1rem 1.1rem;
    margin-bottom: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.manual-link-box__label {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: rgba(255,255,255,0.5);
}
.manual-link-box__code {
    color: #cf1c1c;
    background: #201f1f;
    padding: .6rem .75rem;
    border-radius: 4px;
    font-size: .82rem;
    word-break: break-all;
}

.manual-feature-list {
    list-style: none;
    padding-left: 0;
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.manual-feature-list li {
    display: flex;
    align-items: center;
    gap: .75rem;
    background: #131313;
    border: 1px solid #2a2a2a;
    border-radius: 6px;
    padding: .65rem .9rem;
    color: #fff !important;
}
.manual-feature-list .material-symbols-outlined { color: #990000; font-size: 20px; }

.manual-info-box {
    background: #131313;
    border: 1px solid #353534;
    border-radius: 6px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
}
.manual-info-box__title {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #cf1c1c;
    font-weight: 700;
    margin-bottom: .6rem;
}
.manual-info-box ul { margin-bottom: 0; }

.manual-screenshot { margin-top: 1rem; }
.manual-screenshot img {
    width: 100%;
    border-radius: 6px;
    border: 1px solid #2a2a2a;
    display: block;
}

.manual-rules-list {
    padding-left: 1.3rem;
    display: flex;
    flex-direction: column;
    gap: .9rem;
}
.manual-rules-list li { color: rgba(255,255,255,0.75); font-size: .92rem; line-height: 1.6; }
.manual-rules-list strong { color: #fff; }

.manual-cta {
    margin-top: 3rem;
    background: #1c1b1b;
    border: 1px solid rgba(153,0,0,0.3);
    border-radius: 8px;
    padding: 2.5rem 2rem;
    text-align: center;
}
.manual-cta__icon { font-size: 3rem; color: #990000; margin-bottom: 1rem; }
.manual-cta__title {
    font-family: var(--title-font-family);
    font-size: 1.4rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: .75rem;
}
.manual-cta__text {
    color: rgba(255,255,255,0.65);
    font-size: .92rem;
    max-width: 480px;
    margin: 0 auto 1.5rem;
}
.manual-cta__btn {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    background: linear-gradient(135deg, #990000 0%, #6e0707 100%);
    color: #fff;
    font-weight: 700;
    padding: .95rem 2rem;
    text-decoration: none;
    font-size: .95rem;
}
</style>
@endpush
