@php $publicView = $publicView ?? false; @endphp

<div class="row g-4" id="manualTop">

    {{-- ═══════════════════════════════════════
         SIDEBAR — Índice
    ═══════════════════════════════════════ --}}
    <div class="col-lg-3">
        <div class="modern-card manual-sidebar sticky-top" style="top: 80px;">
            <div class="modern-card-header">
                <h6 class="modern-card-title mb-0"><i class="bi bi-list-ul me-1"></i> Índice</h6>
            </div>
            <nav class="manual-nav p-2">
                @php
                $sectionGroups = [
                    'Comece Aqui' => [
                        ['id' => 'bem-vindo',      'icon' => 'bi-hand-thumbs-up',    'label' => 'Bem-vindo'],
                        ['id' => 'apresentacao',   'icon' => 'bi-megaphone',         'label' => 'Apresentar a Izzycar'],
                        ['id' => 'link',           'icon' => 'bi-link-45deg',        'label' => 'O Teu Link'],
                    ],
                    'Como Funciona' => [
                        ['id' => 'lead-criada',    'icon' => 'bi-inbox',             'label' => 'Como a Lead é Criada'],
                        ['id' => 'painel',         'icon' => 'bi-speedometer2',      'label' => 'O Meu Painel'],
                        ['id' => 'leads',          'icon' => 'bi-funnel',            'label' => 'As Minhas Leads'],
                        ['id' => 'contactos',      'icon' => 'bi-clock-history',     'label' => 'Contactos e Follow-ups'],
                    ],
                    'Propostas & Comissões' => [
                        ['id' => 'propostas',      'icon' => 'bi-file-earmark-text', 'label' => 'Propostas'],
                        ['id' => 'comissoes',      'icon' => 'bi-cash-coin',         'label' => 'Comissões'],
                        ['id' => 'formularios',    'icon' => 'bi-envelope-open',     'label' => 'Formulários'],
                    ],
                    'Regras' => [
                        ['id' => 'visibilidade',   'icon' => 'bi-eye',               'label' => 'O Que Podes Ver'],
                        ['id' => 'deveres',        'icon' => 'bi-clipboard-check',   'label' => 'Os Teus Deveres'],
                    ],
                ];
                if ($publicView) {
                    $sectionGroups['Junte-se a Nós'] = [
                        ['id' => 'quero-ser-angariador', 'icon' => 'bi-person-plus', 'label' => 'Quero Ser Angariador'],
                    ];
                }
                @endphp
                @foreach($sectionGroups as $groupLabel => $items)
                <div class="manual-nav__group-title">{{ $groupLabel }}</div>
                @foreach($items as $s)
                <a href="#{{ $s['id'] }}" class="manual-nav__item">
                    <i class="bi {{ $s['icon'] }}"></i>
                    {{ $s['label'] }}
                </a>
                @endforeach
                @endforeach
            </nav>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         CONTEÚDO
    ═══════════════════════════════════════ --}}
    <div class="col-lg-9">

        {{-- ── BEM-VINDO ── --}}
        <div class="manual-section modern-card mb-4" id="bem-vindo">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-hand-thumbs-up"></i> Bem-vindo à Izzycar</h4>
            </div>
            <div class="manual-section__body">
                <p>Este manual explica tudo o que precisas de saber para trabalhar como <strong>angariador Izzycar</strong>: como apresentar o serviço, como funciona o teu link pessoal, como acompanhar as tuas leads dentro da plataforma e como e quando recebes a tua comissão.</p>
                <p>Como angariador, o teu papel é <strong>encontrar pessoas interessadas em importar um carro e colocá-las em contacto com a Izzycar através do teu link pessoal</strong>. A partir daí, a equipa Izzycar trata de todo o processo — pesquisa, negociação, transporte, legalização e entrega. A ti cabe angariar, acompanhar o contacto com a lead e não a deixar esfriar.</p>
                @if(!$publicView)
                <div class="alert alert-info py-2 small">
                    <i class="bi bi-lightbulb me-1"></i>
                    <strong>Dica:</strong> Guarda este manual nos favoritos — está sempre acessível no menu lateral, em "Manual do Angariador".
                </div>
                @endif
            </div>
        </div>

        {{-- ── APRESENTAÇÃO ── --}}
        <div class="manual-section modern-card mb-4" id="apresentacao">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-megaphone"></i> Como Apresentar a Izzycar</h4>
            </div>
            <div class="manual-section__body">
                <p>Quando falares com um potencial cliente, usa sempre a mesma mensagem — já testada e pronta a usar. Podes adaptar o tom (WhatsApp, email, conversa presencial), mas <strong>mantém a informação sobre o serviço e o valor sempre igual</strong>, para que não haja expectativas erradas.</p>

                <h6 class="manual-topic">Texto de apresentação (usa como modelo)</h6>
                <blockquote class="manual-quote">
                    Trabalho com um serviço completo de importação automóvel, acompanhado por mim do início ao fim, para que todo o processo seja simples e transparente.<br><br>
                    O custo da importação varia consoante o veículo e a localização, mas o serviço inclui sempre:<br>
                    ✔️ Verificação do histórico e estado do veículo<br>
                    ✔️ Transporte para Portugal<br>
                    ✔️ Inspeção (IPO)<br>
                    ✔️ Legalização completa<br>
                    ✔️ Emissão de matrícula portuguesa<br>
                    ✔️ Entrega do veículo<br><br>
                    O valor deste serviço ronda, em média, os <strong>2.500 €</strong>, acrescendo ISV e IUC apenas quando aplicável.<br><br>
                    No final, envio sempre uma <strong>proposta chave na mão</strong>, com o valor total fechado, já incluindo o preço do carro e todos os custos legais, para que não existam surpresas.<br><br>
                    Se fizer sentido, posso realizar uma pesquisa personalizada e enviar-lhe uma proposta de importação sem qualquer compromisso.<br><br>
                    👉 Para isso, basta preencher este formulário: <strong>(o seu link pessoal — ver secção seguinte)</strong><br><br>
                    Assim que estiver preenchido, começo a procura e apresento-lhe as melhores propostas disponíveis.
                </blockquote>

                <h6 class="manual-topic">Regras importantes ao apresentar o serviço</h6>
                <ul>
                    <li><strong>Nunca garantas um preço final</strong> antes de a Izzycar enviar a proposta — o valor de ~2.500 € é uma média, não um preço fixo por carro.</li>
                    <li><strong>Nunca inventes informações</strong> sobre prazos, garantias ou condições que não estejam neste manual ou confirmadas pela Izzycar.</li>
                    <li>Deixa sempre claro que o preenchimento do formulário <strong>não é um compromisso de compra</strong> — é só o pedido de uma proposta.</li>
                    <li>Toda a negociação, pesquisa e proposta final é sempre feita pela equipa Izzycar, nunca por ti.</li>
                </ul>
            </div>
        </div>

        {{-- ── O TEU LINK ── --}}
        <div class="manual-section modern-card mb-4" id="link">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-link-45deg"></i> O Teu Link de Angariador</h4>
                @if(!$publicView)
                <a href="{{ route('admin.angariador.dashboard') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ver o meu link
                </a>
                @endif
            </div>
            <div class="manual-section__body">
                <p>Cada angariador tem um <strong>código pessoal e único</strong>. Esse código é o que garante que qualquer lead que chegue através de ti fica associada a ti — e é isso que determina a tua comissão.</p>

                <h6 class="manual-topic">Onde encontrar o teu link</h6>
                <p>O teu link está sempre disponível no topo de <strong>O Meu Painel</strong>, com um botão para copiar. Tem este formato:</p>
                <p><code>https://izzycar.pt/formulario-importacao?angariador=O_TEU_CODIGO</code></p>

                <div class="alert alert-danger py-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Regra de ouro:</strong> Deves <strong>sempre</strong> enviar o formulário através deste link, nunca o link genérico do site. Se enviares o link errado (sem o teu código), a lead não fica associada a ti e não gera comissão — mesmo que tenhas sido tu a angariá-la.
                </div>

                <h6 class="manual-topic">Como funciona por dentro</h6>
                <ul>
                    <li>Quando alguém abre o teu link, o código fica guardado no telemóvel/computador da pessoa durante 30 dias — mesmo que ela não preencha o formulário de imediato.</li>
                    <li>Se a mesma pessoa preencher o formulário mais tarde (dentro desses 30 dias), a lead continua associada a ti.</li>
                    <li>Se alguém já tiver aberto o link de outro angariador antes do teu, a lead fica associada ao <strong>primeiro</strong> angariador — não é possível "roubar" uma lead já atribuída.</li>
                </ul>
            </div>
        </div>

        {{-- ── COMO A LEAD É CRIADA ── --}}
        <div class="manual-section modern-card mb-4" id="lead-criada">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-inbox"></i> Como a Lead é Criada</h4>
            </div>
            <div class="manual-section__body">
                <p>A lead nasce sempre do <strong>formulário de importação</strong> preenchido pelo cliente através do teu link. Quando o cliente submete o formulário:</p>
                <ol>
                    <li>É criado um registo de lead na plataforma, com o teu código de angariador associado.</li>
                    <li>O cliente recebe automaticamente um email de confirmação do pedido.</li>
                    <li>A equipa Izzycar é notificada e começa a tratar o pedido.</li>
                    <li>A lead aparece de imediato em <strong>As Minhas Leads</strong>, no teu painel.</li>
                </ol>

                <h6 class="manual-topic">E se a pessoa já for cliente?</h6>
                <p>Se o email e o telefone preenchidos no formulário já corresponderem a um registo existente na Izzycar (por exemplo, a pessoa já tinha pedido uma proposta antes, através de outro angariador ou diretamente), o sistema <strong>não cria um registo duplicado</strong> — em vez disso, a administração é notificada automaticamente por email para analisar o caso manualmente e decidir como associar este novo pedido.</p>
                <div class="alert alert-info py-2 small">
                    <i class="bi bi-lightbulb me-1"></i>
                    Isto significa que, se angariares alguém que já é (ou já foi) cliente Izzycar por outra via, a atribuição desse caso específico pode ser decidida manualmente pela administração, e não é garantido que fique automaticamente associado a ti. Se isso acontecer, podes sempre confirmar com a administração.
                </div>
            </div>
        </div>

        {{-- ── O MEU PAINEL ── --}}
        <div class="manual-section modern-card mb-4" id="painel">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-speedometer2"></i> O Meu Painel</h4>
                @if(!$publicView)
                <a href="{{ route('admin.angariador.dashboard') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
                @endif
            </div>
            <div class="manual-section__body">
                <p>É a tua página inicial. Mostra um resumo rápido de tudo o que precisas de saber:</p>
                <ul>
                    <li><strong>O teu link de angariador</strong>, sempre pronto a copiar.</li>
                    <li><strong>Leads Geradas</strong> — quantas leads já trouxeste através do teu link.</li>
                    <li><strong>Leads Convertidas</strong> e <strong>Taxa de Conversão</strong>.</li>
                    <li><strong>Comissão Pendente</strong> e <strong>Comissão Recebida</strong>, com acesso rápido ao detalhe.</li>
                </ul>
            </div>
        </div>

        {{-- ── AS MINHAS LEADS ── --}}
        <div class="manual-section modern-card mb-4" id="leads">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-funnel"></i> As Minhas Leads</h4>
                @if(!$publicView)
                <a href="{{ route('admin.angariador.leads') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
                @endif
            </div>
            <div class="manual-section__body">
                <p>Lista todas as pessoas que preencheram o formulário através do teu link. Ao abrir uma lead, encontras os dados de contacto e o estado do processo.</p>

                <div class="alert alert-warning py-2 small">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Importante:</strong> o tratamento comercial da lead (pesquisa, negociação, proposta) é sempre feito pela equipa Izzycar — não pelo angariador. Enquanto não houver proposta enviada, verás apenas que a lead está a ser tratada, sem detalhes do processo interno.
                </div>
            </div>
        </div>

        {{-- ── CONTACTOS E FOLLOW-UPS ── --}}
        <div class="manual-section modern-card mb-4" id="contactos">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-clock-history"></i> Registar Contactos e Agendar Follow-ups</h4>
            </div>
            <div class="manual-section__body">
                <p>Dentro de cada lead, tens duas ferramentas essenciais para não perder o fio à meada:</p>

                <h6 class="manual-topic">Timeline &amp; Notas — registar cada contacto</h6>
                <p>Sempre que falares com a lead — telefonema, WhatsApp, email, Facebook, reunião ou apenas uma nota — <strong>regista aqui</strong>. Escolhe o tipo de contacto, escreve um resumo curto (ex: "Liguei, ainda está a comparar preços") e, se quiseres, um detalhe adicional. Isto cria um histórico completo, visível só por ti, com data e hora de cada interação.</p>

                <h6 class="manual-topic">Follow-up — agendar o próximo contacto</h6>
                <p>Sempre que terminares uma conversa sem fechar o assunto, <strong>agenda logo o próximo follow-up</strong> — data, hora e uma nota do que ficou acordado (ex: "Confirmar se já decidiu o modelo"). Isto evita que a lead "esfrie" por falta de acompanhamento.</p>

                <div class="alert alert-success py-2 small">
                    <i class="bi bi-envelope-check me-1"></i>
                    <strong>Recebes um email automático</strong> no exato momento em que o follow-up estiver agendado para acontecer — assim não te esqueces de contactar o cliente, mesmo sem estares a olhar para a plataforma.
                </div>

                <h6 class="manual-topic">Boas práticas</h6>
                <ul>
                    <li>Regista <strong>todos</strong> os contactos, mesmo os que não tiveram resposta ("Liguei, não atendeu").</li>
                    <li>Nunca deixes uma lead sem um follow-up agendado enquanto o assunto não estiver fechado.</li>
                    <li>Usa notas curtas e objetivas — a equipa Izzycar também pode consultar este histórico.</li>
                </ul>
            </div>
        </div>

        {{-- ── PROPOSTAS ── --}}
        <div class="manual-section modern-card mb-4" id="propostas">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-file-earmark-text"></i> Propostas</h4>
                @if(!$publicView)
                <a href="{{ route('admin.angariador.propostas') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
                @endif
            </div>
            <div class="manual-section__body">
                <p>Quando a equipa Izzycar envia uma proposta a uma das tuas leads, ela aparece aqui — com o mesmo documento/link que o cliente recebeu, e o estado (enviada, aceite, etc.).</p>
                <p>Podes abrir a proposta para ver exatamente o que o cliente está a ver, mas <strong>não vês valores internos</strong> como margens ou custos de compra — apenas o que já está no documento entregue ao cliente.</p>
            </div>
        </div>

        {{-- ── COMISSÕES ── --}}
        <div class="manual-section modern-card mb-4" id="comissoes">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-cash-coin"></i> Comissões</h4>
                @if(!$publicView)
                <a href="{{ route('admin.angariador.comissoes') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
                @endif
            </div>
            <div class="manual-section__body">
                <p>A tua comissão tem um <strong>valor fixo</strong>, definido pela administração no teu perfil (não depende do preço do carro). É gerada automaticamente quando uma das tuas leads aceita uma proposta.</p>

                <h6 class="manual-topic">Quando é paga</h6>
                <ul>
                    <li>A comissão fica <strong>pendente</strong> desde a aceitação da proposta.</li>
                    <li>É paga quando o processo chega ao estado <strong>"Entrega"</strong> (o carro é entregue ao cliente).</li>
                    <li>A administração tem até <strong>24 horas</strong> após esse momento para efetuar o pagamento — se ultrapassar esse prazo sem ser marcada como paga, o registo é assinalado como "em atraso" para a administração.</li>
                </ul>

                <h6 class="manual-topic">Comprovativo</h6>
                <p>Quando a comissão é marcada como paga, a administração pode anexar um comprovativo de transferência. Se existir, verás um pequeno ícone de clip junto à data de pagamento — podes clicar para consultar.</p>
            </div>
        </div>

        {{-- ── FORMULÁRIOS ── --}}
        <div class="manual-section modern-card mb-4" id="formularios">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-envelope-open"></i> Formulários</h4>
                @if(!$publicView)
                <a href="{{ route('admin.angariador.formularios') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
                @endif
            </div>
            <div class="manual-section__body">
                <p>Lista todos os pedidos de importação submetidos através do teu link — o registo em bruto de cada formulário preenchido, incluindo os detalhes do veículo pretendido.</p>
            </div>
        </div>

        {{-- ── O QUE PODES VER ── --}}
        <div class="manual-section modern-card mb-4" id="visibilidade">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-eye"></i> O Que Podes e Não Podes Ver</h4>
            </div>
            <div class="manual-section__body">
                <h6 class="manual-topic">Podes ver</h6>
                <ul>
                    <li>Todas as leads geradas através do teu link, os dados de contacto delas e o histórico que tu próprio registaste.</li>
                    <li>As propostas enviadas às tuas leads — o documento final, igual ao que o cliente recebe.</li>
                    <li>O estado geral do processo (enviado, aceite, etc.) das tuas leads.</li>
                    <li>As tuas comissões — valor, estado (pendente/paga) e comprovativo, quando existir.</li>
                </ul>

                <h6 class="manual-topic">Não podes ver</h6>
                <ul>
                    <li>Margens, custos de compra ou qualquer valor interno da Izzycar.</li>
                    <li>Detalhes do processo interno de uma lead antes de existir proposta enviada.</li>
                    <li>Leads, propostas ou comissões de outros angariadores.</li>
                    <li>Qualquer módulo do backoffice fora da tua área — o acesso está limitado apenas ao que está descrito neste manual.</li>
                </ul>
            </div>
        </div>

        {{-- ── OS TEUS DEVERES ── --}}
        <div class="manual-section modern-card mb-4" id="deveres">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-clipboard-check"></i> Os Teus Deveres como Angariador Izzycar</h4>
            </div>
            <div class="manual-section__body">
                <ol>
                    <li><strong>Usar sempre o teu link pessoal</strong> ao partilhar o formulário de importação — nunca o link genérico do site.</li>
                    <li><strong>Apresentar a Izzycar</strong> exatamente como descrito na secção "Como Apresentar a Izzycar" deste manual, sem inventar preços, prazos ou condições.</li>
                    <li><strong>Registar todos os contactos</strong> com cada lead na Timeline &amp; Notas, mesmo sem resposta.</li>
                    <li><strong>Agendar sempre um follow-up</strong> quando um assunto fica em aberto, para nunca deixar uma lead esquecida.</li>
                    <li><strong>Não tratar comercialmente</strong> a lead por conta própria (negociação, propostas de valor, promessas) — esse trabalho é sempre da equipa Izzycar.</li>
                    <li><strong>Respeitar a confidencialidade</strong> dos dados dos clientes e das informações a que tens acesso na plataforma.</li>
                    <li><strong>Comunicar com a administração</strong> sempre que surgir uma dúvida ou uma situação fora do previsto neste manual (ex: uma lead sinalizada como duplicada).</li>
                </ol>
            </div>
        </div>

        @if($publicView)
        <div class="manual-section modern-card mb-4" id="quero-ser-angariador">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title"><i class="bi bi-person-plus"></i> Quero Ser Angariador Izzycar</h4>
            </div>
            <div class="manual-section__body text-center">
                <p>Se leste este manual e queres começar a angariar para a Izzycar, candidata-te — a administração analisa o teu registo e ativa a tua conta.</p>
                <a href="{{ route('public.angariador.register') }}" class="btn btn-primary-modern">
                    <i class="bi bi-person-plus me-1"></i> Candidatar-me a Angariador
                </a>
            </div>
        </div>
        @endif

        <div class="text-center text-muted small py-3">
            <a href="#manualTop" class="text-muted"><i class="bi bi-arrow-up-circle me-1"></i>Voltar ao topo</a>
        </div>

    </div>{{-- /col-lg-9 --}}
