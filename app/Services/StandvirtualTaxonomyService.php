<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

/**
 * Lê a lista real de marcas/modelos do Standvirtual (Portugal), para alimentar
 * selects fechados no formulário de criação de pesquisas do radar.
 *
 * Fonte: qualquer página de listagem (ex.: `/carros/bmw`) embute em
 * __NEXT_DATA__ (props.pageProps.urqlState) o resultado de uma query GraphQL
 * "filters" com TODOS os "states" possíveis de TODOS os filtros do site - não
 * só da marca da página atual. Cada estado do filtro "filter_enum_model" tem
 * uma "condition" a dizer a que marca pertence, e devolve a lista COMPLETA de
 * modelos dessa marca (confirmado: 151 marcas, 67-86+ modelos por marca) -
 * muito mais completo e fiável do que o antigo bloco "alternativeLinks" da
 * pesquisa (que só listava as ~20 marcas/modelos mais populares e falhava de
 * forma não determinística).
 *
 * Marcas como a BMW agrupam modelos em "séries" (ex.: "Série X" em vez de
 * "iX1"/"X1"/"X3" diretamente) - esses nomes granulares vivem num filtro
 * separado, "filter_enum_engine_code" ("Sub-modelo" no site), com uma
 * condition a apontar para (marca, modelo-pai). Confirmado empiricamente que
 * o valor granular (ex.: "ix1") funciona por si só como segmento de URL
 * (/carros/bmw/ix1 filtra corretamente, tal como /carros/bmw/serie-x/ix1) -
 * por isso, sempre que existir essa expansão para um modelo, usa-se o
 * granular em vez do agrupado.
 */
class StandvirtualTaxonomyService
{
    private const CACHE_TTL_DAYS = 30;

    /**
     * Combustível e caixa confirmados empiricamente (2026-09-03) a partir de
     * anúncios reais - ver scarperAutoscout/scraper/standvirtual_filters.py.
     */
    public const FUEL_OPTIONS = [
        'diesel' => 'Diesel',
        'gaz' => 'Gasolina',
        'electric' => 'Elétrico',
        'hibride-gaz' => 'Híbrido (Gasolina)',
        'plugin-hybrid' => 'Híbrido Plug-In',
        'gpl' => 'GPL',
    ];

    public const GEAR_OPTIONS = [
        'automatic' => 'Automática',
        'manual' => 'Manual',
    ];

    /**
     * Nº de tentativas para o pedido - confirmado empiricamente (2026-09-04) que o
     * Standvirtual devolve, de forma não determinística, uma página sem o bloco
     * "filters" esperado (ex.: 3 em 4 pedidos seguidos ao mesmo URL devolveram os
     * dados certos, 1 veio vazio) - não é um erro do nosso lado.
     */
    private const MAX_ATTEMPTS = 4;

    public function getMakes(): array
    {
        $states = $this->filterStates();
        $makeState = $this->findState($states, 'filter_enum_make', []);

        $makes = $this->valuesFromState($makeState);
        usort($makes, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

        return $makes;
    }

    public function getModels(string $makeSlug): array
    {
        $makeSlug = strtolower(trim($makeSlug));
        $states = $this->filterStates();

        $modelState = $this->findState($states, 'filter_enum_model', ['filter_enum_make' => $makeSlug]);
        $baseModels = $this->valuesFromState($modelState);

        $models = [];
        foreach ($baseModels as $model) {
            // Se este modelo tiver uma expansão em "Sub-modelo" (ex.: BMW "Série X"
            // -> iX1/iX2/X1/X3/...), usa os granulares em vez do agrupado - é o que
            // o utilizador espera ver e selecionar diretamente.
            $subState = $this->findState($states, 'filter_enum_engine_code', [
                'filter_enum_make' => $makeSlug,
                'filter_enum_model' => $model['slug'],
            ]);
            $subModels = $subState ? $this->valuesFromState($subState) : [];

            if (!empty($subModels)) {
                array_push($models, ...$subModels);
            } else {
                $models[] = $model;
            }
        }

        // Dedup por slug (uma marca podia, em teoria, repetir o mesmo granular via
        // dois modelos-pai diferentes) e ordena por label.
        $models = collect($models)->unique('slug')->values()->all();
        usort($models, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

        return $models;
    }

    /**
     * Todos os "states" de todos os filtros do site, de qualquer página de
     * listagem (não é preciso ser a marca específica - o payload inclui sempre
     * tudo). Cache única para makes + models de todas as marcas, em vez de um
     * pedido HTTP por marca como antes.
     */
    private function filterStates(): array
    {
        return Cache::remember('standvirtual:taxonomy:filter-states', now()->addDays(self::CACHE_TTL_DAYS), function () {
            for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
                $states = $this->fetchFilterStates();
                if (!empty($states)) {
                    return $states;
                }
            }

            return [];
        });
    }

    private function fetchFilterStates(): array
    {
        $client = new Client(['timeout' => 15]);
        $response = $client->get('https://www.standvirtual.com/carros', [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept-Language' => 'pt-PT,pt;q=0.9,en;q=0.8',
            ],
        ]);
        $html = (string) $response->getBody();

        // Atributos extra no <script> (nonce, crossorigin) - não usar type="application/json"> literal.
        if (!preg_match('/<script id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/s', $html, $matches)) {
            return [];
        }

        $data = json_decode($matches[1], true);
        $urqlState = $data['props']['pageProps']['urqlState'] ?? [];

        foreach ($urqlState as $entry) {
            $payload = json_decode($entry['data'] ?? 'null', true);
            if (isset($payload['filters']['states'])) {
                return $payload['filters']['states'];
            }
        }

        return [];
    }

    /**
     * Encontra o "state" de um filtro cujas "conditions" batem exatamente com
     * $conditions (filterId => value). $conditions vazio = sem condições (ex.:
     * filter_enum_make, que não depende de mais nada).
     */
    private function findState(array $states, string $filterId, array $conditions): ?array
    {
        foreach ($states as $state) {
            if (($state['filterId'] ?? null) !== $filterId) {
                continue;
            }

            $actual = [];
            foreach ($state['conditions'] ?? [] as $condition) {
                $actual[$condition['filterId']] = $condition['value'];
            }

            if ($actual == $conditions) {
                return $state;
            }
        }

        return null;
    }

    /** @return array<int, array{slug: string, label: string}> */
    private function valuesFromState(?array $state): array
    {
        if (!$state) {
            return [];
        }

        $options = [];
        foreach ($state['values'] ?? [] as $group) {
            foreach ($group['values'] ?? [] as $value) {
                if (empty($value['id']) || empty($value['name'])) {
                    continue;
                }
                $options[] = ['slug' => $value['id'], 'label' => $value['name']];
            }
        }

        return $options;
    }
}
