<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentação para o Financiamento — Izzycar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #111111 0%, #2a0000 100%); padding: 36px 30px 40px; text-align: center; }
        .header img { width: 130px; max-width: 130px; height: auto; margin-bottom: 20px; opacity: .95; }
        .check-badge { width: 56px; height: 56px; border-radius: 50%; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; }
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
        .highlight-box { background: #fff8f0; border-radius: 10px; padding: 18px 22px; margin-bottom: 30px; display: flex; align-items: center; gap: 14px; }
        .highlight-box .icon { font-size: 22px; flex-shrink: 0; }
        .highlight-box p { font-size: 14px; color: #6b4a00; line-height: 1.6; margin: 0; }
        .highlight-box strong { color: #4a3300; }
        .divider { border: none; border-top: 1px solid #eee; margin: 30px 0; }
        .contact-block { text-align: center; font-size: 14px; color: #555; margin-bottom: 8px; }
        .contact-block a { color: #990000; font-weight: bold; text-decoration: none; }
        .footer { background: #111111; padding: 28px 30px; text-align: center; }
        .footer img { width: 90px; max-width: 90px; height: auto; margin-bottom: 12px; }
        .footer p { color: rgba(255,255,255,0.5); font-size: 12px; line-height: 1.8; }
        @media only screen and (max-width: 600px) {
            .wrapper { margin: 0; border-radius: 0; }
            .body { padding: 26px 20px; }
        }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <img src="https://izzycar.pt/storage/settings/logo.png" alt="Izzycar" width="130" style="width:130px;max-width:130px;height:auto;">
        <div class="check-badge">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
        </div>
        <h1>Documentação para o Financiamento</h1>
        <p>Consulte os anexos deste email</p>
    </div>

    <div class="body">
        <p class="greeting">Olá, {{ $client->name }}!</p>
        <p class="intro">
            Segue em anexo a lista de documentação necessária para o processo de crédito pessoal, bem como o
            formulário de RGPD relativo ao tratamento de dados pessoais (este último deve ser assinado e enviado
            juntamente com os restantes documentos).
        </p>

        <div class="section-title">Anexos</div>
        <table class="steps-table" width="100%" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="step-icon">📄</td>
                <td class="step-text">
                    <strong>Lista de documentação necessária</strong>
                    Documentos a reunir para o processo de crédito pessoal.
                </td>
            </tr>
            <tr class="last">
                <td class="step-icon">✍️</td>
                <td class="step-text">
                    <strong>Formulário de RGPD</strong>
                    Deve ser assinado e enviado junto com os restantes documentos.
                </td>
            </tr>
        </table>

        <div class="highlight-box">
            <div class="icon">🤝</div>
            <p>Pode enviar tudo diretamente para a <strong>xFin</strong>, parceira de financiamento da IzzyCar, ou,
                se preferir, enviar-nos a nós que tratamos de fazer chegar a informação ao parceiro.</p>
        </div>

        <p class="intro" style="margin-bottom: 0;">Qualquer dúvida, estamos à disposição.</p>

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
