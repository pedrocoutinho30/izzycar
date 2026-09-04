<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

/**
 * Lê a lista real de marcas e modelos do Carmine.pt, para alimentar os selects
 * fechados no formulário de criação de pesquisas do radar.
 *
 * O Carmine não separa marca/modelo no URL como a AutoScout24/Standvirtual -
 * usa um único slug combinado "{make}-{model}" (ex. "renault-clio"), e não
 * expõe nenhuma lista de modelos por marca (o único sitemap que os lista
 * mistura modelo+localização no mesmo padrão de slug, impossível de separar).
 * Por isso os modelos são descobertos empiricamente: lê-se o "model.name" de
 * anúncios reais dessa marca (várias páginas, para apanhar os modelos menos
 * comuns), constrói-se o slug candidato e CONFIRMA-SE contra o Carmine (o
 * pedido só filtra mesmo por modelo se o site devolver um filtro
 * "filters[model][id]" na resposta - caso contrário ignora silenciosamente o
 * modelo e devolve todos os anúncios da marca, por isso nunca se pode confiar
 * num slug de modelo sem esta confirmação).
 */
class CarmineTaxonomyService
{
    private const CACHE_TTL_DAYS = 30;

    /** Nº de páginas de anúncios reais amostradas para descobrir nomes de modelo por marca. */
    private const MODEL_SAMPLE_PAGES = 5;

    /** ucwords() parte do princípio que cada marca é "Palavra Palavra", o que estraga siglas. */
    private const LABEL_OVERRIDES = [
        'BMW' => 'BMW',
        'BYD' => 'BYD',
        'DS' => 'DS',
        'KGM' => 'KGM',
        'MAN' => 'MAN',
        'MG' => 'MG',
    ];

    public function getMakes(): array
    {
        return Cache::remember('carmine:taxonomy:makes', now()->addDays(self::CACHE_TTL_DAYS), function () {
            $pageProps = $this->fetchPageProps('https://carmine.pt/carros-usados');
            $brands = $pageProps['brands'] ?? [];

            $makes = [];
            foreach ($brands as $brand) {
                if (($brand['type'] ?? null) !== 'make' || empty($brand['text'])) {
                    continue;
                }
                $label = self::LABEL_OVERRIDES[$brand['text']] ?? ucwords(mb_strtolower($brand['text']));
                $slug = $this->slugify($brand['text']);
                $makes[] = ['slug' => $slug, 'label' => $label];
            }

            usort($makes, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

            return $makes;
        });
    }

    /**
     * Modelos reais e CONFIRMADOS de uma marca - ver docblock da classe para o
     * porquê desta descoberta em duas fases (amostra + confirmação).
     *
     * @return array<int, array{slug: string, label: string}>
     */
    public function getModels(string $makeSlug): array
    {
        $makeSlug = strtolower(trim($makeSlug));

        return Cache::remember("carmine:taxonomy:models:{$makeSlug}", now()->addDays(self::CACHE_TTL_DAYS), function () use ($makeSlug) {
            $models = [];
            foreach ($this->sampleModelNames($makeSlug) as $name) {
                $slug = $this->slugify($name);
                if (isset($models[$slug])) {
                    continue;
                }
                $confirmedLabel = $this->confirmModelSlug($makeSlug, $slug);
                if ($confirmedLabel !== null) {
                    $models[$slug] = $confirmedLabel;
                }
            }

            $result = [];
            foreach ($models as $slug => $label) {
                $result[] = ['slug' => $slug, 'label' => $label];
            }
            usort($result, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

            return $result;
        });
    }

    /** Nomes de modelo (não confirmados) vistos em anúncios reais desta marca, várias páginas. */
    private function sampleModelNames(string $makeSlug): array
    {
        $names = [];
        for ($page = 1; $page <= self::MODEL_SAMPLE_PAGES; $page++) {
            $pageProps = $this->fetchPageProps("https://carmine.pt/carros-usados/{$makeSlug}?page={$page}");
            $listings = $pageProps['classifieds']['classifiedList'] ?? [];
            if (empty($listings)) {
                break;
            }
            foreach ($listings as $listing) {
                $name = $listing['model']['name'] ?? null;
                if (!empty($name)) {
                    $names[$name] = true;
                }
            }
        }

        return array_keys($names);
    }

    /** Confirma que "{makeSlug}-{modelSlug}" filtra mesmo por modelo (e não só por marca) - devolve o label real, ou null. */
    private function confirmModelSlug(string $makeSlug, string $modelSlug): ?string
    {
        $pageProps = $this->fetchPageProps("https://carmine.pt/carros-usados/{$makeSlug}-{$modelSlug}");
        $filters = $pageProps['initialState']['filters'] ?? [];

        foreach ($filters as $filter) {
            if (($filter['field'] ?? null) === 'filters[model][id]') {
                return $filter['label'] ?? null;
            }
        }

        return null;
    }

    private function fetchPageProps(string $url): array
    {
        $client = new Client(['timeout' => 15]);
        $response = $client->get($url, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept-Language' => 'pt-PT,pt;q=0.9,en;q=0.8',
            ],
        ]);
        $html = (string) $response->getBody();

        if (!preg_match('/<script id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/s', $html, $matches)) {
            throw new \RuntimeException("Não foi possível encontrar __NEXT_DATA__ na página do Carmine.pt: {$url}");
        }

        $data = json_decode($matches[1], true);

        return $data['props']['pageProps'] ?? [];
    }

    private function slugify(string $text): string
    {
        $slug = mb_strtolower(trim($text));
        $slug = str_replace(['á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç'], ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c'], $slug);

        return preg_replace('/[^a-z0-9]+/', '-', $slug);
    }
}
