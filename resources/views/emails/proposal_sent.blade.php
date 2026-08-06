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

        <div style="text-align: center; margin-bottom: 20px;">
            <img src="https://izzycar.pt/storage/settings/logo_redondo.png" alt="Izzycar Logo" style="max-height: 60px; max-width: 60px; margin-bottom: 10px;">
            <div style="font-size: 24px; font-weight: bold; color: #fff;">
                @if($forAngariador)
                    Cotação Enviada ao Cliente
                @else
                    A Sua Cotação Está Pronta
                @endif
            </div>
        </div>

        @if($forAngariador)
        <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Olá,</p>
        <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">
            Foi enviada uma cotação ao cliente <b>{{ $proposal->client->name ?? '—' }}</b>, de uma das suas leads.
            Pode consultar exatamente o mesmo documento que o cliente recebeu através do link abaixo.
        </p>
        @else
        <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Olá <b>{{ $proposal->client->name ?? '' }}</b>,</p>
        <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">A sua cotação para a importação do seu novo carro já está disponível. Pode consultá-la através do link abaixo.</p>
        @endif

        <div>
            <p style="font-size: 16px; margin-bottom: 10px; color: #fff;"><b>Detalhes do carro:</b></p>
            <ul>
                @if($proposal->brand)<li style="font-size: 16px; margin-bottom: 10px; color: #fff;"><b>Marca:</b> {{ $proposal->brand }}</li>@endif
                @if($proposal->model)<li style="font-size: 16px; margin-bottom: 10px; color: #fff;"><b>Modelo:</b> {{ $proposal->model }}</li>@endif
                @if($proposal->version)<li style="font-size: 16px; margin-bottom: 10px; color: #fff;"><b>Versão:</b> {{ $proposal->version }}</li>@endif
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" style="background: #6e0707; color: #fff; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;">
                Ver Cotação
            </a>
        </div>

        <p style="font-size: 13px; margin-bottom: 20px; color: #ccc; word-break: break-all;">{{ $url }}</p>

        @if($forAngariador)
        <div style="background: #1c1c1c; border-radius: 8px; padding: 15px 20px; margin: 20px 0;">
            <p style="font-size: 15px; margin-bottom: 10px; color: #fff;"><b>O que deve fazer agora:</b></p>
            <ul style="color: #ddd; font-size: 14px; padding-left: 18px;">
                <li style="margin-bottom: 8px;">Dentro de <b>{{ $followupDays }} dias</b>, faça um ponto de situação com o cliente sobre esta cotação.</li>
                <li style="margin-bottom: 8px;">Confirme com o cliente se recebeu o email com a proposta — se necessário, reforce o link da cotação por mensagem (WhatsApp/SMS).</li>
                <li style="margin-bottom: 0;">Já agendámos automaticamente um follow-up para si, {{ $followupDays }} dias após o envio desta cotação, para não se esquecer.</li>
            </ul>
        </div>
        @else
        <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Qualquer dúvida, estamos disponíveis para ajudar.</p>
        @endif

        <div class="footer">
            <p style="font-size: 16px; margin-bottom: 0; color: #fff;">Obrigado pela confiança na Izzycar! 🚀</p>
        </div>
    </div>
</body>

</html>
