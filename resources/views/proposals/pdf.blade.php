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


        <!-- Tabela com a Informação do Esperado -->
        <h4>Informação Esperada</h4>
        <div class="information-table">
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
                    <th>Ano</th>
                    <td>{{ $proposal->year }}</td>
                </tr>
                <tr>
                    <th>Kms</th>
                    <td>{{ $proposal->mileage }} km</td>
                </tr>
                <tr>
                    <th>Cilindrada</th>
                    <td>{{ $proposal->engine_capacity }} cc</td>
                </tr>
                <tr>
                    <th>CO2</th>
                    <td>{{ $proposal->co2 }} g/km</td>
                </tr>
                <tr>
                    <th>Combustível</th>
                    <td>{{ $proposal->fuel }}</td>
                </tr>
                <tr>
                    <th>Valor Máximo</th>
                    <td>{{ $proposal->value }} €</td>
                </tr>
                <tr>
                    <th>Observações</th>
                    <td>{!!$proposal->notes !!}</td>
                </tr>

            </table>
        </div>
        <div style="page-break-before: always;"></div>



        <h4>Carro proposto</h4>
        <div class="information-table">
            <table width="100%">
                <tr>
                    <th>Kms</th>
                    <td>{{$proposal->proposed_car_mileage}}</td>
                </tr>
                <tr>
                    <th>Mês/Ano</th>
                    <td>{{$proposal->proposed_car_year_month}}</td>
                </tr>
                <tr>
                    <th>Valor</th>
                    <td>{{$proposal->proposed_car_value}} €</td>
                </tr>
                <tr>
                    <th>Observações</th>
                    <td></td>
                </tr>
                <tr>
                    <th>Valor do carro</th>
                    <td>{{$proposal->proposed_car_value }} €</td>
                </tr>
            </table>
        </div>
        <div>
            <h2>Características</h2>
            {!!$proposal->proposed_car_features!!}
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

        <?php
        $totalCost = $proposal->commission_cost + $proposal->inspection_commission_cost + $proposal->license_plate_cost + $proposal->isv_cost + $proposal->registration_cost + $proposal->imt_cost + $proposal->ipo_cost + $proposal->transport_cost + $proposal->proposed_car_value;
        ?>
        <h4>Custos</h4>
        <div class="information-table">
            <table width="100%">
                <tr>
                    <th>*Transporte</th>
                    <td>{{$proposal->transport_cost}} €</td>
                </tr>
                <tr>
                    <th>Custo inspeção</th>
                    <td>{{$proposal->ipo_cost}} €</td>
                </tr>
                <tr>
                    <th>Custos IMT</th>
                    <td>{{$proposal->imt_cost}} €</td>
                </tr>
                <tr>
                    <th>Custos Registo Automóvel</th>
                    <td>{{$proposal->registration_cost}} €</td>
                </tr>
                <tr>
                    <th>*ISV</th>
                    <td>{{$proposal->isv_cost}} €</td>
                </tr>
                <tr>
                    <th>Matriculas</th>
                    <td>{{$proposal->license_plate_cost}} €</td>
                </tr>
                <tr>
                    <th>Valor do carro</th>
                    <td>{{$proposal->proposed_car_value}} €</td>
                </tr>

                <?php
                $insp_commision = 2;
                $myCommission = 4;
                if ($proposal->proposed_car_value < 10000) {
                    $myCommission = 7;
                    $insp_commision = 5;   
                }else if ($proposal->proposed_car_value < 20000) {
                    $myCommission = 5;
                    $insp_commision = 3;
                }
                ?>
                <tr>
                    <th>*Comissão vistoria ({{$insp_commision}}%)</th>
                    <td>{{$proposal->inspection_commission_cost}} €</td>
                </tr>
                <tr>
                    <th>*Comissão vendedor ({{$myCommission}}%)</th>
                    <td>{{$proposal->commission_cost}} €</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <td>{{ $totalCost }} €</td>
                </tr>
            </table>
            <p> * Estes valores são demonstrativos, embora bastante aproximados dos valores finais, podendo
                alterar conforme o valor e características do carro.</p>
        </div>
        <div style="page-break-before: always;"></div>
        <h2>Notas</h2>

        <p>Para iniciar o processo de importação e legalização do veículo é recomendável seguir os seguintes passos:</p>

        <h3>Vistoria Prévia na Origem:</h3>
        <p>Embora opcional, é altamente recomendada uma inspeção do veículo ainda na Alemanha. Este procedimento deve ser pago no momento da solicitação.</p>

        <h3>Pagamento ao Vendedor:</h3>
        <p>O valor do veículo será pago diretamente ao stand ou concessionário alemão.</p>

        <h3>Organização do Transporte e Inspeção:</h3>
        <p>Para agendar o transporte do veículo para Portugal, é necessário efetuar previamente o pagamento dos serviços de transporte e inspeção.</p>

        <h3>Legalização em Portugal:</h3>
        <p>Após a chegada do veículo a Portugal, inicia-se o processo de legalização, que inclui:</p>
        <ul>
            <li><strong>Inspeção Técnica:</strong> Realização de uma inspeção para verificar a conformidade do veículo com as normas portuguesas.</li>
            <li><strong>Pagamento do ISV:</strong> Liquidação do Imposto Sobre Veículos (ISV) diretamente às autoridades fiscais portuguesas.</li>
            <li><strong>Emissão da Matrícula:</strong> Após o pagamento do ISV, é atribuída a matrícula portuguesa ao veículo.</li>
        </ul>
        <div style="page-break-before: always;"></div>
        <h3>Seguro Automóvel:</h3>
        <p>Antes da entrega do veículo, o proprietário deve contratar um seguro automóvel válido em Portugal.</p>

        <h3>Pagamento Final:</h3>
        <p>No ato da entrega, é efetuado o pagamento do valor remanescente acordado.</p>

        <p>Este processo assegura que todas as etapas legais e fiscais são cumpridas, garantindo a conformidade do veículo com as exigências portuguesas.</p>

    </div>
    <div class="footer">
        <p>
            Pedro Coutinho |
            <a href="mailto:izzycarpt@gmail.com">izzycarpt@gmail.com</a> |
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