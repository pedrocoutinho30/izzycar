<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulação de Custos de Importação</title>
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
        .cost-table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
        .cost-table tr td { padding: 12px 16px; font-size: 15px; border-bottom: 1px solid #f0f0f0; }
        .cost-table tr td:first-child { color: #555; }
        .cost-table tr td:last-child { text-align: right; font-weight: 600; color: #111; }
        .cost-table tr.total td { background: #111; color: #fff !important; font-size: 17px; font-weight: bold; border-radius: 0; }
        .cost-table tr.total td:last-child { color: #fff !important; }
        .cta-block { text-align: center; margin: 32px 0; }
        .cta-btn { display: inline-block; background: linear-gradient(135deg, #990000, #6e0707); color: #ffffff !important; text-decoration: none; padding: 16px 36px; border-radius: 8px; font-size: 16px; font-weight: bold; letter-spacing: 0.5px; }
        .note { font-size: 13px; color: #888; text-align: center; margin-top: 8px; }
        .divider { border: none; border-top: 1px solid #eee; margin: 28px 0; }
        .secondary-cta { text-align: center; font-size: 14px; color: #555; margin-bottom: 28px; }
        .secondary-cta a { color: #990000; font-weight: bold; text-decoration: none; }
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
        <h1>Simulação de Custos de Importação</h1>
        <p>{{ $simulation->brand ?? '' }} {{ $simulation->model ?? '' }}</p>
    </div>

    <div class="body">
        <p class="greeting">Olá, {{ $clientName }}!</p>
        <p class="intro">
            A sua simulação de custos de importação está pronta. Clique no botão abaixo para ver o resultado completo, incluindo a tabela detalhada de cálculo do ISV.
        </p>

        <table class="cost-table">
            <tr>
                <td>🚗 Valor do carro</td>
                <td>{{ number_format($simulation->car_value, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td>📋 ISV (Imposto)</td>
                <td>{{ number_format($simulation->isv_cost, 2, ',', '.') }} €</td>
            </tr>
            <tr>
                <td>⚙️ Custos de serviço</td>
                <td>{{ number_format($simulation->commission_cost + $simulation->inspection_commission_cost + $simulation->transport + $simulation->ipo_cost + $simulation->imt_cost + $simulation->registration_cost + $simulation->plates_cost + 300, 2, ',', '.') }} €</td>
            </tr>
            <tr class="total">
                <td>💰 Preço Chave na Mão</td>
                <td>{{ number_format($simulation->total_cost, 2, ',', '.') }} €</td>
            </tr>
        </table>

        <div class="cta-block">
            <a href="{{ $resultUrl }}" class="cta-btn">Ver Resultado Completo</a>
            <p class="note">Este link é pessoal e pode ser acedido a qualquer momento.</p>
        </div>

        <hr class="divider">

        <div class="secondary-cta">
            Quer avançar com a importação?<br>
            <a href="https://izzycar.pt/formulario-importacao">Preencha o formulário de importação</a>
        </div>
    </div>

    <div class="footer">
        <img src="https://izzycar.pt/storage/settings/logo_redondo.png" alt="Izzycar">
        <p>
            Izzycar — Importação de Automóveis<br>
            <a href="https://izzycar.pt" style="color: rgba(255,255,255,0.5);">izzycar.pt</a><br><br>
            Este é um email automático, por favor não responda diretamente a esta mensagem.
        </p>
    </div>

</div>
</body>
</html>