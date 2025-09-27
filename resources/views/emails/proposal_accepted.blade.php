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
                <div class="title" style="font-size: 24px; font-weight: bold; color: #fff;">Aceitação de Proposta </div>
            </div>

            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Olá <b>{{ $data['client_name']}}</b>,</p>


            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Acabou de aceitar a proposta feita pela <b>Izzycar</b> para a importação do seu novo carro.</p>

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

            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Em anexo encontra-se o contrato que deverá ser <b>assinado e devolvido</b> à Izzycar.</p>
            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Uma cópia do cartão de cidadão do comprador deverá ser enviada junto com o contrato.</p>

            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Pode enviá-lo por <b>email</b> ou via <b>WhatsApp</b>, como lhe for mais conveniente.</p>

            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;"><b>Nota Importante:</b> Assim que a transferência de <b>60% do valor do serviço</b> for recebida, o processo será iniciado com a maior brevidade possível.</p>

            <div class="footer">
                <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Obrigado pela confiança na Izzycar! 🚀</p>
                <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Estamos aqui para ajudar em qualquer dúvida que possa ter.</p>
            </div>
        </div>
</body>

</html>