<?php

namespace App\Services;

use GuzzleHttp\Client;

/**
 * Lê um anúncio de um site de venda de automóveis a partir do seu URL e
 * devolve os dados estruturados do veículo, para pré-preencher uma cotação.
 *
 * Suporta apenas o AutoScout24 por agora — o site expõe um bloco JSON
 * (__NEXT_DATA__) com todos os dados do anúncio, sem necessitar de
 * renderização por JavaScript. O mobile.de bloqueia pedidos automatizados
 * (proteção anti-bot de nível empresarial), por isso não é suportado.
 */
class VehicleListingImportService
{
    public function import(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST) ?? '';

        if (str_contains($host, 'autoscout24.')) {
            return $this->importFromAutoScout24($url);
        }

        if (str_contains($host, 'mobile.de')) {
            return [
                'success' => false,
                'supported' => false,
                'message' => 'A importação automática ainda não está disponível para o mobile.de. Preenche os campos manualmente.',
            ];
        }

        return [
            'success' => false,
            'supported' => false,
            'message' => 'Este site ainda não é suportado. Por agora só conseguimos importar anúncios do AutoScout24.',
        ];
    }

    private function importFromAutoScout24(string $url): array
    {
        try {
            $client = new Client(['timeout' => 15]);
            $response = $client->get($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    'Accept-Language' => 'en-US,en;q=0.9,pt-PT;q=0.8',
                ],
            ]);
            $html = (string) $response->getBody();
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'supported' => true,
                'message' => 'Não foi possível aceder a este anúncio. Confirma se o link está correto.',
            ];
        }

        if (!preg_match('/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
            return [
                'success' => false,
                'supported' => true,
                'message' => 'Não foi possível encontrar os dados do anúncio nesta página.',
            ];
        }

        $data = json_decode($matches[1], true);
        $listing = $data['props']['pageProps']['listingDetails'] ?? null;

        if (!$listing) {
            return [
                'success' => false,
                'supported' => true,
                'message' => 'Não foi possível ler os dados deste anúncio.',
            ];
        }

        $vehicle = $listing['vehicle'] ?? [];

        $price = $listing['prices']['public']['priceRaw']
            ?? $listing['prices']['dealer']['priceRaw']
            ?? null;

        $year = null;
        if (!empty($vehicle['firstRegistrationDateRaw'])) {
            $year = substr($vehicle['firstRegistrationDateRaw'], 0, 4);
        }

        $fuel = $this->mapFuel(
            $vehicle['fuelCategory']['formatted'] ?? null,
            $vehicle['primaryFuel']['formatted'] ?? null
        );

        $co2 = $vehicle['co2emissionInGramPerKmWithFallback']['raw']
            ?? $vehicle['wltp']['co2EmissionsCombinedWithFallback']['raw']
            ?? null;
        if ($co2 === null && $fuel === 'Elétrico') {
            $co2 = 0;
        }

        $version = $vehicle['modelVersionInput'] ?? null;
        if ($version) {
            $version = str_replace('|', ', ', $version);
        }

        return [
            'success' => true,
            'supported' => true,
            'values' => [
                'brand' => $vehicle['make'] ?? null,
                'model' => $vehicle['model'] ?? null,
                'version' => $version,
                'year' => $year,
                'mileage' => $vehicle['mileageInKmRaw'] ?? null,
                'fuel' => $fuel,
                'price' => $price,
                'engine_capacity' => $vehicle['rawDisplacementInCCM'] ?? $vehicle['displacementInCCM'] ?? null,
                'co2' => $co2,
            ],
        ];
    }

    private function mapFuel(?string $category, ?string $primary): ?string
    {
        $text = strtolower(($category ?? '').' '.($primary ?? ''));

        if ($text === '') {
            return null;
        }

        if (str_contains($text, 'electric')) {
            return 'Elétrico';
        }

        if (str_contains($text, 'hybrid')) {
            return str_contains($text, 'diesel') ? 'Híbrido Plug-in/Diesel' : 'Híbrido Plug-in/Gasolina';
        }

        if (str_contains($text, 'diesel')) {
            return 'Diesel';
        }

        if (str_contains($text, 'petrol') || str_contains($text, 'gasoline') || str_contains($text, 'benzin')) {
            return 'Gasolina';
        }

        return null;
    }
}
