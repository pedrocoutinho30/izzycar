@extends('layouts.admin-v2')

@section('title', 'Manual de Utilizador')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => '', 'label' => 'Manual de Utilizador']
    ],
    'title'       => 'Manual de Utilizador',
    'subtitle'    => 'Guia completo do back-office Izzycar — consulte sempre que tiver dúvidas',
    'actionHref'  => '',
    'actionLabel' => ''
])

<div class="row g-4" id="manualTop">

    {{-- ═══════════════════════════════════════
         SIDEBAR — Índice de módulos
    ═══════════════════════════════════════ --}}
    <div class="col-lg-3">
        <div class="modern-card manual-sidebar sticky-top" style="top: 80px;">
            <div class="modern-card-header">
                <h6 class="modern-card-title mb-0"><i class="bi bi-list-ul me-1"></i> Índice</h6>
            </div>
            <nav class="manual-nav p-2">
                @php
                $sections = [
                    ['id' => 'dashboard',           'icon' => 'bi-speedometer2',       'label' => 'Dashboard'],
                    ['id' => 'formularios',         'icon' => 'bi-inbox',              'label' => 'Formulários de Importação'],
                    ['id' => 'leads',               'icon' => 'bi-funnel',             'label' => 'Leads'],
                    ['id' => 'cotacoes',            'icon' => 'bi-file-earmark-text',  'label' => 'Cotações'],
                    ['id' => 'cotacoes-convertidas','icon' => 'bi-check2-all',         'label' => 'Cotações Convertidas'],
                    ['id' => 'clientes',            'icon' => 'bi-people',             'label' => 'Clientes'],
                    ['id' => 'simulador',           'icon' => 'bi-calculator',         'label' => 'Simulador de Custos'],
                    ['id' => 'viaturas',            'icon' => 'bi-car-front',          'label' => 'Viaturas (Stock)'],
                    ['id' => 'vendas',              'icon' => 'bi-bag-check',          'label' => 'Vendas'],
                    ['id' => 'movimentos',          'icon' => 'bi-arrow-left-right',   'label' => 'Movimentos'],
                    ['id' => 'tarefas',             'icon' => 'bi-check2-square',      'label' => 'Tarefas'],
                    ['id' => 'ferramentas',         'icon' => 'bi-tools',              'label' => 'Ferramentas'],
                    ['id' => 'auditoria',           'icon' => 'bi-shield-check',       'label' => 'Log de Auditoria'],
                ];
                @endphp
                @foreach($sections as $s)
                <a href="#{{ $s['id'] }}" class="manual-nav__item">
                    <i class="bi {{ $s['icon'] }}"></i>
                    {{ $s['label'] }}
                </a>
                @endforeach
            </nav>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         CONTEÚDO DO MANUAL
    ═══════════════════════════════════════ --}}
    <div class="col-lg-9">

        {{-- ── DASHBOARD ── --}}
        <div class="manual-section modern-card mb-4" id="dashboard">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </h4>
                <a href="{{ route('admin.v2.dashboard') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
            </div>
            <div class="manual-section__body">
                <p>O <strong>Dashboard</strong> é a página inicial do back-office. Dá uma visão rápida do estado do negócio.</p>

                <h6 class="manual-topic">O que encontra aqui</h6>
                <ul>
                    <li><strong>Ações Prioritárias</strong> — alerta automático com o que precisa de atenção hoje: leads sem contacto há mais de 3 dias, follow-ups em atraso, cotações sem resposta e viaturas sem preço.</li>
                    <li><strong>Cards de estatísticas</strong> — formulários novos, propostas pendentes, clientes e vendas do mês.</li>
                    <li><strong>Tarefas da semana</strong> — tarefas com prazo próximo ou em atraso.</li>
                    <li><strong>Alertas</strong> — formulários por tratar e propostas antigas sem resposta.</li>
                    <li><strong>Gráfico mensal</strong> — evolução de clientes, cotações e simulações ao longo do tempo.</li>
                </ul>

                <div class="alert alert-info py-2 small">
                    <i class="bi bi-lightbulb me-1"></i>
                    <strong>Dica:</strong> Comece sempre o dia pelo Dashboard. As "Ações Prioritárias" dizem-lhe exactamente o que fazer a seguir sem ter de pesquisar.
                </div>
            </div>
        </div>

        {{-- ── FORMULÁRIOS ── --}}
        <div class="manual-section modern-card mb-4" id="formularios">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-inbox"></i> Formulários de Importação
                </h4>
                <a href="{{ route('admin.v2.form-proposals.index') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
            </div>
            <div class="manual-section__body">
                <p>Quando um cliente preenche o formulário de importação no site, o pedido aparece aqui. É o <strong>ponto de entrada</strong> de novos interessados.</p>

                <h6 class="manual-topic">Fluxo típico</h6>
                <ol>
                    <li>O cliente submete o formulário no site → recebe email de confirmação automático.</li>
                    <li>O formulário aparece com estado <span class="badge bg-warning text-dark">Novo</span> nos Formulários.</li>
                    <li>Abra o formulário, analise os dados e clique em <strong>"Converter em Lead"</strong>.</li>
                    <li>É criada automaticamente uma lead com os dados do cliente.</li>
                    <li>Continue o processo na secção <strong>Leads</strong>.</li>
                </ol>

                <h6 class="manual-topic">Estados possíveis</h6>
                <ul>
                    <li><span class="badge bg-warning text-dark">Novo</span> — ainda não foi visto.</li>
                    <li><span class="badge bg-info text-white">Em análise</span> — está a ser tratado.</li>
                    <li><span class="badge bg-success">Convertido</span> — já foi transformado em lead/cliente.</li>
                    <li><span class="badge bg-secondary">Arquivado</span> — não avança (fora do perfil, cancelado, etc.).</li>
                </ul>

                <div class="alert alert-warning py-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Importante:</strong> Responda sempre no próprio dia. O cliente enviou um pedido e espera contacto rápido.
                </div>
            </div>
        </div>

        {{-- ── LEADS ── --}}
        <div class="manual-section modern-card mb-4" id="leads">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-funnel"></i> Leads
                </h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.v2.leads.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-list-ul me-1"></i> Lista
                    </a>
                    <a href="{{ route('admin.v2.leads.kanban') }}" class="btn btn-sm btn-primary-modern">
                        <i class="bi bi-kanban me-1"></i> Kanban
                    </a>
                </div>
            </div>
            <div class="manual-section__body">
                <p>Uma <strong>lead</strong> é um potencial cliente que ainda não confirmou a compra. É aqui que passa a maior parte do tempo de acompanhamento comercial.</p>

                <h6 class="manual-topic">Vista Kanban vs Lista</h6>
                <ul>
                    <li><strong>Kanban</strong> — 4 colunas por estado. Arraste o card para mudar o estado. Ideal para gerir o pipeline visualmente.</li>
                    <li><strong>Lista</strong> — tabela com filtros. Ideal para pesquisar ou exportar.</li>
                </ul>

                <h6 class="manual-topic">Estados da lead</h6>
                <ul>
                    <li><span class="badge bg-primary">Nova</span> — acabou de entrar, ainda não foi contactada.</li>
                    <li><span class="badge bg-info text-dark">Em Contacto</span> — já houve pelo menos um contacto.</li>
                    <li><span class="badge bg-secondary">Fria</span> — sem resposta há muito tempo, baixa probabilidade.</li>
                    <li><span class="badge bg-danger">Perdida</span> — não vai avançar (optou por outra solução, etc.).</li>
                </ul>

                <h6 class="manual-topic">O que fazer dentro de cada lead</h6>
                <ol>
                    <li><strong>Timeline</strong> — registe cada interacção: chamada, email, reunião, nota. Fica o histórico completo.</li>
                    <li><strong>Follow-up</strong> — defina uma data e hora para ligar/contactar. O sistema avisa-o no Dashboard e no Kanban.</li>
                    <li><strong>Converter em Cliente</strong> — quando a lead confirmar interesse, clique em "Converter". São criados automaticamente os dados de cliente e pode avançar com uma cotação.</li>
                    <li><strong>Marcar como Fria/Perdida</strong> — se não avançar, mude o estado para retirar do pipeline activo.</li>
                </ol>

                <div class="alert alert-info py-2 small">
                    <i class="bi bi-lightbulb me-1"></i>
                    <strong>Dica:</strong> Registe <em>sempre</em> na timeline o que aconteceu (chamada sem resposta, enviou email, etc.). Assim qualquer colega consegue retomar o caso sem precisar de perguntar.
                </div>
            </div>
        </div>

        {{-- ── COTAÇÕES ── --}}
        <div class="manual-section modern-card mb-4" id="cotacoes">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-file-earmark-text"></i> Cotações
                </h4>
                <a href="{{ route('admin.v2.proposals.index') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
            </div>
            <div class="manual-section__body">
                <p>Uma <strong>cotação</strong> é a proposta formal de importação enviada ao cliente, com todos os custos detalhados.</p>

                <h6 class="manual-topic">Criar uma cotação</h6>
                <ol>
                    <li>Vá a <strong>Cotações → Nova Cotação</strong>.</li>
                    <li>Selecione o cliente ou lead (caixa de pesquisa com todos os contactos).</li>
                    <li>Preencha os dados do veículo (marca, modelo, ano, combustível, cilindrada, CO₂).</li>
                    <li>Preencha os custos: veículo, ISV, transporte, IPO, IMT, registo, etc.</li>
                    <li>O <strong>Total Chave na Mão</strong> é calculado automaticamente.</li>
                    <li>Clique em <strong>"Criar Cotação"</strong>.</li>
                </ol>

                <h6 class="manual-topic">Partilhar a cotação com o cliente</h6>
                <p>No painel lateral direito da cotação (ao editar) encontra <strong>"Partilhar Cotação"</strong>:</p>
                <ul>
                    <li><strong>Copiar Link</strong> — copia o link para a área de transferência e cola onde quiser.</li>
                    <li><strong>WhatsApp</strong> — abre o WhatsApp com mensagem pré-escrita e link incluído. Se o cliente tiver telefone guardado, abre directamente a conversa.</li>
                    <li><strong>Email</strong> — abre o seu cliente de email com assunto e corpo pré-preenchidos.</li>
                    <li><strong>Pré-visualizar</strong> — veja exactamente o que o cliente vê antes de enviar.</li>
                </ul>

                <h6 class="manual-topic">O que o cliente vê</h6>
                <p>O cliente recebe uma página profissional com o resumo da cotação, detalhe de todos os custos e um botão <strong>"Aceitar Cotação"</strong>. Se aceitar, é notificado automaticamente e a cotação passa a "Convertida".</p>

                <h6 class="manual-topic">Estados da cotação</h6>
                <ul>
                    <li><span class="badge bg-warning text-dark">Pendente</span> — ainda não foi enviada ao cliente.</li>
                    <li><span class="badge bg-info text-white">Enviado</span> — foi partilhada, aguarda resposta.</li>
                    <li><span class="badge bg-secondary">Sem resposta</span> — passou tempo sem o cliente reagir.</li>
                    <li><span class="badge bg-success">Aprovada</span> — cliente aceitou.</li>
                    <li><span class="badge bg-danger">Reprovada</span> — cliente recusou.</li>
                </ul>

                <div class="alert alert-warning py-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Atenção:</strong> Depois de enviar a cotação, mude o estado para "Enviado" e defina um follow-up para 3–5 dias. O Dashboard avisa-o se ficar sem resposta mais de 5 dias.
                </div>
            </div>
        </div>

        {{-- ── COTAÇÕES CONVERTIDAS ── --}}
        <div class="manual-section modern-card mb-4" id="cotacoes-convertidas">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-check2-all"></i> Cotações Convertidas
                </h4>
                <a href="{{ route('admin.v2.converted-proposals.index') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
            </div>
            <div class="manual-section__body">
                <p>Quando um cliente aceita uma cotação, ela passa para <strong>Cotações Convertidas</strong>. A partir daqui acompanha todo o processo de importação até entrega.</p>

                <h6 class="manual-topic">Processo após aceitação</h6>
                <ol>
                    <li>O cliente aceita a cotação no link partilhado.</li>
                    <li>A cotação aparece em Cotações Convertidas com o estado de acompanhamento.</li>
                    <li>Recolha os dados em falta do cliente (NIF, morada, etc.) na ficha de cliente.</li>
                    <li>Avance com o processo de importação e actualize o estado à medida que avança.</li>
                </ol>
            </div>
        </div>

        {{-- ── CLIENTES ── --}}
        <div class="manual-section modern-card mb-4" id="clientes">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-people"></i> Clientes
                </h4>
                <a href="{{ route('admin.v2.clients.index') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
            </div>
            <div class="manual-section__body">
                <p>A base de dados de todos os <strong>clientes confirmados</strong> (diferentes das leads). Um cliente é alguém que já avançou com pelo menos uma importação ou que foi manualmente registado.</p>

                <h6 class="manual-topic">Diferença entre Lead e Cliente</h6>
                <ul>
                    <li><strong>Lead</strong> — potencial cliente, ainda em negociação.</li>
                    <li><strong>Cliente</strong> — já concretizou ou está em processo activo de importação.</li>
                </ul>

                <h6 class="manual-topic">O que guardar na ficha de cliente</h6>
                <ul>
                    <li>Nome completo, email, telefone.</li>
                    <li>NIF — obrigatório para processos de registo e legalização.</li>
                    <li>Morada completa — necessária para documentos.</li>
                    <li>Notas relevantes sobre preferências ou historial.</li>
                </ul>
            </div>
        </div>

        {{-- ── SIMULADOR ── --}}
        <div class="manual-section modern-card mb-4" id="simulador">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-calculator"></i> Simulador de Custos
                </h4>
                <a href="{{ route('admin.v2.cost-simulators.index') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
            </div>
            <div class="manual-section__body">
                <p>Registo de todas as simulações de custos feitas pelos clientes no site. Permite perceber o interesse do mercado e contactar clientes que simularam mas não pediram cotação.</p>

                <h6 class="manual-topic">Como usar</h6>
                <ul>
                    <li>Consulte as simulações recentes para identificar potenciais leads.</li>
                    <li>Se um cliente simulou mas não pediu cotação, pode contactá-lo proactivamente.</li>
                    <li>Os resultados aparecem na timeline de cada lead quando existe ligação.</li>
                </ul>
            </div>
        </div>

        {{-- ── VIATURAS ── --}}
        <div class="manual-section modern-card mb-4" id="viaturas">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-car-front"></i> Viaturas (Stock)
                </h4>
                <a href="{{ route('admin.v3.vehicles.index') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
            </div>
            <div class="manual-section__body">
                <p>Gestão do stock de viaturas disponíveis para venda directa (viaturas que a Izzycar já importou e tem em posse).</p>

                <h6 class="manual-topic">Adicionar uma viatura</h6>
                <ol>
                    <li>Clique em <strong>"Nova Viatura"</strong>.</li>
                    <li>Preencha os dados gerais (marca, modelo, versão, ano, quilómetros, combustível).</li>
                    <li>Defina o <strong>Preço de Compra</strong> e o <strong>Preço de Venda</strong> — essenciais para calcular a margem.</li>
                    <li>Adicione fotos (a primeira foto é a capa do anúncio).</li>
                    <li>Active <strong>"Mostrar Online"</strong> quando a viatura estiver pronta para aparecer no site.</li>
                </ol>

                <h6 class="manual-topic">Estados da viatura</h6>
                <ul>
                    <li><span class="badge bg-success">Em Stock</span> — disponível para venda.</li>
                    <li><span class="badge bg-warning text-dark">Reservado</span> — com reserva de cliente.</li>
                    <li><span class="badge bg-secondary">Vendido</span> — já foi vendido.</li>
                </ul>

                <div class="alert alert-warning py-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Atenção:</strong> O Dashboard avisa quando há viaturas em stock sem preço de venda definido. Defina sempre o preço antes de activar online.
                </div>

                <h6 class="manual-topic">Separadores disponíveis</h6>
                <ul>
                    <li><strong>Geral</strong> — dados base do veículo.</li>
                    <li><strong>Compra</strong> — preço e detalhes de aquisição.</li>
                    <li><strong>Equipamentos</strong> — extras e opcionais.</li>
                    <li><strong>Legalização</strong> — acompanhamento do processo legal (ISV, matrícula, etc.).</li>
                    <li><strong>Despesas</strong> — custos adicionais (transporte, reparações, etc.).</li>
                    <li><strong>Anúncio</strong> — texto e imagens para publicação online.</li>
                    <li><strong>Venda</strong> — registo da venda quando concluída.</li>
                    <li><strong>Documentos</strong> — upload de documentos do veículo.</li>
                </ul>
            </div>
        </div>

        {{-- ── VENDAS ── --}}
        <div class="manual-section modern-card mb-4" id="vendas">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-bag-check"></i> Vendas
                </h4>
                <a href="{{ route('admin.v2.sales.index') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
            </div>
            <div class="manual-section__body">
                <p>Registo de todas as vendas concluídas. Cada venda está associada a um cliente e a uma viatura.</p>

                <h6 class="manual-topic">Registar uma venda</h6>
                <ol>
                    <li>Vá ao separador <strong>Venda</strong> na ficha da viatura, ou use o botão "Nova Venda" na lista.</li>
                    <li>Associe ao cliente e indique o preço final de venda.</li>
                    <li>O estado da viatura muda automaticamente para "Vendido".</li>
                </ol>
            </div>
        </div>

        {{-- ── MOVIMENTOS ── --}}
        <div class="manual-section modern-card mb-4" id="movimentos">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-arrow-left-right"></i> Movimentos
                </h4>
                <a href="{{ route('admin.v2.movements.index') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
            </div>
            <div class="manual-section__body">
                <p>Registo de todos os movimentos financeiros: despesas e receitas associadas a viaturas e vendas. Permite calcular a rentabilidade real de cada operação.</p>

                <h6 class="manual-topic">Para que serve</h6>
                <ul>
                    <li>Ver todos os custos de cada viatura num só lugar.</li>
                    <li>Calcular a margem real (preço de venda − preço de compra − todas as despesas).</li>
                    <li>Controlo financeiro geral do negócio.</li>
                </ul>
            </div>
        </div>

        {{-- ── TAREFAS ── --}}
        <div class="manual-section modern-card mb-4" id="tarefas">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-check2-square"></i> Tarefas
                </h4>
                <a href="{{ route('admin.tasks.index') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
            </div>
            <div class="manual-section__body">
                <p>Gestão de tarefas internas da equipa. Aparecem no Dashboard as tarefas com prazo esta semana.</p>

                <h6 class="manual-topic">Como usar</h6>
                <ul>
                    <li>Crie tarefas para qualquer acção que precise de acompanhamento.</li>
                    <li>Defina <strong>prazo</strong> — tarefas em atraso ficam a vermelho no Dashboard.</li>
                    <li>Marque como <strong>Concluída</strong> quando feito.</li>
                </ul>

                <div class="alert alert-info py-2 small">
                    <i class="bi bi-lightbulb me-1"></i>
                    <strong>Dica:</strong> Para acções ligadas a um cliente específico, prefira usar os <strong>Follow-ups na Lead</strong> em vez de tarefas genéricas — ficam associados ao cliente e aparecem na sua timeline.
                </div>
            </div>
        </div>

        {{-- ── FERRAMENTAS ── --}}
        <div class="manual-section modern-card mb-4" id="ferramentas">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-tools"></i> Ferramentas
                </h4>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('calculator.profit') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-calculator me-1"></i> Calculadora
                    </a>
                    <a href="{{ route('admin.v2.comparator.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-columns-gap me-1"></i> Comparador
                    </a>
                </div>
            </div>
            <div class="manual-section__body">
                <h6 class="manual-topic">Calculadora de Lucro</h6>
                <p>Simula rapidamente a margem de uma operação. Útil para calcular o lucro antes de fechar um negócio sem criar uma viatura no sistema.</p>

                <h6 class="manual-topic">Comparador de Veículos</h6>
                <p>Compara dois veículos lado a lado com análise de IA. Útil para ajudar o cliente a decidir entre duas opções.</p>

                <h6 class="manual-topic">Análise de Carros (IA)</h6>
                <p>Analisa um anúncio de viatura com IA e sugere se é boa ou má compra, com base no preço de mercado e condições.</p>
            </div>
        </div>

        {{-- ── LOG DE AUDITORIA ── --}}
        <div class="manual-section modern-card mb-4" id="auditoria">
            <div class="modern-card-header manual-section__header">
                <h4 class="manual-section__title">
                    <i class="bi bi-shield-check"></i> Log de Auditoria
                </h4>
                <a href="{{ route('admin.v2.audit-log') }}" class="btn btn-sm btn-primary-modern">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Ir para o módulo
                </a>
            </div>
            <div class="manual-section__body">
                <p>Registo automático e imutável de <strong>todas as acções</strong> realizadas no back-office: logins, criações, edições, eliminações, exportações e conversões.</p>

                <h6 class="manual-topic">Para que serve</h6>
                <ul>
                    <li>Saber quem fez o quê e quando.</li>
                    <li>Ver exactamente o que foi alterado num registo (valores antes e depois).</li>
                    <li>Auditoria de segurança e responsabilidade da equipa.</li>
                </ul>

                <h6 class="manual-topic">Filtros disponíveis</h6>
                <ul>
                    <li>Por utilizador, tipo de acção, descrição ou intervalo de datas.</li>
                    <li>Clique no ícone <i class="bi bi-code-slash"></i> em cada linha para ver o detalhe das alterações.</li>
                </ul>

                <div class="alert alert-info py-2 small">
                    <i class="bi bi-lightbulb me-1"></i>
                    <strong>Nota:</strong> O log é apenas de leitura — não é possível editar ou apagar registos.
                </div>
            </div>
        </div>

        <div class="text-center text-muted small py-3">
            <a href="#manualTop" class="text-muted"><i class="bi bi-arrow-up-circle me-1"></i>Voltar ao topo</a>
        </div>

    </div>{{-- /col-lg-9 --}}
</div>

@endsection

@push('styles')
<style>
/* Sidebar de navegação do manual */
.manual-sidebar { border: none; }
.manual-nav { display: flex; flex-direction: column; gap: .15rem; }
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

/* Secções do manual */
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

/* Highlight activo na navegação ao fazer scroll */
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
// Destaca item activo na sidebar ao fazer scroll
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
