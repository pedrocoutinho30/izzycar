<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Contrato Izzycar</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.6;
        }

        h1,
        h2 {
            text-align: center;
        }

        .section {
            margin-bottom: 20px;
        }

        .assinaturas {
            margin-top: 60px;
            width: 100%;
        }

        .assinaturas td {
            text-align: center;
            vertical-align: bottom;
            padding: 20px;
        }

        .linha {
            border-top: 1px solid #000;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <h1>CONTRATO DE PRESTAÇÃO DE SERVIÇOS</h1>

    <div class="section">
        <h2>1. Identificação das Partes</h2>
        <p><b>Prestador:</b> <br>
            Nome:<b> {{ $prestador['nome'] }} </b><br>
            NIF: <b>{{ $prestador['nif'] }} </b><br>
            Morada: <b>{{ $prestador['morada'] }}</b></p>

        <p><b>Cliente:</b><br>
            Nome: <b>{{ $cliente['nome'] }} </b><br>
            Morada: <b>{{ $cliente['morada'] }} </b><br>
            NIF: <b>{{ $cliente['nif'] }} </b><br>
    </div>

    <div class="section">
        <h2>2. Objeto do Contrato</h2>
        <p>
            O presente contrato tem como objeto a prestação de serviços por parte do Prestador, consistindo em:
            <br>• Aquisição de veículo no país de origem escolhido pelo Cliente.
            <br>• Transporte do veículo até Portugal, incluindo procedimentos de importação.
            <br>• Legalização e matrícula do veículo em território português.
            <br>• Serviços adicionais acordados.
        </p>
    </div>

    <div class="section">
        <h2>3. Prazos</h2>
        <p>
            Os prazos são meramente indicativos e podem variar por dependência de terceiros:
            <br>• Assinatura do contrato: até 3 dias após proposta aceite.
            <br>• Compra do veículo: até 15 dias após adjudicação.
            <br>• Transporte: cerca de 10 dias após compra.
            <br>• Entrega do veículo: até 30 dias após adjudicação.
        </p>
    </div>

    <div style="page-break-before: always;"></div>
    <div class="section">
        <h2>4. Condições de Pagamento</h2>
        <p><b>Serviço Izzycar:</b><br>
            • 50% na adjudicação do serviço.<br>
            • 50% na entrega do automóvel.<br>
            • Pagamento via transferência ou MB Way:<br>
            
            IBAN: <b>{{ $iban }}</b><br>
            MB Way: <b>{{ $mbway }}</b></p>

        <p><b>Aquisição do veículo:</b><br>
            O pagamento é efetuado diretamente pelo Cliente ao stand de origem.</p>

        <p><i>Em caso de cancelamento por parte do Cliente, os custos já efetuados pelo Prestador não serão devolvidos.</i></p>
    </div>

    <div class="section">
        <h2>5. Assinaturas</h2>
        <table class="assinaturas" width="100%">
            <tr>
                <td>
                    _________________________________ <br>
                    Assinatura do Cliente <br>
                    Data: ____ / ____ / ______
                </td>
                <td>
                    _________________________________ <br>
                    Assinatura do Prestador <br>
                    Data: ____ / ____ / ______ <br>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>