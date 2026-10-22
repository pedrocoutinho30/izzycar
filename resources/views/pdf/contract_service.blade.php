<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Contrato de Prestação de Serviços — Izzycar</title>
    @include('pdf._contract-styles')
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
    @php
        $eur = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €';
    @endphp
    <div class="destaque">
        <b>Veículo objeto do contrato:</b> {{ $veiculo['descricao'] }}@if(!empty($veiculo['ano'])) ({{ $veiculo['ano'] }})@endif{!! filled($veiculo['vin']) ? ', com o VIN <b>' . e($veiculo['vin']) . '</b>' : '' !!}.
        <br>
        @if(filled($veiculo['matricula_origem']))<b>Matrícula de origem:</b> {{ $veiculo['matricula_origem'] }} · @endif
        @if(filled($veiculo['km']))<b>Quilómetros:</b> {{ number_format((float) $veiculo['km'], 0, ',', '.') }} km · @endif
        @if(filled($veiculo['combustivel']))<b>Combustível:</b> {{ $veiculo['combustivel'] }} · @endif
        @if(filled($veiculo['cor']))<b>Cor:</b> {{ $veiculo['cor'] }}@endif
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
        <tr><td class="fase">Entrega do veículo ao Cliente</td><td class="prazo">cerca de 30 dias após compra do veículo</td></tr>
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

    <div class="clausula">
    <h2>6. Cotação e Condições de Pagamento</h2>
    @if($cotacaoAceite)
    @php $eur = $eur ?? fn ($v) => number_format((float) $v, 2, ',', '.') . ' €'; @endphp
    <p>
        O valor a pagar à Izzycar pelos serviços contratados, nos termos da cotação aceite pelo Cliente{{ $cotacao ? ' (n.º ' . $cotacao . ')' : '' }}, que faz parte integrante deste contrato, é o seguinte:
    </p>
    <table class="cotacao">
        <tr class="total"><td>Total a pagar à Izzycar<span class="nota">Serviços da Izzycar e custos de legalização em Portugal. Não inclui o valor do automóvel, pago diretamente ao vendedor de origem, nem o ISV, pago diretamente às Finanças.</span></td><td class="valor">{{ $eur($cotacaoAceite['totalIzzycar']) }}</td></tr>
        @if($cotacaoAceite['isv'])
        <tr class="info"><td>ISV (valor estimado)<span class="nota">Dado informativo: valor estimado, pago diretamente às Finanças (Autoridade Tributária e Aduaneira) e apurado por esta, não incluído no total a pagar à Izzycar.</span></td><td class="valor">{{ $eur($cotacaoAceite['isv']) }}</td></tr>
        @endif
        @if($cotacaoAceite['valorCarro'])
        <tr class="info"><td>Valor de aquisição do automóvel<span class="nota">Dado informativo: pago diretamente ao vendedor de origem, não incluído no total a pagar à Izzycar.</span></td><td class="valor">{{ $eur($cotacaoAceite['valorCarro']) }}</td></tr>
        @endif
    </table>
    @endif
    </div>
    <div class="pagamento">
        <b>Serviço Izzycar @if($cotacaoAceite)— {{ $eur($cotacaoAceite['totalIzzycar']) }} em duas tranches @endif</b>
        <ul>
            @if($cotacaoAceite)
            <li><b>1.ª tranche: {{ $eur($cotacaoAceite['tranche1']) }}</b> ({{ rtrim(rtrim(number_format($cotacaoAceite['pct1'], 2, ',', ''), '0'), ',') }}%), na adjudicação do serviço.</li>
            <li><b>2.ª tranche: {{ $eur($cotacaoAceite['tranche2']) }}</b> ({{ rtrim(rtrim(number_format($cotacaoAceite['pct2'], 2, ',', ''), '0'), ',') }}%), na entrega do automóvel.</li>
            @else
            @if($pagamentoInicial)<li>{{ $pagamentoInicial }} na adjudicação do serviço.</li>@endif
            @if($pagamentoFinal)<li>{{ $pagamentoFinal }} na entrega do automóvel.</li>@endif
            @endif
        </ul>
        Pagamento por transferência bancária para o IBAN: <b>{{ $iban }}</b><br>
        Titular: Pedro Coutinho. Deve ser enviado o comprovativo de pagamento para
        @if(!empty($prestador['email'])){{ $prestador['email'] }}@else o Prestador @endif.
    </div>
    <p>
        <b>Aquisição do veículo:</b> o pagamento do valor do automóvel é efetuado diretamente pelo Cliente ao stand ou
        vendedor de origem, não sendo recebido pelo Prestador nem incluído no total a pagar à Izzycar.
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
        <li>A responsabilidade pelo transporte, guarda e conservação da viatura rege-se pela secção 8.</li>
        <li>O Prestador não responde por danos resultantes de informação incompleta ou incorreta fornecida pelo Cliente, nem por atos ou omissões de entidades oficiais.</li>
    </ul>

    <h2>8. Transporte, guarda e responsabilidade pela viatura</h2>
    <ol>
        <li>A IzzyCar assegura a organização e contratação do transporte da viatura junto de uma transportadora habilitada, verificando previamente a existência de seguro válido e as respetivas condições de cobertura aplicáveis ao serviço contratado.</li>
        <li>Quando o transporte seja contratado pela IzzyCar no âmbito do presente contrato, a IzzyCar assume perante o Cliente a responsabilidade pela execução desse serviço, desde a tomada a cargo da viatura no vendedor, incluindo as operações de carga, até à descarga e receção no destino acordado, abrangendo eventuais períodos de armazenamento intermédio. Mantém-se o direito da IzzyCar de exigir à transportadora ou a outros responsáveis o ressarcimento dos prejuízos que lhes sejam imputáveis.</li>
        <li>Na recolha, o estado aparente da viatura será verificado pelo vendedor e pela transportadora e registado em documento assinado por ambos. Na receção pela IzzyCar, ou por pessoa por esta designada, será efetuada nova verificação, com registo das diferenças ou danos aparentes no documento de entrega, assinado pelo transportador e por quem recebe. Sempre que possível, serão recolhidas fotografias ou vídeos.</li>
        <li>Os danos aparentes identificados na receção serão imediatamente assinalados e comunicados à transportadora. A IzzyCar assegurará o acompanhamento da reclamação e a comunicação com o Cliente, sem prejuízo dos direitos e prazos aplicáveis a danos não aparentes.</li>
        <li>Após a receção, a IzzyCar assume a responsabilidade pela guarda e conservação da viatura até à entrega ao Cliente, mantendo-a em garagem fechada. A deslocação à inspeção será efetuada em reboque. As deslocações para lavagem e entrega ao Cliente serão realizadas por circulação da própria viatura, apenas após a atribuição da matrícula e com seguro automóvel obrigatório válido, mantendo-se a responsabilidade da IzzyCar até à entrega ao Cliente.</li>
        <li>Caso o Cliente contrate diretamente a transportadora através de contrato autónomo, as condições de transporte, seguro e responsabilidade serão definidas nesse contrato, disponibilizado ao Cliente para análise antes da contratação. Esta modalidade deverá ficar expressamente identificada por escrito, mantendo-se a responsabilidade da IzzyCar pelos serviços que lhe competem.</li>
    </ol>

    <h2>9. Cancelamento e Resolução</h2>
    <ul>
        <li>Em caso de cancelamento por parte do Cliente, os custos já efetuados pelo Prestador, incluindo os de terceiros, não serão devolvidos.</li>
        <li>Qualquer das Partes pode resolver o contrato em caso de incumprimento grave da outra, após aviso por escrito e prazo razoável para o corrigir.</li>
    </ul>

    <h2>10. Proteção de Dados</h2>
    <p>
        Os dados pessoais do Cliente são tratados pelo Prestador exclusivamente para a execução do contrato e
        cumprimento de obrigações legais, nos termos do Regulamento Geral sobre a Proteção de Dados. Podem ser
        comunicados a entidades indispensáveis ao serviço (vendedor, transportadora, AT, IMT, IRN). O Cliente pode exercer
        os seus direitos de acesso, retificação e apagamento através do contacto do Prestador indicado neste contrato.
    </p>

    <h2>11. Comunicações</h2>
    <p>
        As comunicações entre as Partes são feitas por email ou telefone, para os contactos indicados neste contrato,
        devendo cada Parte avisar a outra de qualquer alteração.
    </p>

    <h2>12. Lei Aplicável e Resolução de Litígios</h2>
    <p>
        O contrato rege-se pela lei portuguesa. Para a resolução de litígios é competente o tribunal da comarca do
        domicílio do Cliente, sem prejuízo do recurso a entidades de resolução alternativa de litígios de consumo. O
        Cliente pode ainda apresentar reclamações no Livro de Reclamações Eletrónico (www.livroreclamacoes.pt).
    </p>

    <h2>13. Assinaturas</h2>
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
