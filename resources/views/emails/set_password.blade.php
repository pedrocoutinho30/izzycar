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
            <div style="font-size: 24px; font-weight: bold; color: #fff;">Bem-vindo à Izzycar</div>
        </div>

        <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Olá <b>{{ $user->name }}</b>,</p>

        <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Foi criada uma conta de acesso ao backoffice da Izzycar para si. Para começar a utilizá-la, defina a sua password clicando no botão abaixo.</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" style="background: #6e0707; color: #fff; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;">
                Definir a minha password
            </a>
        </div>

        <p style="font-size: 14px; margin-bottom: 20px; color: #ccc;">Se o botão não funcionar, copie e cole este link no seu navegador:</p>
        <p style="font-size: 13px; margin-bottom: 20px; color: #ccc; word-break: break-all;">{{ $url }}</p>

        <p style="font-size: 14px; margin-bottom: 0; color: #ccc;">Este link é válido durante 3 dias. Se não pediu a criação desta conta, pode ignorar este email.</p>

        <div class="footer" style="margin-top: 20px;">
            <p style="font-size: 16px; margin-bottom: 0; color: #fff;">Até já! 🚀</p>
        </div>
    </div>
</body>

</html>
