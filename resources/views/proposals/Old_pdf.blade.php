<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            max-width: 200px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
            padding: 10px;
            border-top: 1px solid #000;
        }

        .footer a {
            text-decoration: none;
            color: #000;
            font-weight: bold;
        }

        .social-icons img {
            width: 15px;
            vertical-align: middle;
            margin-left: 5px;
        }

        .proposal-details {
            margin-top: 20px;
        }

        .proposal-details th,
        .proposal-details td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .proposal-details th {
            background-color: #f2f2f2;
        }

        .information-table {
            margin-top: 30px;
            border-collapse: collapse;
        }

        .information-table th,
        .information-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .information-table th {
            background-color: #f2f2f2;
        }

        .logo {
            width: 10%;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="header" style="display: flex; width: 100%; align-items: center;">
        <!-- Logo à esquerda -->
        <div style="flex-shrink: 0;">
            <img src="{{ public_path('img/logo-transparente.jpeg') }}" alt="Logo" style="max-height: 50px;">
        </div>

        <!-- Título centralizado -->
        <div style="flex-grow: 1; text-align: center;">
            <h2 style="margin: 0;">Proposta de Importação</h2>
        </div>
    </div>
    <div class="content">
        <?php
        $insp_commision = 2;
        $myCommission = 4;
        if ($proposal->proposed_car_value < 10000) {
            $myCommission = 7;
            $insp_commision = 5;
        } else if ($proposal->proposed_car_value < 20000) {
            $myCommission = 5;
            $insp_commision = 3;
        }
        $totalCost = $proposal->commission_cost + $proposal->inspection_commission_cost + $proposal->license_plate_cost + $proposal->isv_cost + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost + $proposal->transport_cost + $proposal->proposed_car_value;
        ?>
        <h3>Detalhes da Proposta</h3>
        <div class="proposal-details">
            <table width="100%">
                <tr>
                    <th>Marca</th>
                    <td>{{ $proposal->brand }}</td>
                </tr>
                <tr>
                    <th>Modelo</th>
                    <td>{{ $proposal->model }}</td>
                </tr>
                 <tr>
                    <th>Versão</th>
                    <td>{{ $proposal->version }}</td>
                </tr>
                <tr>
                    <th>Ano</th>
                    <td>{{ $proposal->proposed_car_year_month }}</td>
                </tr>
                <tr>
                    <th>Kms</th>
                    <td>{{ $proposal->proposed_car_mileage }} km</td>
                </tr>

                <tr>
                    <th>Combustível</th>
                    <td>{{ $proposal->fuel }}</td>
                </tr>
                <tr>
                    <th>Observações</th>
                    <td>{{$proposal->proposed_car_notes}}</td>
                </tr>
                <tr>
                    <th>Valor do veículo na origem</th>
                    <td>{{ $proposal->proposed_car_value }} €</td>
                </tr>
                <tr>
                    <th>ISV</th>
                    <td>{{ $proposal->isv_cost }} €</td>
                </tr>
                <tr>
                    <th>Valor chave na mão <b>(*)</b></th>
                    <td><b>{{ round($totalCost, 0) }} €</b></td>
                </tr>
                <tr>
                    <th>Validade da proposta <b>(**)</b></th>
                    <td>{{ \Carbon\Carbon::parse($proposal->created_at)->addDays(15)->format('d-m-Y') }}</td>
                </tr>
            </table>

            <p> <b>(*)</b> O valor chave na mão inclui:
            <ul>

                <li>Valor do veículo</li>
                <li>Custos de transporte</li>
                <li>Inspeção técnica</li>
                <li>Isv</li>
                <li>Matrícula</li>
                <li>Registo no IMT e conservatória</li>
                <li>Intermediação</li>
            </ul>
            </p>
            <p> <b>(**)</b> A concretização da proposta depende sempre da disponibilidade do veículo no país de origem.</p>

        </div>
        <div style="page-break-before: always;"></div>

        <div>
            <h2>Características</h2>
            <?php

            use Illuminate\Support\Str;

            $sanitizedHtml = Str::of($proposal->proposed_car_features)->trim();

            // Exemplo com HTMLPurifier (recomendado)
            $config = \HTMLPurifier_Config::createDefault();
            $purifier = new \HTMLPurifier($config);
            $cleanHtml = $purifier->purify($sanitizedHtml);
            ?>
            {!!$cleanHtml!!}
        </div>
        <div style="page-break-before: always;"></div>

        <?php
        if ($proposal->images) {

            $images = json_decode($proposal->images, true);
        }
        ?>
        @if (!empty($images))
        <div class="images-gallery">
            @foreach($images as $index => $image)
            <div class="image-row" style="margin-top: 10px; text-align: center;">
                <div class="image-item" style="margin-bottom: 10px;">
                    <img src="{{ public_path('storage/' . $image) }}" style="width: 90%; height: auto;">
                </div>
            </div>
            @if (($index + 1) % 2 == 0 && !$loop->last)
            <div style="page-break-before: always;"></div>
            @endif
            @endforeach
        </div>
        @endif


        <div style="page-break-before: always;"></div>
        <h2>Informações Adicionais</h2>

        <p>Para iniciar o processo de importação e legalização do veículo é recomendável seguir os seguintes passos:</p>

        <h3>Vistoria Prévia na Origem (Realizada por técnico especializado da EuroVerify Auto)</h3>
        <p>Este procedimento deve ser pago no momento da solicitação. ({{$proposal->inspection_commission_cost}}€)</p>

        <h3>Pagamento ao Vendedor:</h3>
        <p>O valor do veículo será pago diretamente ao stand ou concessionário no país de origem.</p>

        <h3>Organização do Transporte e Inspeção:</h3>
        <p>Para agendar o transporte do veículo para Portugal, é necessário efetuar previamente o pagamento dos serviços de transporte e inspeção.</p>

        <h3>Legalização em Portugal:</h3>
        <p>Após a chegada do veículo a Portugal, inicia-se o processo de legalização, que inclui:</p>
        <ul>
            <li><strong>Inspeção Técnica:</strong> Realização de uma inspeção para verificar a conformidade do veículo com as normas portuguesas.</li>
            <li><strong>Pagamento do ISV:</strong> Liquidação do Imposto Sobre Veículos (ISV) diretamente às autoridades fiscais portuguesas.</li>
            <li><strong>Emissão da Matrícula:</strong> Após o pagamento do ISV, é atribuída a matrícula portuguesa ao veículo.</li>
        </ul>
        <h3>Seguro Automóvel:</h3>
        <p>Antes da entrega do veículo, o proprietário deve contratar um seguro automóvel válido em Portugal.</p>

        <h3>Pagamento Final:</h3>
        <p>No ato da entrega, é efetuado o pagamento do valor remanescente acordado.</p>

        <p>Este processo assegura que todas as etapas legais e fiscais são cumpridas, garantindo a conformidade do veículo com as exigências portuguesas.</p>

    </div>
    <div class="footer">
        <p>
            Pedro Coutinho |
            <a href="mailto:geral@izzycr.pt">geral@izzycr.pt</a> |
            <a href="tel:+351914250947">914250947</a>
        </p>
        <p class="social-icons">
            <a href="https://www.facebook.com/profile.php?id=61572831810539" target="_blank">
                Facebook
            </a>
            <a href="https://www.instagram.com/izzycarpt" target="_blank">
                Instagram
            </a>
        </p>
    </div>
</body>

</html>