</div>

@push('styles')
<style>
.manual-sidebar { border: none; }
.manual-nav { display: flex; flex-direction: column; gap: .15rem; }
.manual-nav__group-title {
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #bbb;
    padding: .75rem .75rem .25rem;
}
.manual-nav__group-title:first-child { padding-top: .25rem; }
.manual-nav__item {
    display: flex; align-items: center; gap: .6rem;
    padding: .45rem .75rem; border-radius: 6px;
    font-size: .83rem; color: #555; text-decoration: none;
    transition: background .15s, color .15s;
}
.manual-nav__item:hover,
.manual-nav__item.active { background: #f3f3f3; color: var(--admin-primary, #c00); }
.manual-nav__item i { font-size: .9rem; flex-shrink: 0; color: #aaa; }
.manual-nav__item:hover i,
.manual-nav__item.active i { color: var(--admin-primary, #c00); }

.manual-section__header {
    display: flex; align-items: center;
    justify-content: space-between; flex-wrap: wrap; gap: .5rem;
}
.manual-section__title {
    font-size: 1.1rem; font-weight: 700; margin: 0;
    display: flex; align-items: center; gap: .5rem;
    color: #222;
}
.manual-section__title i { color: var(--admin-primary, #c00); }
.manual-section__body { padding: 1.25rem; font-size: .88rem; line-height: 1.7; }
.manual-section__body p { margin-bottom: .75rem; color: #444; }
.manual-section__body ul,
.manual-section__body ol { padding-left: 1.4rem; color: #444; margin-bottom: .75rem; }
.manual-section__body li { margin-bottom: .3rem; }

.manual-topic {
    font-size: .78rem; text-transform: uppercase; letter-spacing: .06em;
    color: #aaa; font-weight: 700; margin: 1.1rem 0 .5rem;
    padding-bottom: .3rem; border-bottom: 1px solid #f0f0f0;
}

.manual-quote {
    background: #fafafa; border-left: 3px solid var(--admin-primary, #c00);
    padding: 1rem 1.25rem; margin: .5rem 0 1rem; font-size: .85rem;
    color: #333; border-radius: 0 8px 8px 0;
}

.manual-nav__item.is-active {
    background: rgba(var(--admin-primary-rgb, 180,0,0), .08);
    color: var(--admin-primary, #c00);
    font-weight: 600;
}
.manual-nav__item.is-active i { color: var(--admin-primary, #c00); }
</style>
@endpush

@push('scripts')
<script>
(function() {
    const navItems = document.querySelectorAll('.manual-nav__item');
    const sections = document.querySelectorAll('.manual-section');

    function onScroll() {
        let current = '';
        sections.forEach(sec => {
            if (window.scrollY >= sec.offsetTop - 120) current = sec.id;
        });
        navItems.forEach(a => {
            const href = a.getAttribute('href').replace('#', '');
            a.classList.toggle('is-active', href === current);
        });
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();
</script>
@endpush