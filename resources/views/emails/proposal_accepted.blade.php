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
    </style>
</head>

<body>
    <div class="container" style="background: #111111; border-radius: 8px; padding: 25px; max-width: 600px; margin: auto; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">

        <div class="container">
            <div style="  text-align: center;
            margin-bottom: 20px;">
                <img src="https://izzycar.pt/storage/settings/logo_redondo.png" alt="Izzycar Logo" style="max-height: 60px; max-width: 60px; margin-bottom: 10px;">
                <div class="title" style="font-size: 24px; font-weight: bold; color: #fff;">Aceitação de Cotação </div>
            </div>

            @if($forAngariador ?? false)
            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Olá,</p>
            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">O cliente <b>{{ $data['client_name'] }}</b> acabou de aceitar a cotação de uma das suas leads. Pode acompanhar o progresso do processo através do link abaixo.</p>
            @else
            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Olá <b>{{ $data['client_name']}}</b>,</p>


            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Acabou de aceitar a cotação feita pela <b>Izzycar</b> para a importação do seu novo carro.</p>
            @endif

            <div>
                <p style="font-size: 16px; margin-bottom: 20px; color: #fff;"><b>Detalhes do carro:</b></p>
                <ul>
                    <li style="font-size: 16px; margin-bottom: 20px; color: #fff;"><b>Marca:</b> {{ $data['brand'] }}</li>
                    <li style="font-size: 16px; margin-bottom: 20px; color: #fff;"><b>Modelo:</b> {{ $data['model'] }}</li>
                    <li style="font-size: 16px; margin-bottom: 20px; color: #fff;"><b>Versão:</b> {{ $data['version'] }}</li>
                </ul>
                @if($data['car_image'])
                <img src="{{ url($data['car_image']) }}" alt="Carro escolhido">
                @endif
            </div>

            @if($forAngariador ?? false)
            <div style="background: #1c1c1c; border-radius: 8px; padding: 15px 20px; margin: 10px 0 20px;">
                <p style="font-size: 15px; margin-bottom: 0; color: #ddd;">Acompanhe o estado do processo de importação — transporte, legalização e entrega — sempre que quiser, através do link abaixo.</p>
            </div>
            @else
            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Em anexo encontra-se o contrato que deverá ser <b>assinado e devolvido</b> à Izzycar.</p>
            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Uma cópia do cartão de cidadão do comprador deverá ser enviada junto com o contrato.</p>

            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Pode enviá-lo por <b>email</b> ou via <b>WhatsApp</b>, como lhe for mais conveniente.</p>

            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;"><b>Nota Importante:</b> Assim que a transferência de <b>60% do valor do serviço</b> for recebida, o processo será iniciado com a maior brevidade possível.</p>
            @endif

            @if(!empty($tracking_url))
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $tracking_url }}" style="background: #6e0707; color: #fff; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;">
                    Acompanhar o Processo
                </a>
            </div>
            <p style="font-size: 13px; margin-bottom: 20px; color: #ccc; word-break: break-all;">{{ $tracking_url }}</p>
            @endif

            <div class="footer">
                <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Obrigado pela confiança na Izzycar! 🚀</p>
                <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Estamos aqui para ajudar em qualquer dúvida que possa ter.</p>
            </div>
        </div>
</body>

</html>