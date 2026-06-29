<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recebemos o seu pedido — Izzycar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #111111 0%, #2a0000 100%); padding: 40px 30px; text-align: center; }
        .header img { max-height: 70px; margin-bottom: 16px; }
        .header h1 { color: #ffffff; font-size: 22px; font-weight: bold; }
        .header p { color: rgba(255,255,255,0.75); font-size: 14px; margin-top: 6px; }
        .body { padding: 36px 30px; }
        .greeting { font-size: 18px; font-weight: bold; color: #111; margin-bottom: 10px; }
        .intro { font-size: 15px; color: #555; line-height: 1.7; margin-bottom: 28px; }
        .steps { margin-bottom: 28px; }
        .step { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px; }
        .step-num { background: linear-gradient(135deg, #990000, #6e0707); color: #fff; width: 28px; height: 28px; border-radius: 50%; font-size: 13px; font-weight: bold; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .step-text { font-size: 14px; color: #444; line-height: 1.6; padding-top: 4px; }
        .step-text strong { color: #111; }
        .summary-box { background: #f8f8f8; border-left: 4px solid #990000; border-radius: 0 8px 8px 0; padding: 20px 24px; margin-bottom: 28px; }
        .summary-box h3 { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #aaa; margin-bottom: 14px; }
        .summary-row { display: flex; justify-content: space-between; font-size: 14px; padding: 6px 0; border-bottom: 1px solid #eee; }
        .summary-row:last-child { border-bottom: none; }
        .summary-row span:first-child { color: #777; }
        .summary-row span:last-child { font-weight: 600; color: #111; text-align: right; }
        .cta-block { text-align: center; margin: 32px 0 20px; }
        .cta-btn { display: inline-block; background: linear-gradient(135deg, #990000, #6e0707); color: #ffffff !important; text-decoration: none; padding: 16px 36px; border-radius: 8px; font-size: 16px; font-weight: bold; letter-spacing: 0.5px; }
        .note { font-size: 13px; color: #888; text-align: center; margin-top: 10px; }
        .divider { border: none; border-top: 1px solid #eee; margin: 28px 0; }
        .contact-block { text-align: center; font-size: 14px; color: #555; margin-bottom: 8px; }
        .contact-block a { color: #990000; font-weight: bold; text-decoration: none; }
        .footer { background: #111111; padding: 28px 30px; text-align: center; }
        .footer img { max-height: 50px; margin-bottom: 12px; }
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
        <img src="https://izzycar.pt/storage/settings/logo.png" alt="Izzycar">
        <h1>Recebemos o seu pedido!</h1>
        <p>A nossa equipa vai entrar em contacto brevemente</p>
    </div>

    <div class="body">
        <p class="greeting">Olá, {{ $client->name }}!</p>
        <p class="intro">
            O seu pedido de importação foi recebido com sucesso. Analisámos centenas de casos como o seu e estamos prontos para
            tornar o processo o mais simples e transparente possível.
        </p>

        {{-- Próximos passos --}}
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text">
                    <strong>Análise do pedido</strong><br>
                    A nossa equipa irá analisar os detalhes do seu pedido nas próximas horas.
                </div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text">
                    <strong>Contacto personalizado</strong><br>
                    Um especialista irá entrar em contacto consigo pelo telefone ou email para esclarecer dúvidas e apresentar opções.
                </div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text">
                    <strong>Cotação detalhada</strong><br>
                    Receberá uma cotação completa com todos os custos — ISV, transporte, IPO e serviços — sem surpresas.
                </div>
            </div>
        </div>

        {{-- Resumo do pedido --}}
        <div class="summary-box">
            <h3>Resumo do seu pedido</h3>

            @if($proposal->brand || $proposal->model)
            <div class="summary-row">
                <span>Veículo</span>
                <span>{{ implode(' ', array_filter([$proposal->brand, $proposal->model, $proposal->version])) ?: '— (a definir)' }}</span>
            </div>
            @endif

            @if($proposal->fuel)
            <div class="summary-row">
                <span>Combustível</span>
                <span>{{ $proposal->fuel }}</span>
            </div>
            @endif

            @if($proposal->budget)
            <div class="summary-row">
                <span>Orçamento</span>
                <span>até {{ number_format($proposal->budget, 0, ',', '.') }} €</span>
            </div>
            @endif

            @php
                $purchaseLabels = [
                    'imediato'    => 'O mais breve possível',
                    '1_3_meses'   => '1 a 3 meses',
                    '3_6_meses'   => '3 a 6 meses',
                    'pesquisar'   => 'Ainda a pesquisar',
                ];
            @endphp
            @if($proposal->estimated_purchase_date)
            <div class="summary-row">
                <span>Prazo estimado</span>
                <span>{{ $purchaseLabels[$proposal->estimated_purchase_date] ?? $proposal->estimated_purchase_date }}</span>
            </div>
            @endif

            @php
                $paymentLabels = [
                    'pronto_pagamento' => 'Pronto pagamento',
                    'financiamento'    => 'Financiamento',
                ];
            @endphp
            @if($proposal->payment_type)
            <div class="summary-row">
                <span>Pagamento</span>
                <span>{{ $paymentLabels[$proposal->payment_type] ?? $proposal->payment_type }}</span>
            </div>
            @endif
        </div>

        <div class="cta-block">
            <a href="https://izzycar.pt" class="cta-btn">Visitar o nosso site</a>
            <p class="note">Entraremos em contacto em até 24 horas úteis.</p>
        </div>

        <hr class="divider">

        <div class="contact-block">
            Tem alguma dúvida? Fale connosco:<br>
            <a href="mailto:geral@izzycar.pt">geral@izzycar.pt</a>
            &nbsp;|&nbsp;
            <a href="https://izzycar.pt">izzycar.pt</a>
        </div>
    </div>

    <div class="footer">
        <img src="https://izzycar.pt/storage/settings/logo_redondo.png" alt="Izzycar">
        <p>
            Izzycar — Importação de Automóveis<br>
            <a href="https://izzycar.pt" style="color: rgba(255,255,255,0.5);">izzycar.pt</a><br><br>
            Este é um email automático. Para responder, utilize <a href="mailto:geral@izzycar.pt" style="color: rgba(255,255,255,0.5);">geral@izzycar.pt</a>
        </p>
    </div>

</div>
</body>
</html>
