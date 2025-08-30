<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Prestação de Serviços</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12pt;
            margin: 40px;
            line-height: 1.6;
            color: #000;
        }
        h1, h2, h3, h4 {
            text-align: center;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 25px;
        }
        ul {
            margin: 10px 0 10px 20px;
        }
        .assinaturas {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .assinatura {
            text-align: center;
            width: 40%;
        }
        .assinatura .linha {
            margin-top: 60px;
            border-top: 1px solid #000;
        }
    </style>
</head>
<body>
    <div class="document">
        <h1>Contrato de Prestação de Serviços</h1>

        <div class="section">
            <p><strong>Prestador:</strong> Pedro Coutinho - izzycar<br>
            NIF: 242414958<br>
            Morada: Rua da Imprensa Portuguesa, 1 drt 10, Praia do Furadouro, Ovar</p>
        </div>

        <div class="section">
            <p><strong>Cliente:</strong> {{ $client->name ?? '___________________________________________' }}<br>
            NIF: {{ $client->nif ?? '________________________' }}<br>
            Morada: {{ $client->address ?? '___________________________________________' }}</p>
        </div>

        <div class="section">
            <h3>1. Objeto</h3>
            <p>O presente contrato tem como objeto a prestação de serviços de consultoria e intermediação na aquisição e legalização de veículo automóvel em Portugal.</p>
        </div>

        <div class="section">
            <h3>2. Formalização</h3>
            <p>O processo é oficializado com a assinatura de dois contratos:</p>
            <ul>
                <li><strong>Prestação de Serviços:</strong> Define o serviço de forma clara e estruturada.</li>
                <li><strong>Compra e Venda com o Stand:</strong> Formaliza a aquisição do veículo e respetiva legalização.</li>
            </ul>
        </div>

        <div class="section">
            <h3>3. Obrigações do Prestador</h3>
            <p>O Prestador compromete-se a acompanhar o Cliente em todas as fases do processo de importação, incluindo: pesquisa, negociação, transporte e legalização do veículo.</p>
        </div>

        <div class="section">
            <h3>4. Prazos</h3>
            <p>O prazo estimado para a conclusão do processo é de 30 a 60 dias úteis. Contudo, o Cliente reconhece que estes prazos são meramente indicativos, podendo sofrer alterações por dependência de terceiros, como stands, transportadoras e entidades públicas.</p>
        </div>

        <div class="section">
            <h3>5. Condições de Pagamento</h3>
            <p><strong>Serviço da izzycar:</strong></p>
            <ul>
                <li>50% na adjudicação do serviço (após assinatura do contrato).</li>
                <li>50% na entrega do automóvel em Portugal.</li>
            </ul>
            <p>Pagamento via transferência bancária ou MB Way:<br>
            IBAN: [teu IBAN]<br>
            MB Way: [teu número]</p>

            <p><strong>Aquisição do veículo:</strong><br>
            O pagamento é efetuado diretamente pelo Cliente ao stand de origem do veículo.</p>
        </div>

        <div class="section">
            <h3>6. Cancelamentos</h3>
            <p>Em caso de cancelamento por parte do Cliente, todos os custos já efetuados pelo Prestador não serão devolvidos.</p>
        </div>

        <div class="assinaturas">
            <div class="assinatura">
                <p>___________________________________</p>
                <p>Assinatura do Cliente</p>
                <p>Data: ____/____/______</p>
            </div>
            <div class="assinatura">
                <p>___________________________________</p>
                <p>Pedro Coutinho - izzycar</p>
                <p>Data: ____/____/______</p>
            </div>
        </div>
    </div>
</body>
</html>
