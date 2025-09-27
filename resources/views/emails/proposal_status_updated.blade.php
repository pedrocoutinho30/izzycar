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
    </style>
</head>

<body>
    <div class="container" style="background: #111111; border-radius: 8px; padding: 25px; max-width: 600px; margin: auto; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">


        <div style="  text-align: center;
            margin-bottom: 20px;">
            <img src="https://izzycar.pt/storage/settings/logo_redondo.png" alt="Izzycar Logo" style="max-height: 60px; max-width: 60px; margin-bottom: 10px;">
            <div class="title" style="font-size: 24px; font-weight: bold; color: #fff;">Atualização do Estado da Proposta </div>
        </div>

        <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Olá <b>{{ $client_name }}</b>,</p>


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
        <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">{{ $message }}</p>

        <div style="font-size: 16px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 6px;
            text-align: center;
            margin: 20px 0;">
            <h3>{{ $convertedProposal->brand }} {{ $convertedProposal->modelCar }} {{ $convertedProposal->version }}</h3>
        </div>
        <div style="font-size: 16px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 6px;
            text-align: center;
            margin: 20px 0;">
            <b style="color: #990000;">{{ $oldStatus }}</b> ➝ <b style="color: green;">{{ $newStatus }}</b>
        </div>



        <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Iremos mantê-lo informado sobre os próximos passos do processo.</p>

        <div class="footer" style="font-size: 16px; text-align: center; margin-bottom: 20px; color: #fff; margin-top: 30px;">
            <p>Equipa Izzycar</p>
            <p><i>Este é um email automático, por favor não responda diretamente a esta mensagem.</i></p>
        </div>
    </div>
</body>

</html>