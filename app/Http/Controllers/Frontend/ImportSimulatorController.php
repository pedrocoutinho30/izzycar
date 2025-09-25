<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ImportSimulatorController extends Controller
{




    public function index()
    {
        return view('isv.simulator');
    }

    public function calcular(Request $request)
    {

        $pais_matricula = $request->pais_matricula; // uniao-europeia ou outro
        $estado_veiculo = $request->estado_viatura; // 'novo' ou 'usado'
        $co2 = $request->co2;
        $cilindrada = $request->cilindrada;
        $combustivel = $request->combustivel;
        $tipo_combustivel = $request->tipo_combustivel; // 'gasolina', 'diesel', 'hibrido', 'hibrido_plug_in', 'gas-natural'
        $tipo_veiculo = $request->tipo_veiculo; // 'passageiros', 'uso-misto', 'mercadorias', 'autocaravana'
        $tipo_medicao = $request->tipo_medicao;


        $data_matricula = $request->data_matricula;
        $autonomiaEletrica = $request->autonomia == 'igual_superior' ? 60 : 40; // km
        $emissao_particulas = $request->combustivel === 'diesel' ?  $request->emissao_particulas : '0'; // '>0.0001' ou '0'
        $peso = 0;           // kg
        $lotacao = 0;        // nº de lugares
        $alturaCaixa = 0;    // cm
        $comprimentoCaixa = 0; // cm
        $lugaresCaixa = 0;     // nº de lugares na caixa
        $anoMatriculaUE = Carbon::parse($data_matricula)->year; // para híbridos plug-in importados UE 2015–2020

        if ($combustivel === 'eletrico') {
            $isv = 0;
            return response()->json([
                'html' => "",
                'isv' => $isv
            ]);
        }
        //01. Componente cilindrada
        $componente_cc =  $this->calculo_componente_cc($cilindrada, $tabela = 'A');

        //02. Componente ambiental
        $componente_ambiental = $this->calculo_componente_ambiental($co2, $combustivel, $tipo_medicao);

        [$faixa, $reducao] = $this->calcularIdade(Carbon::parse($data_matricula));
        if ($pais_matricula !== 'uniao-europeia') {
            $reducao = 0;
        }

        $taxa_reduzida = $this->taxa_intermedia_reduzida(
            $tabela = 'A',
            $tipo_veiculo,
            $combustivel,
            $co2,
            $autonomiaEletrica,
            $peso,
            $lotacao,
            $alturaCaixa,
            $comprimentoCaixa,
            $lugaresCaixa,
            $anoMatriculaUE
        );


        // 03. Taxa aplicável da tabela	
        $taxa_aplicavel = round(($componente_cc + $componente_ambiental) * $taxa_reduzida, 2);


        //04a. Redução de Anos de Uso (Componente Cilindrada)
        $componente_cc_reduzido = round(($componente_cc * $taxa_reduzida) * $reducao, 2);
        //04b. Redução de Anos de Uso (Componente Ambiental)
        $componente_ambiental_reduzido = round(($componente_ambiental * $taxa_reduzida) * $reducao, 2);
        $componente_ambiental_reduzido_tabela = round(($componente_ambiental * $taxa_reduzida) * $reducao, 2);
        if ($componente_ambiental_reduzido < 0) $componente_ambiental_reduzido = 0;
        //05. Agravamento Partículas
        $particulas = $this->agravamentoParticulas($emissao_particulas, $combustivel);

        $reducao_particulas =  round($particulas * $reducao, 2);
        $isv = $taxa_aplicavel - $componente_cc_reduzido - $componente_ambiental_reduzido + $particulas - $reducao_particulas;
        if ($isv < 100) $isv = 100;

        $html = "
        <table class='table table-bordered'>
            <tr><th colspan='3'>Tabela ISV aplicável: A</th></tr>
            <tr>
                <td>01. Componente cilindrada</td>
                <td>{$cilindrada} cc</td>
                <td>" . number_format($componente_cc, 2, ',', '.') . " €</td>
            </tr>
            <tr>
                <td>02. Componente ambiental</td>
                <td>{$co2} g/km</td>
                <td>" . number_format($componente_ambiental, 2, ',', '.') . " €</td>
            </tr>
            <tr>
                <td>03. Taxa aplicável da tabela</td>
                <td>" . ($taxa_reduzida * 100) . "%</td>
                <td>" . number_format($taxa_aplicavel, 2, ',', '.') . " €</td>
            </tr>
            <tr>
                <td>04a. Redução de Anos de Uso (Cilindrada) {$faixa}</td>
                <td>" . ($reducao * 100) . "%</td>
                <td>" . number_format($componente_cc_reduzido, 2, ',', '.') . " €</td>
            </tr>
            <tr>
                <td>04b. Redução de Anos de Uso (Ambiental) {$faixa}</td>
                <td>" . ($reducao * 100) . "%</td>
                <td>" . number_format($componente_ambiental_reduzido_tabela, 2, ',', '.') . " €</td>
            </tr>
            <tr>
                <td>05. Agravamento Partículas</td>
                <td>-</td>
                <td>" . number_format($particulas, 2, ',', '.') . " €</td>
            </tr>
            <tr>
                <td>05a. Redução de Anos de Uso (Partículas) {$faixa}</td>
                <td>" . ($particulas == 0 ? '-' : ($reducao * 100) . "%") . "</td>
                <td>" . number_format($reducao_particulas, 2, ',', '.') . " €</td>
            </tr>
            <tr>
                <th colspan='2'>06. Total ISV</th>
                <th>" . number_format($isv, 2, ',', '.') . " €</th>
            </tr>
        </table>
    "; // Fim da construção da tabela HTML
        // $dataMatricula = Carbon::parse($request->data_matricula);
        // [$faixa, $reducao] = $this->calcularIdade($dataMatricula);
        return response()->json([
            'html' => $html,
            'isv' => $isv
        ]);
        return view('isv.form', compact('isv'));
    }

    public function agravamentoParticulas($particulas, $combustivel,)
    {

        if ($particulas === '+0.0001' && $combustivel === 'diesel') {
            return 500; // 30% de agravamento
        }
        return 0; // Sem agravamento

    }
    public
    function taxa_intermedia_reduzida(
        $tabela,             // 'A' ou 'B'
        $tipoVeiculo = 'passageiros',        // 'passageiros', 'uso-misto', 'mercadorias', 'autocaravana'
        $combustivel,        // 'gasolina', 'diesel', 'hibrido', 'hibrido_plug_in', 'gas-natural'
        $co2 = 0,            // Em g/km
        $autonomiaEletrica = 0, // km
        $peso = 0,           // kg
        $lotacao = 0,        // nº de lugares
        $alturaCaixa = 0,    // cm
        $comprimentoCaixa = 0, // cm
        $lugaresCaixa = 0,     // nº de lugares na caixa
        $anoMatriculaUE = null // para híbridos plug-in importados UE 2015–2020
    ) {
        if ($tabela === 'A') {
            // Tabela A

            if ($combustivel === 'gasolina' || $combustivel === 'diesel') {
                if ($tipoVeiculo === 'hibrido' && $autonomiaEletrica > 50 && $co2 < 50) return 0.60;
                if ($tipoVeiculo === 'hibrido_plug_in' && $autonomiaEletrica >= 50 && $co2 < 50) return 0.25;
                if ($tipoVeiculo === 'hibrido_plug_in' && $anoMatriculaUE >= 2015 && $anoMatriculaUE <= 2020 && $autonomiaEletrica >= 25) return 0.25;
                if ($tipoVeiculo === 'gas-natural') return 0.40;
            }
            // if ($tipoVeiculo === 'passageiros') {
            //     if ($combustivel === 'hibrido' && $autonomiaEletrica > 50 && $co2 < 50) return 0.60;
            //     if ($combustivel === 'hibrido_plug_in' && $autonomiaEletrica >= 50 && $co2 < 50) return 0.25;
            //     if ($combustivel === 'hibrido_plug_in' && $anoMatriculaUE >= 2015 && $anoMatriculaUE <= 2020 && $autonomiaEletrica >= 25) return 0.25;
            //     if ($combustivel === 'gas-natural') return 0.40;
            // }
            if ($tipoVeiculo === 'uso-misto' && $peso > 2500 && $lotacao >= 7) return 0.40;
            return 1.0; // caso não se aplique nenhuma taxa
        } elseif ($tabela === 'B') {
            // Tabela B
            if ($tipoVeiculo === 'mercadorias') {
                // Caixa aberta ou sem caixa, >3 lugares, com 4x4
                if ($lugaresCaixa > 3 && $alturaCaixa > 0 /* só para identificar 4x4 */) return 0.50;
                // Uso misto com peso >2300kg, comprimento caixa ≥145cm, altura ≥130cm, sem 4x4
                if ($tipoVeiculo === 'uso-misto' && $peso > 2300 && $comprimentoCaixa >= 145 && $alturaCaixa >= 130) return 0.15;
                // Caixa aberta/sem caixa, >3 lugares, sem 4x4
                if ($lugaresCaixa > 3 && $tipoVeiculo === 'mercadorias') return 0.15;
                // Caixa fechada, sem cabina integrada, peso ≤3500 kg, >3 lugares, sem 4x4
                if ($tipoVeiculo === 'mercadorias' && $peso <= 3500 && $lugaresCaixa > 3) return 0.15;
                // Caixa aberta/fechada/sem caixa, max 3 lugares
                if ($lugaresCaixa <= 3) return 0.10;
            }
            if ($tipoVeiculo === 'autocaravana') return 0.60;

            return 1.0; // caso não se aplique nenhuma taxa
        }

        throw new \Exception("Tabela inválida ou parâmetros insuficientes.");
    }
    public function calculo_componente_ambiental($co2, $combustivel, $tipoMedicao = 'NEDC')
    {
        $taxa = 0;
        $parcelaAbater = 0;

        $co2 = floatval($co2);
        if ($combustivel === 'gas-natural') $combustivel = 'gasolina';
        if ($combustivel === 'eletrico') return 0;
        // Gasolina
        if ($combustivel === 'gasolina') {
            if ($tipoMedicao === 'NEDC') {
                if ($co2 <= 99) {
                    $taxa = 4.62;
                    $parcelaAbater = 427.00;
                } elseif ($co2 <= 115) {
                    $taxa = 8.09;
                    $parcelaAbater = 750.99;
                } elseif ($co2 <= 145) {
                    $taxa = 52.56;
                    $parcelaAbater = 5903.94;
                } elseif ($co2 <= 175) {
                    $taxa = 61.24;
                    $parcelaAbater = 7140.17;
                } elseif ($co2 <= 195) {
                    $taxa = 155.97;
                    $parcelaAbater = 23627.27;
                } else {
                    $taxa = 205.65;
                    $parcelaAbater = 33390.12;
                }
            } elseif ($tipoMedicao === 'WLTP') {
                if ($co2 <= 110) {
                    $taxa = 0.44;
                    $parcelaAbater = 43.02;
                } elseif ($co2 <= 115) {
                    $taxa = 1.10;
                    $parcelaAbater = 115.80;
                } elseif ($co2 <= 120) {
                    $taxa = 1.38;
                    $parcelaAbater = 147.79;
                } elseif ($co2 <= 130) {
                    $taxa = 5.27;
                    $parcelaAbater = 619.17;
                } elseif ($co2 <= 145) {
                    $taxa = 6.38;
                    $parcelaAbater = 762.73;
                } elseif ($co2 <= 175) {
                    $taxa = 41.54;
                    $parcelaAbater = 5819.56;
                } elseif ($co2 <= 195) {
                    $taxa = 51.38;
                    $parcelaAbater = 7247.39;
                } elseif ($co2 <= 235) {
                    $taxa = 193.01;
                    $parcelaAbater = 34190.52;
                } else {
                    $taxa = 233.81;
                    $parcelaAbater = 41910.96;
                }
            }
        }

        // Gasóleo
        elseif ($combustivel === 'diesel') {
            if ($tipoMedicao === 'NEDC') {
                if ($co2 <= 79) {
                    $taxa = 5.78;
                    $parcelaAbater = 439.04;
                } elseif ($co2 <= 95) {
                    $taxa = 23.45;
                    $parcelaAbater = 1848.58;
                } elseif ($co2 <= 120) {
                    $taxa = 79.22;
                    $parcelaAbater = 7195.63;
                } elseif ($co2 <= 140) {
                    $taxa = 175.73;
                    $parcelaAbater = 18924.92;
                } elseif ($co2 <= 160) {
                    $taxa = 195.43;
                    $parcelaAbater = 21720.92;
                } else {
                    $taxa = 268.42;
                    $parcelaAbater = 33447.90;
                }
            } elseif ($tipoMedicao === 'WLTP') {
                if ($co2 <= 110) {
                    $taxa = 1.72;
                    $parcelaAbater = 11.50;
                } elseif ($co2 <= 120) {
                    $taxa = 18.96;
                    $parcelaAbater = 1906.19;
                } elseif ($co2 <= 140) {
                    $taxa = 65.04;
                    $parcelaAbater = 7360.85;
                } elseif ($co2 <= 150) {
                    $taxa = 127.40;
                    $parcelaAbater = 16080.57;
                } elseif ($co2 <= 160) {
                    $taxa = 160.81;
                    $parcelaAbater = 21176.06;
                } elseif ($co2 <= 170) {
                    $taxa = 221.69;
                    $parcelaAbater = 29227.38;
                } elseif ($co2 <= 190) {
                    $taxa = 274.08;
                    $parcelaAbater = 36987.98;
                } else {
                    $taxa = 282.35;
                    $parcelaAbater = 38271.32;
                }
            }
        } else {
            throw new \Exception("Combustível inválido. Use 'gasolina' ou 'diesel'.");
        }

        // Componente ambiental
        $componenteAmbiental = ($co2 * $taxa) - $parcelaAbater;



        return round($componenteAmbiental, 2);
    }


    public function calculo_componente_cc($cilindrada, $tipoVeiculo = 'A')
    {
        $componente = 0;

        if ($tipoVeiculo === 'A') {
            // Tabela A - Automóveis de passageiros, ligeiros de uso misto, etc.
            if ($cilindrada <= 1000) {
                $taxa = 1.09;
                $parcelaAbater = 849.03;
            } elseif ($cilindrada <= 1250) {
                $taxa = 1.18;
                $parcelaAbater = 850.69;
            } else { // acima de 1250
                $taxa = 5.61;
                $parcelaAbater = 6194.88;
            }
        } elseif ($tipoVeiculo === 'B') {
            // Tabela B - Veículos ligeiros de mercadorias, uso misto
            if ($cilindrada <= 1250) {
                $taxa = 5.30;
                $parcelaAbater = 3331.68;
            } else { // acima de 1250
                $taxa = 12.58;
                $parcelaAbater = 12138.47;
            }
        } else {
            throw new \Exception("Tipo de veículo inválido. Use 'A' ou 'B'.");
        }

        // Cálculo do componente cilindrada
        $componente = ($cilindrada * $taxa) - $parcelaAbater;

        // Garantir que o componente não seja negativo
        if ($componente < 0) $componente = 0;

        return round($componente, 2);
    }
    public function calcularIdade($dataMatricula)
    {
        $ano = $dataMatricula->year;
        $mes = $dataMatricula->month;
        $dia = $dataMatricula->day;


        $dataMatricula = Carbon::createFromDate($ano, $mes, $dia);
        $hoje = Carbon::today();

        $idadeAnos = $dataMatricula->diffInDays($hoje) / 365.25;

        // Determinar a faixa e percentagem de redução
        if ($idadeAnos >= 0 && $idadeAnos < 1) {
            $faixa = '0-1 ano';
            $reducao = 0.10;
        } elseif ($idadeAnos >= 1 && $idadeAnos < 2) {
            $faixa = '1-2 anos';
            $reducao = 0.20;
        } elseif ($idadeAnos >= 2 && $idadeAnos < 3) {
            $faixa = '2-3 anos';
            $reducao = 0.28;
        } elseif ($idadeAnos >= 3 && $idadeAnos < 4) {
            $faixa = '3-4 anos';
            $reducao = 0.35;
        } elseif ($idadeAnos >= 4 && $idadeAnos < 5) {
            $faixa = '4-5 anos';
            $reducao = 0.43;
        } elseif ($idadeAnos >= 5 && $idadeAnos < 6) {
            $faixa = '5-6 anos';
            $reducao = 0.52;
        } elseif ($idadeAnos >= 6 && $idadeAnos < 7) {
            $faixa = '6-7 anos';
            $reducao = 0.60;
        } elseif ($idadeAnos >= 7 && $idadeAnos < 8) {
            $faixa = '7-8 anos';
            $reducao = 0.65;
        } elseif ($idadeAnos >= 8 && $idadeAnos < 9) {
            $faixa = '8-9 anos';
            $reducao = 0.70;
        } elseif ($idadeAnos >= 9 && $idadeAnos < 10) {
            $faixa = '9-10 anos';
            $reducao = 0.75;
        } elseif ($idadeAnos >= 10) {
            $faixa = 'mais de 10 anos';
            $reducao = 0.80;
        }
        return [$faixa, $reducao];
    }

    private function calcularISV($combustivel, $co2, $ano, $medicao)
    {
        $isvCo2 = 0;

        // TABELAS DE EXEMPLO (valores fictícios!)
        $tabelaCo2 = [
            'gasolina' => [
                'NEDC' => [
                    [0, 120, 5],
                    [121, 180, 15],
                    [181, 999, 30]
                ],
                'WLTP' => [
                    [0, 140, 6],
                    [141, 200, 18],
                    [201, 999, 35]
                ]
            ],
            'diesel' => [
                'NEDC' => [
                    [0, 100, 10],
                    [101, 160, 20],
                    [161, 999, 40]
                ],
                'WLTP' => [
                    [0, 120, 12],
                    [121, 180, 25],
                    [181, 999, 45]
                ]
            ]
        ];

        // Calcular ISV do CO2
        foreach ($tabelaCo2[$combustivel][$medicao] as $faixa) {
            [$min, $max, $valor] = $faixa;
            if ($co2 >= $min && $co2 <= $max) {
                $isvCo2 = $co2 * $valor;
                break;
            }
        }

        // Reduções para carros usados
        $anoAtual = date('Y');
        $idade = $anoAtual - $ano;
        $reducao = 0;

        if ($idade >= 1 && $idade <= 2) $reducao = 0.20;
        elseif ($idade <= 4) $reducao = 0.40;
        elseif ($idade <= 6) $reducao = 0.60;
        elseif ($idade <= 10) $reducao = 0.80;
        elseif ($idade > 10) $reducao = 0.90;

        $isvTotal = $isvCo2 * (1 - $reducao);

        return round($isvTotal, 2);
    }
}
