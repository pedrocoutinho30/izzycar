@extends('layouts.admin-v2')

@section('title', 'Perguntas Frequentes')

@section('content')

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Início', 'href' => route('admin.angariador.dashboard')],
        ['icon' => '', 'label' => 'Perguntas Frequentes']
    ],
    'title'       => 'Perguntas Frequentes',
    'subtitle'    => 'Respostas rápidas para as dúvidas mais comuns dos potenciais clientes',
    'extraActions' => [
        ['href' => route('admin.angariador.manual'), 'label' => 'Manual do Angariador', 'icon' => 'bi-book'],
    ],
])

<div class="alert alert-info py-2 small mb-4">
    <i class="bi bi-lightbulb me-1"></i>
    Usa estas respostas como estão — é a informação oficial da Izzycar. Se surgir uma pergunta que não está aqui, confirma sempre com a administração antes de responder ao cliente.
</div>

@php
$faqGroups = [
    'Sobre o Processo de Importação' => [
        ['q' => 'Quanto tempo demora o processo de importação?', 'a' => 'Normalmente, o processo demora entre 2 a 6 semanas, dependendo do país de origem e da disponibilidade logística. O cliente é sempre informado do estado do processo em tempo real.'],
        ['q' => 'Posso escolher qualquer carro ou a Izzycar ajuda-me na seleção?', 'a' => 'O cliente pode escolher um modelo específico ou apenas indicar as características que procura. A Izzycar apresenta sempre uma proposta detalhada, com preço chave na mão.'],
        ['q' => 'Que veículos importam?', 'a' => 'Importamos todo o tipo de veículos com preço de origem a partir dos 10.000€, adquiridos exclusivamente a profissionais ou stands automóvel.'],
        ['q' => 'Como posso ter a certeza de que o carro está em boas condições?', 'a' => 'É realizada uma vistoria presencial e, em paralelo, uma análise detalhada do histórico da viatura, que é apresentada ao cliente.'],
        ['q' => 'O transporte é seguro?', 'a' => 'Sim. Trabalhamos apenas com transportadoras certificadas, com seguro incluído. O cliente é mantido informado sobre o progresso do transporte.'],
        ['q' => 'O que acontece quando o carro chega a Portugal?', 'a' => 'O carro é descarregado no centro de inspeções para a realização da Inspeção B e depois é guardado nas instalações da Izzycar até à emissão da matrícula portuguesa.'],
        ['q' => 'Posso acompanhar todo o processo de importação do meu carro?', 'a' => 'Sim. A Izzycar garante total visibilidade em cada etapa. Desde a escolha do veículo até à entrega final, o cliente recebe atualizações regulares e pode consultar o estado do processo a qualquer momento.'],
        ['q' => 'Como é feita a entrega final?', 'a' => 'Após a emissão da matrícula, são feitas as chapas, o cliente contrata o seguro automóvel e pode levantar o carro.'],
    ],
    'Pagamentos e Custos' => [
        ['q' => 'Como funciona o pagamento?', 'a' => 'A compra requer pagamento integral, sem possibilidade de financiamento ou retomas.'],
        ['q' => 'Como são feitos os pagamentos?', 'a' => 'O carro deve ser pago na totalidade por transferência interbancária diretamente para o stand vendedor.<br>O serviço é pago em duas fases: 60% no início e 40% na entrega do veículo.'],
        ['q' => 'Existem custos adicionais?', 'a' => 'Não. O valor apresentado na proposta já inclui todos os custos de transporte, legalização e impostos.'],
        ['q' => 'O que está incluído no valor da proposta?', 'a' => '<ul class="mb-0"><li><b>Preço da Viatura</b> — valor do carro adquirido fora de Portugal.</li><li><b>Transporte</b> — transporte seguro até Portugal.</li><li><b>ISV</b> — Imposto Sobre Veículos, pago ao Estado na legalização.</li><li><b>IUC</b> — Imposto Único de Circulação, anual, tal como em qualquer viatura nacional.</li><li><b>Inspeção, Matrícula e Legalização</b> — inspeção obrigatória, emissão da matrícula e processo administrativo.</li><li><b>Honorários do Serviço</b> — valor fixo pelo serviço completo de importação e legalização.</li></ul>'],
    ],
    'Legalização e Documentos' => [
        ['q' => 'Quais os contratos que preciso assinar?', 'a' => 'São assinados dois contratos: o contrato de compra (emitido pelo stand vendedor) e o contrato de prestação de serviços com a Izzycar.'],
        ['q' => 'Quem trata da legalização do carro em Portugal?', 'a' => 'A Izzycar assume todo o processo: inspeção obrigatória, matrícula nacional, pagamento de impostos (ISV e IUC) e emissão da documentação.'],
        ['q' => 'Como funciona o pagamento do ISV e a matrícula?', 'a' => 'Depois de toda a documentação ser entregue ao IMT/Alfândega, é emitida uma guia de pagamento do ISV, que deve ser liquidada de imediato. Em 48 a 72h é emitida a DAV com a matrícula nacional.'],
        ['q' => 'E se a compra for em nome de uma empresa?', 'a' => 'Quando a compra é feita em nome de uma pessoa coletiva, dependendo da viatura, pode haver lugar à devolução do IVA. Nesse caso, o valor do IVA funciona como caução para o vendedor e só é devolvido após confirmação da legitimidade da empresa compradora, sendo entregue apenas quando o vendedor recebe o comprovativo de matrícula em Portugal.'],
    ],
    'Sobre Ser Angariador' => [
        ['q' => 'O cliente tem de me pagar alguma coisa?', 'a' => 'Não. A tua comissão é paga diretamente pela Izzycar — o cliente nunca paga nada ao angariador, apenas o valor da proposta à Izzycar.'],
        ['q' => 'Posso negociar ou alterar o preço da proposta?', 'a' => 'Não. Toda a pesquisa, negociação e valor final da proposta é sempre definido pela equipa Izzycar. O teu papel é angariar e acompanhar o contacto, nunca negociar preços.'],
        ['q' => 'E se o cliente já for cliente Izzycar, através de outra via?', 'a' => 'Se o email/telefone já corresponderem a um registo existente, a administração é notificada para analisar o caso manualmente — a atribuição desse caso específico pode não ficar automaticamente associada a ti. Confirma sempre com a administração se isso acontecer.'],
        ['q' => 'O que faço se o cliente disser que não recebeu o email da proposta?', 'a' => 'Confirma a caixa de spam e, se necessário, reforça o mesmo link da proposta por WhatsApp/SMS — encontras o link na página da lead, em "Propostas".'],
        ['q' => 'Quanto tempo depois da entrega recebo a minha comissão?', 'a' => 'A administração tem até 24 horas após a entrega do veículo para marcar a comissão como paga. Podes acompanhar o estado (pendente/paga) na área de Comissões do teu painel.'],
    ],
];
@endphp

