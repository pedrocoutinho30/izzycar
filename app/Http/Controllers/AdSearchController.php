<?php

namespace App\Http\Controllers;

use App\Models\AdSearch;
use App\Models\AdListing;
use Illuminate\Http\Request;
use App\Models\Brand;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Artisan;
use Mpdf\Tag\Li;

class AdSearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $searches = AdSearch::latest()->with(['listings'])->orderBy('created_at', 'desc')->paginate(10);
        return view('ad-searches.index', compact('searches'));
    }

    public
    function detail($listingId, Request $request)
    {
        $priceDiffPercent = $request->query('price_diff_percent');
        $avgPriceSimilar = $request->query('avg_price_similar');
        $priceDiff = $request->query('price_diff');

        $car = AdListing::findOrFail($listingId);
        return view('ad-searches.detail', compact('car', 'priceDiffPercent', 'avgPriceSimilar', 'priceDiff'));
    }

    public function show(Request $request, AdSearch $adSearch)
    {
        $query = $adSearch->listings()->orderBy('price');
        $listings = $query->orderByDesc('price')->get();
        $allListings = $adSearch->listings;

        // Pesos dos extras
        $featureWeights = [
            'teto_panoramico' => 5,
            'camara_360' => 5,
            'sensor_estacionamento_dianteiro' => 4,
            'sensor_estacionamento_traseiro' => 4,
            'apple_carplay' => 4,
            'android_auto' => 4,
            'estofos_pele' => 4,
            'banco_condutor_regulacao_eletrica' => 3,
            'bancos_memoria' => 3,
            'cruise_control_adaptativo' => 5,
            'assistente_angulo_morto' => 4,
            'conducao_autonoma_basica' => 4,
            'teto_de_abrir' => 3,
            'camara_marcha_atras' => 4,
            'assistente_estacionamento' => 3,
            'sistema_estacionamento_autonomo' => 2,
            'bluetooth' => 3,
            'porta_usb' => 2,
            'carregador_wireless' => 3,
            'sistema_navegacao' => 3,
            'estofos_alcantara' => 4,
            'estofos_tecido' => 2,
            'banco_condutor_aquecido' => 3,
            'banco_passageiro_aquecido' => 2,
            'fecho_central_sem_chave' => 3,
            'arranque_sem_chave' => 3,
            'cruise_control' => 3,
            'cruise_control_predictivo' => 4,
            'suspensao_desportiva' => 3,
            'retrovisores_retrateis' => 2,
            'assistente_mudanca_faixa' => 4,
            'controlo_proximidade' => 3,
        ];

        $evaluatedListings = $allListings->map(function ($listing) use ($allListings, $featureWeights) {
            $year = $listing->year;
            $mileage = $listing->mileage;
            $price = $listing->price;

            // Procurar similares
            $similar = $allListings->filter(function ($other) use ($listing) {
                return $other->id !== $listing->id &&
                    $other->year === $listing->year &&
                    abs($other->mileage - $listing->mileage) <= 15000;
            });

            if ($similar->isEmpty()) {
                $similar = $allListings->filter(function ($other) use ($listing) {
                    return $other->id !== $listing->id &&
                        $other->year < $listing->year;
                })->sortByDesc('year');
            }

            if ($similar->isEmpty()) {
                $similar = $allListings->filter(function ($other) use ($listing) {
                    return $other->id !== $listing->id &&
                        $other->year > $listing->year;
                })->sortBy('year');
            }

            if ($similar->isEmpty()) {
                $listing->avg_price_similar = null;
                $listing->price_diff = null;
                $listing->price_diff_percent = null;
                $listing->avg_feature_score_similar = null;
                return $listing;
            }

            $similar = $similar->filter(function ($other) use ($mileage) {
                return abs($other->mileage - $mileage) <= 15000;
            });

            if ($similar->isEmpty()) {
                $similar = $allListings->filter(function ($other) use ($listing) {
                    return $other->id !== $listing->id &&
                        $other->year !== null;
                });
            }

            $avgPrice = $similar->avg('price');
            $priceDiff = $avgPrice ? $price - $avgPrice : null;
            $priceDiffPercent = $avgPrice ? ($priceDiff / $avgPrice) * 100 : null;

            $yearPenalty = 0;
            $mileagePenalty = 0;

            foreach ($similar as $comp) {
                if ($comp->year < $year && $price < $comp->price) {
                    $yearPenalty -= 2;
                } elseif ($comp->year > $year && $price > $comp->price) {
                    $yearPenalty += 3;
                }

                if ($comp->mileage < $mileage) {
                    $mileagePenalty += 2;
                } elseif ($comp->mileage > $mileage) {
                    $mileagePenalty -= 2;
                }
            }

            $featureScore = $mileagePenalty + $yearPenalty;
            $totalCount = 4;
            foreach ($featureWeights as $feature => $weight) {
                $totalCount += $weight;
                if ($listing->$feature) {
                    $featureScore += $weight;
                }
            }



            // Média dos scores dos similares
            $similarFeatureScores = $similar->map(function ($sim) use ($featureWeights, $year, $mileage) {
                $simScore = 0;
                $count = 4;

                foreach ($featureWeights as $feature => $weight) {
                    $count += $weight;
                    if ($sim->$feature) {
                        $simScore += $weight;
                    }
                }

                return ($count > 0) ? ($simScore / $count) : 0;
            });

            $listing->avg_feature_score_similar = round($similarFeatureScores->avg(), 2);

            $listing->feature_score = round(($featureScore / $totalCount), 2);
            $listing->score_total = $listing->feature_score;

            $listing->avg_price_similar = round($avgPrice, 2);
            $listing->price_diff = round($priceDiff, 2);
            $listing->price_diff_percent = round($priceDiffPercent, 2);
            $listing->year_score = $yearPenalty;
            $listing->mileage_score = $mileagePenalty;


            return $listing;
        })->sortByDesc('score_total');

        return view('ad-searches.show', compact('adSearch', 'listings', 'evaluatedListings'));
    }


    public function old_show(Request $request, AdSearch $adSearch)
    {
        $query = $adSearch->listings()->orderBy('price');

        // Filtro de ativo


        $listings = $query->orderByDesc('price')->get();

        $allListings = $adSearch->listings;

        // // Prepara avaliação de preço por anúncio
        // $evaluatedListings = $allListings->map(function ($evaluated) use ($allListings) {
        //     $similar = $allListings->filter(function ($other) use ($evaluated) {
        //         return $other->id !== $evaluated->id &&
        //             $other->year === $evaluated->year &&
        //             abs($other->mileage - $evaluated->mileage) <= 15000;
        //     });

        //     $avgPrice = $similar->count() ? $similar->avg('price') : null;
        //     $priceDiff = $avgPrice ? $evaluated->price - $avgPrice : null;
        //     $priceDiffPercent = $avgPrice ? ($priceDiff / $avgPrice) * 100 : null;

        //     $evaluated->avg_price_similar = $avgPrice;
        //     $evaluated->price_diff = $priceDiff;
        //     $evaluated->price_diff_percent = $priceDiffPercent;

        //     return $evaluated;
        // });


        $evaluatedListings = $allListings->map(function ($listing) use ($allListings) {
            $year = $listing->year;
            $mileage = $listing->mileage;
            $price = $listing->price;

            // Prioriza carros do mesmo ano
            $similar = $allListings->filter(function ($other) use ($listing) {
                return $other->id !== $listing->id &&
                    $other->year === $listing->year &&
                    abs($other->mileage - $listing->mileage) <= 15000;
            });

            // Se não houver carros do mesmo ano suficientes, procura anos anteriores
            if ($similar->isEmpty()) {
                $similar = $allListings->filter(function ($other) use ($listing) {
                    return $other->id !== $listing->id &&
                        $other->year < $listing->year;
                })->sortByDesc('year');
            }

            // Se ainda assim não houver, procura anos mais recentes
            if ($similar->isEmpty()) {
                $similar = $allListings->filter(function ($other) use ($listing) {
                    return $other->id !== $listing->id &&
                        $other->year > $listing->year;
                })->sortBy('year');
            }

            // Se nenhum semelhante em ano, retorna sem avaliação
            if ($similar->isEmpty()) {
                $listing->avg_price_similar = null;
                $listing->price_diff = null;
                $listing->price_diff_percent = null;
                return $listing;
            }

            // Aplica lógica de comparação por KMs (tenta 15.000, senão mais largo)
            $similar = $similar->filter(function ($other) use ($mileage) {
                return abs($other->mileage - $mileage) <= 15000;
            });

            if ($similar->isEmpty()) {
                $similar = $allListings->filter(function ($other) use ($listing) {
                    return $other->id !== $listing->id &&
                        $other->year !== null;
                });
            }

            // Calcula preço médio dos semelhantes
            $avgPrice = $similar->avg('price');

            // Avaliação baseada na comparação com anteriores ou posteriores
            $priceDiff = $avgPrice ? $price - $avgPrice : null;
            $priceDiffPercent = $avgPrice ? ($priceDiff / $avgPrice) * 100 : null;

            $yearPenalty = 0;
            $mileagePenalty = 0;

            foreach ($similar as $comp) {
                // Penalidade/ajuste baseado no ano
                if ($comp->year < $year && $price < $comp->price) {
                    $yearPenalty -= 2; // vantagem
                } elseif ($comp->year > $year && $price > $comp->price) {
                    $yearPenalty += 3; // penalização
                }

                // Penalidade/ajuste por km
                if ($comp->mileage < $mileage) {
                    $mileagePenalty += 2;
                } elseif ($comp->mileage > $mileage) {
                    $mileagePenalty -= 2;
                }
            }

            /*
             Resumo dos critérios:
                5: Muito valorizado, pode influenciar diretamente a decisão de compra.
                4: Bastante valorizado, mas não crítico.
                3: Útil e desejável, mas esperado em muitos modelos.
                2: Valor secundário ou já muito comum.
                1 ou 0: Quase irrelevante ou considerado obsoleto (nenhum no teu caso).
            */
            $featureWeights = [
                'teto_panoramico' => 5,
                'camara_360' => 5,
                'sensor_estacionamento_dianteiro' => 4,
                'sensor_estacionamento_traseiro' => 4,
                'apple_carplay' => 4,
                'android_auto' => 4,
                'estofos_pele' => 4,
                'banco_condutor_regulacao_eletrica' => 3,
                'bancos_memoria' => 3,
                'cruise_control_adaptativo' => 5,
                'assistente_angulo_morto' => 4,
                'conducao_autonoma_basica' => 4,
                'teto_de_abrir' => 3,
                'camara_marcha_atras' => 4,
                'assistente_estacionamento' => 3,
                'sistema_estacionamento_autonomo' => 2,
                'bluetooth' => 3,
                'porta_usb' => 2,
                'carregador_wireless' => 3,
                'sistema_navegacao' => 3,
                'estofos_alcantara' => 4,
                'estofos_tecido' => 2,
                'banco_condutor_aquecido' => 3,
                'banco_passageiro_aquecido' => 2,
                'fecho_central_sem_chave' => 3,
                'arranque_sem_chave' => 3,
                'cruise_control' => 3,
                'cruise_control_predictivo' => 4,
                'suspensao_desportiva' => 3,
                'retrovisores_retrateis' => 2,
                'assistente_mudanca_faixa' => 4,
                'controlo_proximidade' => 3,
            ];

            $featureScore = $mileagePenalty + $yearPenalty;
            $totalCount = 4;
            foreach ($featureWeights as $feature => $weight) {
                $totalCount += $weight;
                if ($listing->$feature) {
                    $featureScore += $weight;
                } else {
                    // $featureScore -= $weight; // penalização por não ter
                }
            }

            $listing->feature_score = round(($featureScore / $totalCount), 2);


            // $listing->feature_score = $featureScore / $totalCount;
            $listing->score_total =  $listing->feature_score;
            $listing->avg_price_similar = round($avgPrice, 2) / $listing->score_total;
            $listing->price_diff = round($priceDiff, 2);
            $listing->price_diff_percent = round($priceDiffPercent, 2);
            $listing->year_score = $yearPenalty;
            $listing->mileage_score = $mileagePenalty;




            return $listing;
        })->sortByDesc('score_total');

        return view('ad-searches.show', compact('adSearch', 'listings', 'evaluatedListings'));
    }

    public function form()
    {
        $combustiveis = [
            "Diesel",
            "Eléctrico",
            "Gasolina",
            "GNC",
            "GPL",
            "Híbrido (Diesel)",
            "Híbrido (Gasolina)",
            "Híbrido Plug-In",
            "Hidrogénio",
        ];


        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();
        return view('ad-searches.form', compact('combustiveis', 'brands'));
    }

    public function submit(Request $request)
    {
        // $python = '/Users/pedrocoutinho/projects/pessoais/novo_izzycar_app/venv/bin/python';
        $python = '/usr/bin/python3';
        // $script = '/Users/pedrocoutinho/projects/pessoais/novo_izzycar_app/scripts/getFromStandVirtual.py';
        $scriptPath = base_path('scripts/getFromStandVirtual.py');
        $data = $request->only(['brand', 'model', 'submodelo', 'ano_init', 'ano_fin', 'combustivel', 'descricao', 'url']);
        // Prepara os argumentos
        $args = [
            $data['brand'] ?? '',
            $data['model'] ?? '',
            $data['submodelo'] ?? '',
            $data['ano_init'] ?? '',
            $data['ano_fin'] ?? '',
            $data['combustivel'] ?? '',
            $data['descricao'] ?? '',
            $data['url'],
        ];

        $command = escapeshellcmd($python) . ' ' . escapeshellarg($script);

        foreach ($args as $arg) {
            $command .= ' ' . escapeshellarg($arg);
        }

        exec($command . ' 2>&1', $output, $return_var);

        if ($return_var !== 0) {
            // Mostra o erro
            dd($output);
        }

        return redirect()->route('ad-searches.index')->with('success', 'Pesquisa de anúncios iniciada com sucesso!');
    }

    public function importarAnuncios()
    {
        $exitCode = Artisan::call('ads:import');

        if ($exitCode === 0) {
            return back()->with('success', 'Importação de anúncios concluída com sucesso.');
        } else {
            return back()->with('error', 'Ocorreu um erro ao importar os anúncios.');
        }
    }

    public function destroy(AdSearch $adSearch)
    {
        $adSearch->delete();
        // Excluir os anúncios associados
        $adSearch->listings()->delete();
        return redirect()->route('ad-searches.index')->with('success', 'Pesquisa de anúncios excluída com sucesso!');
    }
}
