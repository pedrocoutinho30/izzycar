<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CarAnalysisController extends Controller
{
    /**
     * Bonus de mercado estimado (€) por feature premium.
     * Usado no ajuste diferencial do PJM.
     */
    private const FEATURE_BONUSES = [
        'Keyless-go'                            => 400,
        'Jantes de liga leve'                   => 300,
        'Reconhecimento dos sinais de trânsito' => 300,
        'Apple CarPlay'                         => 400,
        'Android Auto'                          => 350,
        'Sensor de estacionamento dianteiro'    => 200,
        'Sensor de estacionamento traseiro'     => 200,
        'Câmara de marcha atrás'                => 400,
        'Teto de abrir'                         => 700,
        'Assistente de ângulo morto'            => 400,
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    // -------------------------------------------------------------------------
    // Routes
    // -------------------------------------------------------------------------

    public function index()
    {
        return view('car-analysis');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'json_file' => ['required', 'file', 'mimetypes:application/json,text/plain'],
        ]);

        $content = file_get_contents($request->file('json_file')->getRealPath());
        $raw     = json_decode($content, true);

        if (!is_array($raw) || empty($raw)) {
            return back()->withErrors(['json_file' => 'O ficheiro JSON é inválido ou está vazio.']);
        }

        $carros = $this->normalizar($raw);

        if ($carros->isEmpty()) {
            return back()->withErrors(['json_file' => 'Nenhum carro válido encontrado no ficheiro.']);
        }

        $carros   = $this->avaliar($carros);
        $metricas = $this->metricas($carros);

        return view('car-analysis', compact('carros', 'metricas'));
    }

    // -------------------------------------------------------------------------
    // 1. Normalização
    // -------------------------------------------------------------------------

    /**
     * Converte o array bruto do JSON numa Collection normalizada.
     * Descarta entradas sem preço ou ano válidos.
     */
    private function normalizar(array $raw): Collection
    {
        return collect($raw)->map(function (array $item): array {
            return [
                'marca_modelo' => trim($item['marca_modelo'] ?? ''),
                'submodelo'    => trim($item['submodelo']    ?? ''),
                'ano'          => (int)   ($item['ano']  ?? 0),
                'kms'          => $this->parseKms($item['kms']   ?? ''),
                'preco'        => $this->parsePrice($item['preco'] ?? ''),
                'localizacao'  => trim($item['localizacao'] ?? ''),
                'link'         => trim($item['link']        ?? ''),
                'features'     => is_array($item['features'] ?? null) ? $item['features'] : [],
            ];
        })->filter(fn(array $c): bool => $c['preco'] > 0 && $c['ano'] > 0)->values();
    }

    // -------------------------------------------------------------------------
    // 2. Agrupamento de comparáveis
    // -------------------------------------------------------------------------

    /**
     * Retorna a sub-collection de carros comparáveis a $alvo dentro de $todos.
     *
     * Tenta primeiro ±1 ano / ±30% kms; se o grupo tiver < 3 elementos,
     * alarga para ±2 anos / ±50% kms (fallback).
     */
    private function comparaveis(array $alvo, Collection $todos): Collection
    {
        $filtrar = function (Collection $pool, int $deltaAno, float $deltaKmsPct) use ($alvo): Collection {
            $kmsMin = $alvo['kms'] * (1 - $deltaKmsPct);
            $kmsMax = $alvo['kms'] * (1 + $deltaKmsPct);

            return $pool->filter(function (array $c) use ($alvo, $deltaAno, $kmsMin, $kmsMax): bool {
                return $c['marca_modelo'] === $alvo['marca_modelo']
                    && abs($c['ano'] - $alvo['ano']) <= $deltaAno
                    && ($alvo['kms'] === 0 || ($c['kms'] >= $kmsMin && $c['kms'] <= $kmsMax));
            });
        };

        $grupo = $filtrar($todos, 1, 0.30);

        if ($grupo->count() < 3) {
            $grupo = $filtrar($todos, 2, 0.50);
        }

        // Garante que o próprio carro entra sempre no grupo
        if ($grupo->isEmpty()) {
            $grupo = $todos->filter(fn(array $c): bool => $c['marca_modelo'] === $alvo['marca_modelo']);
        }

        return $grupo->values();
    }

    // -------------------------------------------------------------------------
    // 3 & 4. Preço Justo de Mercado (PJM)
    // -------------------------------------------------------------------------

    /**
     * Calcula o Preço Justo de Mercado para $alvo com base nos $comparaveis.
     *
     * PJM = mediana_precos
     *       + (ano_alvo      - ano_mediano)  * 500
     *       + (kms_medianos  - kms_alvo)     * 0.02
     *       + (bonus_features_alvo - media_bonus_comparaveis)
     */
    private function calcularPJM(array $alvo, Collection $comparaveis): float
    {
        $precos  = $comparaveis->pluck('preco');
        $mediana = $this->mediana($precos);

        $anoMediano = $this->mediana($comparaveis->pluck('ano'));
        $kmsMediano = $this->mediana($comparaveis->pluck('kms'));

        $ajusteAno = ($alvo['ano'] - $anoMediano) * 500;
        $ajusteKms = ($kmsMediano  - $alvo['kms']) * 0.02;

        // Ajuste de features: diferença entre o bonus do alvo e a média do grupo.
        // Se o alvo tem mais features que a mediana do grupo → PJM sobe → score melhora.
        $bonusAlvo  = $this->featureBonus($alvo['features'] ?? []);
        $mediaBonus = $comparaveis->avg(fn(array $c): float => $this->featureBonus($c['features'] ?? [])) ?? 0.0;
        $ajusteFeatures = $bonusAlvo - $mediaBonus;

        return $mediana + $ajusteAno + $ajusteKms + $ajusteFeatures;
    }

    // -------------------------------------------------------------------------
    // 5. Score
    // -------------------------------------------------------------------------

    /**
     * score = (preco_real - PJM) / PJM
     * Positivo → caro; negativo → barato.
     */
    private function score(float $precoReal, float $pjm): float
    {
        if ($pjm == 0) {
            return 0.0;
        }

        return ($precoReal - $pjm) / $pjm;
    }

    // -------------------------------------------------------------------------
    // 6. Classificação
    // -------------------------------------------------------------------------

    private function classificar(float $score): string
    {
        if ($score < -0.10) return 'Muito barato';
        if ($score < -0.05) return 'Bom negócio';
        if ($score <=  0.05) return 'Justo';
        if ($score <=  0.10) return 'Caro';
        return 'Muito caro';
    }

    // -------------------------------------------------------------------------
    // 7. Avaliação principal
    // -------------------------------------------------------------------------

    /**
     * Enriquece cada carro com: pjm, score, classificacao, num_comparaveis, num_extras.
     */
    private function avaliar(Collection $carros): Collection
    {
        return $carros->map(function (array $carro) use ($carros): array {
            $grupo = $this->comparaveis($carro, $carros);
            $pjm   = $this->calcularPJM($carro, $grupo);
            $score = $this->score($carro['preco'], $pjm);

            $numExtras = count(array_filter($carro['features'] ?? []));

            return array_merge($carro, [
                'pjm'             => $pjm,
                'score'           => $score,
                'classificacao'   => $this->classificar($score),
                'num_comparaveis' => $grupo->count(),
                'num_extras'      => $numExtras,
            ]);
        })->sortBy('score')->values();
    }

    // -------------------------------------------------------------------------
    // Métricas globais para o dashboard
    // -------------------------------------------------------------------------

    private function metricas(Collection $carros): array
    {
        $precos = $carros->pluck('preco');
        $kms    = $carros->pluck('kms');

        return [
            'total'         => $carros->count(),
            'media_preco'   => $precos->avg(),
            'mediano_preco' => $this->mediana($precos),
            'min_preco'     => $precos->min(),
            'max_preco'     => $precos->max(),
            'media_kms'     => $kms->avg(),
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers de parsing / estatística
    // -------------------------------------------------------------------------

    /** "21 990 €" → 21990.0 */
    private function parsePrice(string $value): float
    {
        // Remove tudo excepto dígitos, vírgula e ponto
        $clean = preg_replace('/[^\d,.]/', '', $value);
        // Normaliza separador decimal para ponto
        $clean = str_replace(',', '.', $clean);
        return (float) $clean;
    }

    /** Soma dos bónus das features premium presentes num carro. */
    private function featureBonus(array $features): float
    {
        $total = 0.0;
        foreach (self::FEATURE_BONUSES as $feature => $bonus) {
            if (!empty($features[$feature])) {
                $total += $bonus;
            }
        }
        return $total;
    }

    /** "20 027 km" → 20027 */
    private function parseKms(string $value): int
    {
        return (int) preg_replace('/[^\d]/', '', $value);
    }

    /** Mediana de uma Collection de valores numéricos. */
    private function mediana(Collection $valores): float
    {
        $sorted = $valores->sort()->values();
        $n      = $sorted->count();

        if ($n === 0) {
            return 0.0;
        }

        $mid = (int) floor($n / 2);

        return $n % 2 === 0
            ? ($sorted[$mid - 1] + $sorted[$mid]) / 2.0
            : (float) $sorted[$mid];
    }
}