@foreach($faqGroups as $groupTitle => $items)
<div class="modern-card mb-4">
    <div class="modern-card-header">
        <h5 class="modern-card-title"><i class="bi bi-question-circle"></i> {{ $groupTitle }}</h5>
    </div>
    <div class="accordion" id="faq-{{ Str::slug($groupTitle) }}">
        @foreach($items as $index => $item)
        @php $itemId = Str::slug($groupTitle) . '-' . $index; @endphp
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $itemId }}">
                    {{ $item['q'] }}
                </button>
            </h2>
            <div id="collapse-{{ $itemId }}" class="accordion-collapse collapse" data-bs-parent="#faq-{{ Str::slug($groupTitle) }}">
                <div class="accordion-body">
                    {!! $item['a'] !!}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

@endsection

@push('styles')
<style>
.accordion-button:not(.collapsed) {
    background: rgba(110, 7, 7, 0.06);
    color: var(--admin-primary, #6e0707);
    box-shadow: none;
}
.accordion-button:focus { box-shadow: none; border-color: rgba(0,0,0,.125); }
.accordion-button { font-size: .92rem; font-weight: 600; }
.accordion-body { font-size: .88rem; color: #444; line-height: 1.6; }
.accordion-body ul { padding-left: 1.2rem; margin-bottom: 0; }
</style>
@endpush
