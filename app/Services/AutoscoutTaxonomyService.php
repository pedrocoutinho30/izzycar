<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

/**
 * Lê listas reais (marcas, modelos, combustível, caixa, unidade de potência)
 * diretamente da AutoScout24, para alimentar selects fechados no formulário
 * de criação de pesquisas do radar — evita escrever à mão um valor errado
 * num search YAML (ex.: "mercedes" em vez de "mercedes-benz", "classe-e" em
 * vez de "e-klasse", ou um código de combustível que a AutoScout24 nem usa).
 *
 * Fonte: a página de listagem (`/lst`) embute em __NEXT_DATA__ um bloco
 * "taxonomy" com as opções reais de cada filtro (fuelType, gearing,
 * powerType, ...) e um bloco "interlinking" com os links reais para cada
 * marca (grupo "custom-marken"); a página de uma marca (`/lst/{make}`) traz
 * os seus modelos no grupo "topModels". É a mesma técnica já usada por
 * VehicleListingImportService e pelo scraper Python em scarperAutoscout/.
 */
class AutoscoutTaxonomyService
{
    private const CACHE_TTL_DAYS = 30;

    /**
     * Traduções PT dos labels alemães da AutoScout24, pelo código (`value`)
     * devolvido pela própria AutoScout24 - os códigos são sempre os reais,
     * só o texto mostrado ao utilizador é traduzido. Códigos novos que a
     * AutoScout24 venha a adicionar aparecem com o label alemão original
     * (fallback), em vez de desaparecerem do select.
     */
    private const FUEL_LABELS_PT = [
        '2' => 'Híbrido Plug-in (Gasolina)',
        '3' => 'Híbrido Plug-in (Diesel)',
        'B' => 'Gasolina',
        'C' => 'Gás Natural (CNG)',
        'D' => 'Diesel',
        'E' => 'Elétrico',
        'H' => 'Hidrogénio',
        'L' => 'GPL (Autogás)',
        'M' => 'Etanol',
        'O' => 'Outro',
    ];

    private const GEAR_LABELS_PT = [
        'A' => 'Automática',
        'M' => 'Manual',
        'S' => 'Semi-automática',
    ];

    private const POWERTYPE_LABELS_PT = [
        'hp' => 'CV (hp)',
        'kw' => 'kW',
    ];

