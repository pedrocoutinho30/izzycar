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
        .header { background: linear-gradient(135deg, #111111 0%, #2a0000 100%); padding: 36px 30px 40px; text-align: center; }
        .header img { width: 130px; max-width: 130px; height: auto; margin-bottom: 20px; opacity: .95; }
        .check-badge { width: 56px; height: 56px; border-radius: 50%; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; }
        .check-badge span { font-size: 26px; line-height: 1; color: #fff; }
        .header h1 { color: #ffffff; font-size: 23px; font-weight: bold; }
        .header p { color: rgba(255,255,255,0.75); font-size: 14px; margin-top: 6px; }
        .body { padding: 36px 30px; }
        .greeting { font-size: 18px; font-weight: bold; color: #111; margin-bottom: 10px; }
        .intro { font-size: 15px; color: #555; line-height: 1.7; margin-bottom: 30px; }
        .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #aaa; margin-bottom: 16px; }
        .steps-table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        .steps-table td { padding-bottom: 18px; vertical-align: top; }
        .steps-table tr.last td { padding-bottom: 0; }
        .step-icon { font-size: 20px; width: 32px; line-height: 1; }
        .step-text { font-size: 14px; color: #444; line-height: 1.6; }
        .step-text strong { color: #111; display: block; font-size: 14.5px; margin-bottom: 1px; }
        .summary-box { background: #f8f8f8; border-radius: 10px; padding: 22px 24px; margin-bottom: 30px; }
        .summary-box h3 { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #aaa; margin-bottom: 14px; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { font-size: 14px; padding: 9px 0; border-bottom: 1px solid #ececec; }
        .summary-table tr.last td { border-bottom: none; }
        .summary-table td.icon { font-size: 15px; width: 26px; }
        .summary-table td.label { color: #888; }
        .summary-table td.value { font-weight: 600; color: #111; text-align: right; }
        .highlight-box { background: #fff8f0; border-radius: 10px; padding: 18px 22px; margin-bottom: 30px; display: flex; align-items: center; gap: 14px; }
        .highlight-box .icon { font-size: 22px; flex-shrink: 0; }
        .highlight-box p { font-size: 14px; color: #6b4a00; line-height: 1.6; margin: 0; }
        .highlight-box strong { color: #4a3300; }
        .cta-block { text-align: center; margin: 34px 0 22px; }
        .cta-btn { display: inline-block; background: linear-gradient(135deg, #990000, #6e0707); color: #ffffff !important; text-decoration: none; padding: 16px 40px; border-radius: 8px; font-size: 16px; font-weight: bold; letter-spacing: 0.5px; box-shadow: 0 6px 16px rgba(153,0,0,0.25); }
        .note { font-size: 13px; color: #888; text-align: center; margin-top: 12px; }
        .divider { border: none; border-top: 1px solid #eee; margin: 30px 0; }
        .contact-block { text-align: center; font-size: 14px; color: #555; margin-bottom: 8px; }
        .contact-block a { color: #990000; font-weight: bold; text-decoration: none; }
        .footer { background: #111111; padding: 28px 30px; text-align: center; }
        .footer img { width: 90px; max-width: 90px; height: auto; margin-bottom: 12px; }
        .footer p { color: rgba(255,255,255,0.5); font-size: 12px; line-height: 1.8; }
        @media only screen and (max-width: 600px) {
            .wrapper { margin: 0; border-radius: 0; }
            .body { padding: 26px 20px; }
            .cta-btn { padding: 14px 26px; font-size: 15px; }
        }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <img src="https://izzycar.pt/storage/settings/logo.png" alt="Izzycar" width="130" style="width:130px;max-width:130px;height:auto;">
        <div class="check-badge"><span>✓</span></div>
        <h1>Recebemos o seu pedido!</h1>
        <p>A nossa equipa vai entrar em contacto brevemente</p>
    </div>

    <div class="body">
        <p class="greeting">Olá, {{ $client->name }}!</p>
        <p class="intro">
            O seu pedido de importação foi recebido com sucesso. Já analisámos centenas de casos como o seu e estamos
            prontos para tornar todo o processo simples, transparente e sem surpresas.
        </p>

        <div class="section-title">O que acontece a seguir</div>
        <table class="steps-table" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="step-icon">🔍</td>
                <td class="step-text">
                    <strong>Análise do pedido</strong>
                    A nossa equipa irá analisar os detalhes do seu pedido nas próximas horas.
                </td>
            </tr>
            <tr>
                <td class="step-icon">📞</td>
                <td class="step-text">
                    <strong>Contacto personalizado</strong>
                    Um especialista irá contactá-lo por telefone ou email para esclarecer dúvidas e apresentar opções.
                </td>
            </tr>
            <tr class="last">
                <td class="step-icon">📄</td>
                <td class="step-text">
                    <strong>Cotação detalhada</strong>
                    Receberá uma cotação completa com todos os custos — ISV, transporte, IPO e serviços — sem surpresas.
                </td>
            </tr>
        </table>

        {{-- Resumo do pedido --}}
        @php
            $purchaseLabels = [
                'imediato'    => 'O mais breve possível',
                '1_3_meses'   => '1 a 3 meses',
                '3_6_meses'   => '3 a 6 meses',
                'pesquisar'   => 'Ainda a pesquisar',
            ];
            $paymentLabels = [
                'pronto_pagamento' => 'Pronto pagamento',
                'financiamento'    => 'Financiamento',
            ];

            $summaryRows = [];
            if ($proposal->brand || $proposal->model) {
                $summaryRows[] = [
                    'icon' => '🚗',
                    'label' => 'Veículo',
                    'value' => implode(' ', array_filter([$proposal->brand, $proposal->model, $proposal->version])) ?: '— (a definir)',
                ];
            }
            if ($proposal->fuel) {
                $summaryRows[] = ['icon' => '⛽', 'label' => 'Combustível', 'value' => $proposal->fuel];
            }
            if ($proposal->budget) {
                $summaryRows[] = ['icon' => '💶', 'label' => 'Orçamento', 'value' => 'até ' . number_format($proposal->budget, 0, ',', '.') . ' €'];
            }
            if ($proposal->estimated_purchase_date) {
                $summaryRows[] = [
                    'icon' => '📅',
                    'label' => 'Prazo estimado',
                    'value' => $purchaseLabels[$proposal->estimated_purchase_date] ?? $proposal->estimated_purchase_date,
                ];
            }
            if ($proposal->payment_type) {
                $summaryRows[] = [
                    'icon' => '💳',
                    'label' => 'Pagamento',
                    'value' => $paymentLabels[$proposal->payment_type] ?? $proposal->payment_type,
                ];
            }
        @endphp
        <div class="summary-box">
            <h3>Resumo do seu pedido</h3>
            <table class="summary-table" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                @foreach($summaryRows as $row)
                <tr @if($loop->last) class="last" @endif>
                    <td class="icon">{{ $row['icon'] }}</td>
                    <td class="label">{{ $row['label'] }}</td>
                    <td class="value">{{ $row['value'] }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div class="highlight-box">
            <div class="icon">⏱️</div>
            <p><strong>Entraremos em contacto em até 24 horas úteis.</strong> Entretanto, fique à vontade para explorar mais sobre o processo no nosso site.</p>
        </div>

        <div class="cta-block">
            <a href="https://izzycar.pt" class="cta-btn">Visitar o Nosso Site</a>
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
