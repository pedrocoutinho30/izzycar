<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($forAngariador ?? false) ? 'Cliente Aceitou a Proposta' : 'Proposta Aceite' }} — Izzycar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #111111 0%, #2a0000 100%); padding: 40px 30px; text-align: center; }
        .header img { width: 150px; max-width: 150px; height: auto; margin-bottom: 16px; }
        .header h1 { color: #ffffff; font-size: 22px; font-weight: bold; }
        .header p { color: rgba(255,255,255,0.75); font-size: 14px; margin-top: 6px; }
        .body { padding: 36px 30px; }
        .greeting { font-size: 18px; font-weight: bold; color: #111; margin-bottom: 10px; }
        .intro { font-size: 15px; color: #555; line-height: 1.7; margin-bottom: 28px; }
        .vehicle-box { background: #f8f8f8; border-left: 4px solid #990000; border-radius: 0 8px 8px 0; padding: 20px 24px; margin-bottom: 28px; }
        .vehicle-box h3 { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #aaa; margin-bottom: 14px; }
        .vehicle-row { display: flex; justify-content: space-between; font-size: 14px; padding: 6px 0; border-bottom: 1px solid #eee; }
        .vehicle-row:last-child { border-bottom: none; }
        .vehicle-row span:first-child { color: #777; }
        .vehicle-row span:last-child { font-weight: 600; color: #111; text-align: right; }
        .vehicle-image { display: block; width: 100%; max-width: 100%; height: auto; border-radius: 8px; margin-top: 16px; }
        .steps { margin-bottom: 28px; }
        .step { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px; }
        .step-num { background: linear-gradient(135deg, #990000, #6e0707); color: #fff; width: 28px; height: 28px; border-radius: 50%; font-size: 13px; font-weight: bold; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .step-text { font-size: 14px; color: #444; line-height: 1.6; padding-top: 4px; }
        .step-text strong { color: #111; }
        .note-box { background: #fff8f0; border-left: 4px solid #c98a00; border-radius: 0 8px 8px 0; padding: 16px 20px; margin-bottom: 28px; font-size: 14px; color: #6b4a00; line-height: 1.6; }
        .note-box strong { color: #4a3300; }
        .info-box { background: #f8f8f8; border-left: 4px solid #990000; border-radius: 0 8px 8px 0; padding: 16px 20px; margin-bottom: 28px; font-size: 14px; color: #555; line-height: 1.6; }
        .cta-block { text-align: center; margin: 32px 0 20px; }
        .cta-btn { display: inline-block; background: linear-gradient(135deg, #990000, #6e0707); color: #ffffff !important; text-decoration: none; padding: 16px 36px; border-radius: 8px; font-size: 16px; font-weight: bold; letter-spacing: 0.5px; }
        .note { font-size: 13px; color: #888; text-align: center; margin-top: 10px; word-break: break-all; }
        .divider { border: none; border-top: 1px solid #eee; margin: 28px 0; }
        .contact-block { text-align: center; font-size: 14px; color: #555; margin-bottom: 8px; }
        .contact-block a { color: #990000; font-weight: bold; text-decoration: none; }
        .footer { background: #111111; padding: 28px 30px; text-align: center; }
        .footer img { width: 90px; max-width: 90px; height: auto; margin-bottom: 12px; }
        .footer p { color: rgba(255,255,255,0.5); font-size: 12px; line-height: 1.8; }
        @media only screen and (max-width: 600px) {
            .wrapper { margin: 0; border-radius: 0; }
            .body { padding: 24px 20px; }
            .cta-btn { padding: 14px 24px; font-size: 15px; }
        }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <img src="https://izzycar.pt/storage/settings/logo.png" alt="Izzycar" width="150" style="width:150px;max-width:150px;height:auto;">
        @if($forAngariador ?? false)
            <h1>O Cliente Aceitou a Proposta!</h1>
            <p>Já pode acompanhar o processo de importação</p>
        @else
            <h1>Proposta Aceite!</h1>
            <p>O seu processo de importação vai começar</p>
        @endif
    </div>

    <div class="body">
        @if($forAngariador ?? false)
            <p class="greeting">Boas notícias!</p>
            <p class="intro">
                O cliente <strong>{{ $data['client_name'] }}</strong> acabou de aceitar a cotação de uma das suas leads.
                A partir de agora pode acompanhar o progresso do processo de importação — transporte, legalização e entrega —
                sempre que quiser, através do link mais abaixo.
            </p>
        @else
            <p class="greeting">Olá, {{ $data['client_name'] }}!</p>
            <p class="intro">
                Acabou de aceitar a cotação feita pela <strong>Izzycar</strong> para a importação do seu novo carro.
                Estamos muito felizes por continuar este processo consigo — faltam apenas alguns passos para o seu carro
                chegar até si.
            </p>
        @endif

        <div class="vehicle-box">
            <h3>O Seu Carro</h3>
            <div class="vehicle-row">
                <span>Marca</span>
                <span>{{ $data['brand'] }}</span>
            </div>
            <div class="vehicle-row">
                <span>Modelo</span>
                <span>{{ $data['model'] }}</span>
            </div>
            <div class="vehicle-row">
                <span>Versão</span>
                <span>{{ $data['version'] }}</span>
            </div>
            @if(!empty($data['car_image']))
                <img src="{{ url($data['car_image']) }}" alt="{{ $data['brand'] }} {{ $data['model'] }}" class="vehicle-image">
            @endif
        </div>

        @if($forAngariador ?? false)
            <div class="info-box">
                Não precisa de fazer mais nada — a Izzycar trata de todo o processo com o cliente. Este link é só para acompanhamento.
            </div>
        @else
            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-text">
                        <strong>Assine o contrato em anexo</strong><br>
                        Vai encontrar o contrato entre si e a Izzycar em PDF, junto a este email.
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-text">
                        <strong>Envie o contrato assinado e o seu cartão de cidadão</strong><br>
                        Pode enviar por <strong>email</strong> ou <strong>WhatsApp</strong>, como lhe for mais conveniente.
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-text">
                        <strong>Efetue a transferência de 60% do valor do serviço</strong><br>
                        O processo de importação arranca com a maior brevidade possível assim que recebermos o comprovativo.
                    </div>
                </div>
            </div>

            <div class="note-box">
                <strong>Nota importante:</strong> assim que a transferência de 60% do valor do serviço for recebida, o processo será iniciado de imediato.
            </div>
        @endif

        @if(!empty($tracking_url))
            <div class="cta-block">
                <a href="{{ $tracking_url }}" class="cta-btn">Acompanhar o Processo</a>
                <p class="note">{{ $tracking_url }}</p>
            </div>
        @endif

        <hr class="divider">

        <div class="contact-block">
            Tem alguma dúvida? Fale connosco:<br>
            <a href="mailto:geral@izzycar.pt">geral@izzycar.pt</a>
            &nbsp;|&nbsp;
            <a href="https://izzycar.pt">izzycar.pt</a>
        </div>
    </div>

    <div class="footer">
        <img src="https://izzycar.pt/storage/settings/logo_redondo.png" alt="Izzycar" width="90" style="width:90px;max-width:90px;height:auto;">
        <p>
            Izzycar — Importação de Automóveis<br>
            <a href="https://izzycar.pt" style="color: rgba(255,255,255,0.5);">izzycar.pt</a><br><br>
            Este é um email automático. Para responder, utilize <a href="mailto:geral@izzycar.pt" style="color: rgba(255,255,255,0.5);">geral@izzycar.pt</a>
        </p>
    </div>

</div>
</body>
</html>
