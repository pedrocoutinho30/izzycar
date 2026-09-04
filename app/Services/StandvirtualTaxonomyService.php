<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

/**
 * Lê a lista real de marcas/modelos do Standvirtual (Portugal), para alimentar
 * selects fechados no formulário de criação de pesquisas do radar - mesmo
 * princípio do AutoscoutTaxonomyService, mas o Standvirtual só expõe as top
 * 20 marcas/modelos por popularidade (não a lista completa como a
 * AutoScout24), via os blocos "alternativeLinks" da própria pesquisa.
 *
 * Fonte: cada página de listagem (`/carros` e `/carros/{make}`) embute em
 * __NEXT_DATA__ (props.pageProps.urqlState) o resultado GraphQL da pesquisa,
 * que inclui "alternativeLinks" com os links reais para outras marcas
 * ("makes", na home) ou modelos dessa marca ("models", na página da marca).
 */
class StandvirtualTaxonomyService
{
    private const CACHE_TTL_DAYS = 30;

    /**
     * Combustível e caixa confirmados empiricamente (2026-09-03) a partir de
     * anúncios reais - ver scarperAutoscout/scraper/standvirtual_filters.py.
     * Ao contrário da AutoScout24, o Standvirtual não expõe a lista completa
     * de opções num só sítio, por isso está fixa aqui (pequena e estável).
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
     * Nº de tentativas para cada pedido - confirmado empiricamente (2026-09-04) que o
     * Standvirtual devolve, de forma não determinística, uma página sem o bloco
     * "alternativeLinks" esperado (ex.: 3 em 4 pedidos seguidos ao mesmo URL
     * devolveram os dados certos, 1 veio vazio) - não é um erro do nosso lado, mas
     * sem repetir o pedido ficava uma marca sem modelos (ex.: Mercedes-Benz) presa
     * em cache vazia durante 30 dias.
     */
    private const MAX_ATTEMPTS = 4;

    public function getMakes(): array
    {
        return Cache::remember('standvirtual:taxonomy:makes', now()->addDays(self::CACHE_TTL_DAYS), function () {
            return $this->fetchLinks('https://www.standvirtual.com/carros', 'makes', segments: 1);
        });
    }

    public function getModels(string $makeSlug): array
    {
        $makeSlug = strtolower(trim($makeSlug));

        return Cache::remember("standvirtual:taxonomy:models:{$makeSlug}", now()->addDays(self::CACHE_TTL_DAYS), function () use ($makeSlug) {
            return $this->fetchLinks('https://www.standvirtual.com/carros/'.rawurlencode($makeSlug), 'models', segments: 2);
        });
    }

    /**
     * Busca + parsing com nova tentativa automática - ver MAX_ATTEMPTS. Só desiste
     * (e devolve lista vazia) depois de todas as tentativas darem uma lista vazia.
     */
    private function fetchLinks(string $url, string $blockName, int $segments): array
    {
        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $advertSearch = $this->fetchAdvertSearch($url);
            $links = $this->linksFromBlock($advertSearch, $blockName, $segments);

            if (!empty($links)) {
                return $links;
            }
        }

        return [];
    }

    private function fetchAdvertSearch(string $url): array
    {
        $client = new Client(['timeout' => 15]);
        $response = $client->get($url, [
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
            if (isset($payload['advertSearch'])) {
                return $payload['advertSearch'];
            }
        }

        return [];
    }

    /**
     * @return array<int, array{slug: string, label: string}>
     */
    private function linksFromBlock(array $advertSearch, string $blockName, int $segments): array
    {
        $links = [];
        foreach ($advertSearch['alternativeLinks'] ?? [] as $block) {
            if (($block['name'] ?? null) === $blockName) {
                $links = $block['links'] ?? [];
                break;
            }
        }

        $options = [];
        foreach ($links as $link) {
            $path = trim(parse_url($link['url'] ?? '', PHP_URL_PATH) ?? '', '/');
            $parts = explode('/', $path); // "carros/audi" ou "carros/audi/a4"
            if (count($parts) !== $segments + 1) {
                continue;
            }
            $slug = $parts[count($parts) - 1];
            $options[] = ['slug' => $slug, 'label' => $link['title'] ?? $slug];
        }

        usort($options, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

        return $options;
    }
}
