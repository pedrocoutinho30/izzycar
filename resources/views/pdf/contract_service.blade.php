<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Contrato de Prestação de Serviços — Izzycar</title>
    <style>
        @page {
            margin: 90px 55px 70px 55px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            line-height: 1.55;
            color: #222;
        }

        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 60px;
            border-bottom: 1px solid #b08d3c;
        }

        header img {
            height: 52px;
        }

        header .ref {
            position: absolute;
            right: 0;
            top: 4px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }

        footer {
            position: fixed;
            bottom: -45px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8.5px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .pagenum:before {
            content: counter(page);
        }

        h1 {
            text-align: center;
            font-size: 16px;
            letter-spacing: 1px;
            margin: 0 0 4px 0;
        }

        .subtitulo {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-bottom: 18px;
        }

        h2 {
            font-size: 11.5px;
            color: #7a5c14;
            border-bottom: 1px solid #e3d5b0;
            padding-bottom: 3px;
            margin: 16px 0 6px 0;
            page-break-after: avoid;
        }

        p {
            margin: 4px 0;
            text-align: justify;
        }

        ul {
            margin: 4px 0 4px 0;
            padding-left: 16px;
        }

        li {
            margin-bottom: 2px;
            text-align: justify;
        }

        .partes td {
            vertical-align: top;
            width: 50%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            background: #fafafa;
        }

        .partes .titulo {
            font-weight: bold;
            color: #7a5c14;
            text-transform: uppercase;
            font-size: 9.5px;
            margin-bottom: 4px;
        }

        .prazos {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        .prazos td {
            border: 1px solid #ddd;
            padding: 5px 8px;
        }

        .prazos td.fase {
            width: 55%;
        }

        .prazos td.prazo {
            font-weight: bold;
            white-space: nowrap;
        }

        .destaque {
            border-left: 3px solid #b08d3c;
            background: #fbf6e9;
            padding: 6px 10px;
            margin: 8px 0;
            page-break-inside: avoid;
        }

        .pagamento {
            border: 1px solid #ddd;
            padding: 6px 10px;
            margin: 6px 0;
            page-break-inside: avoid;
        }

        .clausula {
            page-break-inside: avoid;
        }

        .assinaturas {
            margin-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }

        .assinaturas td {
            text-align: center;
            vertical-align: bottom;
            padding: 10px 20px;
            width: 50%;
        }
    </style>
</head>

<body>

    <header>
        @if($logo)
        <img src="{{ $logo }}" alt="Izzycar">
        @endif
        <div class="ref">
            Contrato de Prestação de Serviços<br>
            @if($cotacao)Cotação n.º {{ $cotacao }}<br>@endif
            Emitido em {{ $dataEmissao }}
        </div>
    </header>

    <footer>
        Izzycar · NIF {{ $prestador['nif'] }}
        @if(!empty($prestador['telefone'])) · Tel. {{ $prestador['telefone'] }}@endif
        @if(!empty($prestador['email'])) · {{ $prestador['email'] }}@endif
        — Página <span class="pagenum"></span>
    </footer>

    <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS</h1>
    <div class="subtitulo">Importação de veículo chave na mão</div>

    <h2>1. Identificação das Partes</h2>
    <table class="partes" width="100%" cellspacing="6">
        <tr>
            <td>
                <div class="titulo">Prestador</div>
                <b>{{ $prestador['nome'] }}</b><br>
                NIF: {{ $prestador['nif'] }}<br>
                Morada: {{ $prestador['morada'] }}<br>
                @if(!empty($prestador['telefone']))Telefone: {{ $prestador['telefone'] }}<br>@endif
                @if(!empty($prestador['email']))Email: {{ $prestador['email'] }}@endif
            </td>
            <td>
                <div class="titulo">Cliente</div>
                <b>{{ $cliente['nome'] }}</b><br>
                NIF: {{ $cliente['nif'] ?: '—' }}<br>
                Morada: {{ $cliente['morada'] ?: '—' }}<br>
                @if(!empty($cliente['telefone']))Telefone: {{ $cliente['telefone'] }}<br>@endif
                @if(!empty($cliente['email']))Email: {{ $cliente['email'] }}@endif
            </td>
        </tr>
    </table>

    <h2>2. Objeto do Contrato</h2>
    <p>
        O presente contrato tem por objeto a prestação, pelo Prestador, de serviços de apoio à importação de um veículo
        para Portugal, por conta do Cliente e segundo as condições da cotação por este aceite, a qual faz parte integrante
        do contrato.
    </p>
    @if($veiculo)
    <div class="destaque">
        <b>Veículo objeto do contrato:</b> {{ $veiculo['descricao'] }}@if(!empty($veiculo['ano'])) ({{ $veiculo['ano'] }})@endif.
    </div>
    @endif
    <p>Os serviços compreendem, nos termos da cotação aceite:</p>
    <ul>
        <li>Acompanhamento e apoio na aquisição do veículo no país de origem escolhido pelo Cliente, incluindo a verificação do anúncio e do vendedor e o apoio na negociação.</li>
        <li>Transporte do veículo até Portugal e tratamento dos procedimentos de importação e desalfandegamento, incluindo a declaração e o pagamento do ISV junto da Autoridade Tributária e Aduaneira.</li>
        <li>Legalização do veículo em território português: inspeção técnica (IPO) e homologação (IMT) quando aplicável, matrícula e registo automóvel.</li>
        <li>Acompanhamento do processo até à entrega do veículo ao Cliente, com informação sobre o seu estado.</li>
        <li>Serviços adicionais, apenas se acordados por escrito entre as Partes.</li>
    </ul>
    <p>O Prestador não vende o veículo: a compra é celebrada entre o Cliente e o vendedor de origem.</p>

    <h2>3. Prazos</h2>
    <p>Os prazos abaixo são <b>meramente indicativos</b> (em dias úteis ou de calendário, conforme indicado) e dependem de terceiros (vendedor, transportadora, autoridades e entidades oficiais).</p>
    <table class="prazos">
        <tr><td class="fase">Assinatura do contrato</td><td class="prazo">até 3 dias após a cotação aceite</td></tr>
        <tr><td class="fase">Compra do veículo</td><td class="prazo">até 7 dias úteis após a adjudicação</td></tr>
        <tr><td class="fase">Transporte até Portugal</td><td class="prazo">até 15 dias úteis após a compra</td></tr>
        <tr><td class="fase">Entrega do veículo ao Cliente</td><td class="prazo">cerca de 30 dias após a adjudicação</td></tr>
    </table>
    <div class="destaque">
        <b>Atrasos de transporte.</b> O prazo de transporte inclui uma margem para os atrasos que as transportadoras
        podem ter (carga, consolidação de viaturas, condições meteorológicas, trânsito, greves, inspeções ou
        avarias). Esses atrasos são frequentes e fora do controlo do Prestador, pelo que o Cliente deve contar com eles
        e não programar a entrega para uma data fixa. Enquanto o veículo estiver a caminho, o Prestador informa o
        Cliente de qualquer alteração relevante à data prevista.
    </div>
    <p>
        Os atrasos imputáveis a terceiros ou a causas de força maior (incluindo prazos de entidades oficiais, como a AT ou
        o IMT) não constituem incumprimento do Prestador. Os prazos de legalização só começam a contar com a receção de
        toda a documentação necessária.
    </p>

    <h2>4. Obrigações do Prestador</h2>
    <ul>
        <li>Executar os serviços com diligência, profissionalismo e de acordo com a cotação aceite.</li>
        <li>Manter o Cliente informado sobre o estado do processo, incluindo através da ligação de acompanhamento enviada por email.</li>
        <li>Prestar ao Cliente a informação necessária à decisão de compra, designadamente sobre o estado do veículo que lhe seja transmitido pelo vendedor ou resulte de inspeção contratada.</li>
        <li>Guardar sigilo sobre os dados e documentos do Cliente e usá-los apenas para os fins do contrato.</li>
    </ul>

    <h2>5. Obrigações do Cliente</h2>
    <ul>
        <li>Fornecer, em tempo útil, os dados e documentos necessários (identificação, NIF, comprovativo de morada e demais documentos pedidos), sendo responsável pela sua exatidão.</li>
        <li>Efetuar os pagamentos nos prazos e pelos meios indicados neste contrato.</li>
        <li>Decidir sobre a compra com base na informação disponibilizada, confirmando as caraterísticas do veículo antes da adjudicação.</li>
        <li>Receber o veículo no prazo que lhe for indicado e, a partir da entrega, assumir a sua guarda e os seus encargos (seguro, IUC, entre outros).</li>
    </ul>

    <h2>6. Condições de Pagamento</h2>
    <div class="pagamento">
        <b>Serviço Izzycar</b>
        <ul>
            @if($pagamentoInicial)<li>{{ $pagamentoInicial }} na adjudicação do serviço.</li>@endif
            @if($pagamentoFinal)<li>{{ $pagamentoFinal }} na entrega do automóvel.</li>@endif
        </ul>
        Pagamento por transferência bancária para o IBAN: <b>{{ $iban }}</b><br>
        Titular: Pedro Coutinho. Deve ser enviado o comprovativo de pagamento para
        @if(!empty($prestador['email'])){{ $prestador['email'] }}@else o Prestador @endif.
    </div>
    <p>
        <b>Aquisição do veículo:</b> o pagamento é efetuado diretamente pelo Cliente ao stand ou vendedor de origem, não
        sendo recebido pelo Prestador.
    </p>
    <p>
        <b>Outros custos:</b> os valores de transporte, impostos (ISV), inspeção, taxas e registos são os indicados na
        cotação aceite. Os impostos são apurados pela Autoridade Tributária e Aduaneira, pelo que podem diferir dos
        valores estimados se os dados do veículo ou a legislação aplicável se alterarem, devendo o Cliente ser informado
        antes de qualquer alteração.
    </p>

    <h2>7. Responsabilidade</h2>
    <ul>
        <li>O veículo é adquirido ao vendedor de origem, a quem cabem as garantias e responsabilidades legais da venda. O Prestador apoia o Cliente em eventuais reclamações, mas não responde por vícios ou por informação incorreta prestada pelo vendedor.</li>
        <li>Em caso de dano durante o transporte, o Prestador acompanha a reclamação junto da transportadora ou da seguradora.</li>
        <li>O Prestador não responde por danos resultantes de informação incompleta ou incorreta fornecida pelo Cliente, nem por atos ou omissões de entidades oficiais.</li>
    </ul>

    <h2>8. Cancelamento e Resolução</h2>
    <ul>
        <li>Em caso de cancelamento por parte do Cliente, os custos já efetuados pelo Prestador, incluindo os de terceiros, não serão devolvidos.</li>
        <li>Qualquer das Partes pode resolver o contrato em caso de incumprimento grave da outra, após aviso por escrito e prazo razoável para o corrigir.</li>
    </ul>

    <h2>9. Proteção de Dados</h2>
    <p>
        Os dados pessoais do Cliente são tratados pelo Prestador exclusivamente para a execução do contrato e
        cumprimento de obrigações legais, nos termos do Regulamento Geral sobre a Proteção de Dados. Podem ser
        comunicados a entidades indispensáveis ao serviço (vendedor, transportadora, AT, IMT, IRN). O Cliente pode exercer
        os seus direitos de acesso, retificação e apagamento através do contacto do Prestador indicado neste contrato.
    </p>

    <h2>10. Comunicações</h2>
    <p>
        As comunicações entre as Partes são feitas por email ou telefone, para os contactos indicados neste contrato,
        devendo cada Parte avisar a outra de qualquer alteração.
    </p>

    <h2>11. Lei Aplicável e Resolução de Litígios</h2>
    <p>
        O contrato rege-se pela lei portuguesa. Para a resolução de litígios é competente o tribunal da comarca do
        domicílio do Cliente, sem prejuízo do recurso a entidades de resolução alternativa de litígios de consumo. O
        Cliente pode ainda apresentar reclamações no Livro de Reclamações Eletrónico (www.livroreclamacoes.pt).
    </p>

    <h2>12. Assinaturas</h2>
    <table class="assinaturas" width="100%">
        <tr>
            <td>
                _________________________________ <br>
                Assinatura do Cliente <br>
                Data: ____ / ____ / ______
            </td>
            <td>
                <div style="height: 55px; text-align: center;">
                    
                    @if($signaturePath)
                    <img src="{{ $signaturePath }}" alt="Assinatura" style="height: 50px;">
                    @endif
                </div>
                _________________________________ <br>
                Assinatura do Prestador <br>
                Data: {{ $dataEmissao }} <br>
            </td>
        </tr>
    </table>

</body>

</html>
