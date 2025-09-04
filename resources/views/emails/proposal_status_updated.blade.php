<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f9f9f9;
            padding: 20px;
        }

        .container {
            background: #fff;
            border-radius: 8px;
            padding: 25px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .car-info {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            text-align: center;
        }

        .car-info img {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }

        .status {
            font-size: 16px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 6px;
            text-align: center;
            margin: 20px 0;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #777;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>🚗 Atualização do Estado da Proposta</h2>
        </div>

        <p>Olá <b>{{ $client_name }}</b>,</p>


        @php
        $message = '';

        if ($newStatus === 'Iniciada') {
        $message = "Demos início ao processo do seu carro. Em breve terá novidades sobre os próximos passos.";
        } elseif ($newStatus === 'Negociação Carro') {
        $message = "Estamos a negociar o seu carro com o stand de origem, para garantir as melhores condições para si.";
        } elseif ($newStatus === 'Transporte') {
        $message = "O seu carro já está a caminho! O transporte foi iniciado e em breve chegará a Portugal.";
        } elseif ($newStatus === 'IPO') {
        $message = "O seu carro encontra-se agora em inspeção (IPO), um passo essencial para garantir a sua conformidade.";
        } elseif ($newStatus === 'DAV') {
        $message = "Estamos a tratar da Declaração Aduaneira de Veículo (DAV). O processo continua a avançar.";
        } elseif ($newStatus === 'ISV') {
        $message = "Estamos a tratar do pagamento do ISV, aproximando-nos cada vez mais da legalização do seu carro.";
        } elseif ($newStatus === 'IMT') {
        $message = "O processo de registo no IMT está em curso. Em breve o seu carro estará pronto para circular legalmente em Portugal.";
        } elseif ($newStatus === 'Matriculação') {
        $message = "A nova matrícula do seu carro ($matricula) está a ser tratada. Já falta pouco para o ter consigo!";
        } elseif ($newStatus === 'Entrega') {
        $message = "Estamos a preparar a entrega do seu carro. Muito em breve poderá conduzir o seu novo automóvel.";
        } elseif ($newStatus === 'Registo automóvel') {
        $message = "Estamos a ultimar o registo automóvel em seu nome. O carro ficará oficialmente registado como seu.";
        } elseif ($newStatus === 'Concluido') {
        $message = "Parabéns! O processo do seu carro está concluído. Desejamos-lhe muitos quilómetros de felicidade ao volante.";
        } elseif ($newStatus === 'Cancelado') {
        $message = "O processo foi cancelado. Se desejar, estamos disponíveis para esclarecer dúvidas ou apresentar novas soluções para si.";
        }
        @endphp
        <p>{{ $message }}</p>

        <div class="status">
            <b>{{ $oldStatus }}</b> ➝ <b>{{ $newStatus }}</b>
        </div>

        <div class="car-info">
            <h3>{{ $convertedProposal->brand }} {{ $convertedProposal->modelCar }} {{ $convertedProposal->version }}</h3>
        </div>

        <p>Iremos mantê-lo informado sobre os próximos passos do processo.</p>

        <div class="footer">
            <p>Equipa Izzycar</p>
            <p><i>Este é um email automático, por favor não responda diretamente a esta mensagem.</i></p>
        </div>
    </div>
</body>

</html>