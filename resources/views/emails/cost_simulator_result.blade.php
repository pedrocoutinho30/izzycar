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
                <div class="title" style="font-size: 24px; font-weight: bold; color: #fff;">Resultado do Simulador de Custos </div>
            </div>

            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Aqui estão os detalhes do seu carro e custos associados:
            </p>

            <ul>
                <li style="font-size: 16px;  color: #fff;"><strong>Valor do Carro:</strong> {{ number_format($valorCarro, 2) }} €</li>
                <li style="font-size: 16px;  color: #fff;"><strong>ISV:</strong> {{ number_format($isv, 2) }} €</li>
                <li style="font-size: 16px;  color: #fff;"><strong>Custo de Serviço:</strong> {{ number_format($servicos, 2) }} €</li>
                <li style="font-size: 16px;  color: #fff;"><strong>Custo Total:</strong> {{ number_format($custoTotal, 2) }} €</li>
            </ul>


            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Obrigado por usar o nosso simulador!</p>

            <p style="font-size: 16px; margin-bottom: 20px; color: #fff;">Preencha o nosso formulário de contato para receber uma proposta de importação: <a href="https://izzycar.pt/formulario-importacao" style="color: #fff; text-decoration: underline;">Formulário de Contato</a></p>
        </div>


        <div class="footer" style="font-size: 16px; text-align: center; margin-bottom: 20px; color: #fff; margin-top: 30px;">
            <p>Equipa Izzycar</p>
            <p><i>Este é um email automático, por favor não responda diretamente a esta mensagem.</i></p>
        </div>
</body>

</html>