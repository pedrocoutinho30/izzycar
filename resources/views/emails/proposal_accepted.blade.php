<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            padding: 20px;
        }

        .container {
            background: #ffffff;
            border-radius: 12px;
            padding: 30px;
            max-width: 700px;
            margin: auto;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-height: 60px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #198754;
        }

        .car-info {
            margin: 20px 0;
            background: #f1f3f5;
            border-radius: 8px;
            padding: 15px;
        }

        .car-info img {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #6c757d;
        }

        .btn {
            display: inline-block;
            background: #198754;
            color: white !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ url($logotipo) }}" alt="Izzycar Logo">
            <div class="title">Proposta Aceite 🚗 </div>
        </div>

        <p>Olá <b>{{ $data['client_name'] }}</b>,</p>

        <p>Acabou de aceitar a proposta feita pela <b>Izzycar</b> para a importação do seu novo carro.</p>

        <div class="car-info">
            <p><b>Detalhes do carro:</b></p>
            <ul>
                <li><b>Marca:</b> {{ $data['brand'] }}</li>
                <li><b>Modelo:</b> {{ $data['model'] }}</li>
                <li><b>Versão:</b> {{ $data['version'] }}</li>
            </ul>
            @if($data['car_image'])
            <img src="{{ url($data['car_image']) }}" alt="Carro escolhido">
            @endif
        </div>

        <p>Em anexo encontra-se o contrato que deverá ser <b>assinado e devolvido</b> à Izzycar.</p>
        <p>Pode enviá-lo por <b>email</b> ou via <b>WhatsApp</b>, como lhe for mais conveniente.</p>

        <p><b>Nota Importante:</b> Assim que a transferência de <b>50% do valor do serviço</b> for recebida, o processo será iniciado com a maior brevidade possível.</p>

        <a href="mailto:geral@izzycar.pt" class="btn">Enviar por Email</a>

        <div class="footer">
            <p>Obrigado pela confiança na Izzycar! 🚀</p>
            <p>Izzycar - Importação Automóvel</p>
        </div>
    </div>
</body>

</html>