    public function getMakes(): array
    {
        return Cache::remember('autoscout:taxonomy:makes', now()->addDays(self::CACHE_TTL_DAYS), function () {
            $data = $this->fetchListPageData();
            $group = $this->interlinkingGroup($data, 'custom-marken');

            $makes = [];
            foreach ($group as $link) {
                $slug = $this->slugFromUrl($link['url'] ?? '');
                if (!$slug || str_starts_with($link['url'], 'https://www.autoscout24.de/lst-')) {
                    continue; // ignora "lst-caravan", "lst-trailer", etc. - não são marcas de carro
                }
                $makes[] = ['slug' => $slug, 'label' => $link['anchorText']];
            }

            usort($makes, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

            return $makes;
        });
    }

    public function getModels(string $makeSlug): array
    {
        $makeSlug = strtolower(trim($makeSlug));

        return Cache::remember("autoscout:taxonomy:models:{$makeSlug}", now()->addDays(self::CACHE_TTL_DAYS), function () use ($makeSlug) {
            $html = $this->fetch('https://www.autoscout24.de/lst/'.rawurlencode($makeSlug));
            $data = $this->decodeNextData($html);
            $group = $this->interlinkingGroup($data, 'topModels');

            $models = [];
            $seen = [];
            foreach ($group as $link) {
                $slug = $this->slugFromUrl($link['url'] ?? '');
                if (!$slug || isset($seen[$slug])) {
                    continue;
                }
                $seen[$slug] = true;
                $models[] = ['slug' => $slug, 'label' => $this->stripMakeLabel($link['anchorText'] ?? $slug)];
            }

            usort($models, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

            return $models;
        });
    }

    /**
     * Níveis mais finos que a AutoScout24 expõe abaixo de marca/modelo, como
     * segmentos extra no path (combináveis entre si, confirmado
     * empiricamente): motorização ("mt_e-220-d"), variante de carroçaria
     * ("va_t-modell") e linha de equipamento ("tn_avantgarde"). Os 3 vêm da
     * mesma página do modelo, por isso só é feito um pedido HTTP.
     *
     * @return array{motorTypes: array<int, array{value: string, label: string}>, modelVariants: array<int, array{value: string, label: string}>, trims: array<int, array{value: string, label: string}>}
     */
    public function getSubmodelOptions(string $makeSlug, string $modelSlug): array
    {
        $makeSlug = strtolower(trim($makeSlug));
        $modelSlug = strtolower(trim($modelSlug));

        return Cache::remember("autoscout:taxonomy:submodels:{$makeSlug}:{$modelSlug}", now()->addDays(self::CACHE_TTL_DAYS), function () use ($makeSlug, $modelSlug) {
            $html = $this->fetch('https://www.autoscout24.de/lst/'.rawurlencode($makeSlug).'/'.rawurlencode($modelSlug));
            $data = $this->decodeNextData($html);

            // "12.345 Angebote für Mercedes-Benz E-Klasse" -> "Mercedes-Benz E-Klasse"
            // - o prefixo exato a retirar do anchorText de cada link, para sobrar só a parte relevante ("E 220 d").
            $makeModelLabel = null;
            if (preg_match('/für (.+)$/u', $data['props']['pageProps']['listHeaderTitle'] ?? '', $matches)) {
                $makeModelLabel = trim($matches[1]);
            }

            return [
                'motorTypes' => $this->submodelGroupOptions($data, 'motorTypes', $makeModelLabel),
                'modelVariants' => $this->submodelGroupOptions($data, 'modelVariants', $makeModelLabel),
                'trims' => $this->submodelGroupOptions($data, 'trims', $makeModelLabel),
            ];
        });
    }

    /** @return array<int, array{value: string, label: string}> */
    public function getFuelTypes(): array
    {
        return $this->taxonomyOptions('fuelType', self::FUEL_LABELS_PT);
    }

    /** @return array<int, array{value: string, label: string}> */
    public function getGearOptions(): array
    {
        return $this->taxonomyOptions('gearing', self::GEAR_LABELS_PT);
    }

    /** @return array<int, array{value: string, label: string}> */
    public function getPowerTypes(): array
    {
        return $this->taxonomyOptions('powerType', self::POWERTYPE_LABELS_PT);
    }

    private function taxonomyOptions(string $taxonomyKey, array $ptLabels): array
    {
        return Cache::remember("autoscout:taxonomy:{$taxonomyKey}", now()->addDays(self::CACHE_TTL_DAYS), function () use ($taxonomyKey, $ptLabels) {
            $data = $this->fetchListPageData();
            $options = $data['props']['pageProps']['taxonomy'][$taxonomyKey] ?? [];

            return collect($options)
                ->map(fn ($option) => [
                    'value' => (string) $option['value'],
                    'label' => $ptLabels[(string) $option['value']] ?? $option['label'],
                ])
                ->values()
                ->all();
        });
    }

    /**
     * O __NEXT_DATA__ decodificado da página geral de listagem (`/lst`, sem
     * marca) - é onde vive tanto o "taxonomy" (fuelType, gearing, powerType)
     * como o "interlinking" das marcas (custom-marken). Cache própria
     * (chave diferente de cada getter) para não repetir o pedido HTTP.
     */
    private function fetchListPageData(): array
    {
        return Cache::remember('autoscout:taxonomy:lst-page-data', now()->addDays(self::CACHE_TTL_DAYS), function () {
            return $this->decodeNextData($this->fetch('https://www.autoscout24.de/lst'));
        });
    }

    private function fetch(string $url): string
    {
        $client = new Client(['timeout' => 15]);
        $response = $client->get($url, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept-Language' => 'de-DE,de;q=0.9,en;q=0.8',
            ],
        ]);

        return (string) $response->getBody();
    }

    private function decodeNextData(string $html): array
    {
        if (!preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
            throw new \RuntimeException('Não foi possível encontrar __NEXT_DATA__ na página da AutoScout24.');
        }

        return json_decode($matches[1], true);
    }

    /**
     * @return array<int, array{url: string, anchorText: string}>
     */
    private function interlinkingGroup(array $data, string $groupId): array
    {
        $groups = $data['props']['pageProps']['interlinking'] ?? [];

        foreach ($groups as $group) {
            if (($group['id'] ?? null) === $groupId) {
                return $group['links'] ?? [];
            }
        }

        return [];
    }

    /**
     * Extrai o último segmento do path de um URL /lst/... - ex.:
     * "https://www.autoscout24.de/lst/mercedes-benz" -> "mercedes-benz"
     * "https://www.autoscout24.de/lst/mercedes-benz/e-klasse" -> "e-klasse"
     */
    private function slugFromUrl(string $url): ?string
    {
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        if ($path === '' || !str_starts_with($path, 'lst/')) {
            return null;
        }

        $parts = explode('/', substr($path, 4));

        return $parts[count($parts) - 1] ?? null;
    }

    private function stripMakeLabel(string $anchorText): string
    {
        // "Mercedes-Benz E-Klasse" -> "E-Klasse" (o anchor do topModels vem sempre prefixado com a marca)
        $parts = explode(' ', $anchorText, 2);

        return $parts[1] ?? $anchorText;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function submodelGroupOptions(array $data, string $groupId, ?string $makeModelLabel): array
    {
        $group = $this->interlinkingGroup($data, $groupId);

        $options = [];
        $seen = [];
        foreach ($group as $link) {
            $value = $this->slugFromUrl($link['url'] ?? '');
            if (!$value || isset($seen[$value])) {
                continue;
            }
            $seen[$value] = true;

            $anchorText = $link['anchorText'] ?? $value;
            $label = $anchorText;
            if ($makeModelLabel && str_starts_with($anchorText, $makeModelLabel.' ')) {
                $label = substr($anchorText, strlen($makeModelLabel) + 1);
            }

            $options[] = ['value' => $value, 'label' => $label];
        }

        usort($options, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

        return $options;
    }
}
