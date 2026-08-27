<?php

namespace App\Services;

use GuzzleHttp\Client;

/**
 * Converte um texto de localização livre (cidade, código postal, país) em
 * coordenadas geográficas. Usa o Nominatim (OpenStreetMap) — gratuito, sem
 * key nem faturação, ao contrário da Geocoding API do Google (que partilha
 * o mesmo projeto/faturação da Maps JavaScript API, já com problemas de
 * faturação nesta conta). Uso interno e de baixo volume, dentro do limite
 * de utilização razoável do Nominatim (1 pedido/segundo, com User-Agent
 * identificado, como pedido pela política de uso deles).
 */
class GeocodingService
{
    private const ENDPOINT = 'https://nominatim.openstreetmap.org/search';

    public function geocode(string $query): ?array
    {
        $query = trim($query);

        if ($query === '') {
            return null;
        }

        try {
            $guzzle = new Client(['timeout' => 10]);

            $response = $guzzle->get(self::ENDPOINT, [
                'query' => [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'IzzyCar Admin (geral@izzycar.pt)',
                ],
            ]);

            $results = json_decode($response->getBody()->getContents(), true);

            if (empty($results)) {
                return null;
            }

            return [
                'lat' => (float) $results[0]['lat'],
                'lng' => (float) $results[0]['lon'],
                'display_name' => $results[0]['display_name'],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
