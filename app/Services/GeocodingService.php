<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

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

    public const STATUS_OK = 'ok';
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_ERROR = 'error';

    public function __construct(private ?Client $guzzle = null)
    {
    }

    /** Compatível com o uso antigo: null quando não há resultado OU houve erro (ver lookup()). */
    public function geocode(string $query): ?array
    {
        return $this->lookup($query)['data'];
    }

    /**
     * Como geocode(), mas distingue "sem resultados" de "erro" (rede, limite do
     * Nominatim…). Nunca lança; o erro fica no log sem a morada pesquisada.
     *
     * @return array{status: string, data: ?array{lat: float, lng: float, display_name: string}}
     */
    public function lookup(string $query): array
    {
        $query = trim($query);

        if ($query === '') {
            return ['status' => self::STATUS_NOT_FOUND, 'data' => null];
        }

        try {
            $guzzle = $this->guzzle ?? new Client(['timeout' => 10]);

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

            if (! is_array($results)) {
                Log::warning('Geocoding: resposta do Nominatim inválida.');

                return ['status' => self::STATUS_ERROR, 'data' => null];
            }

            if (empty($results)) {
                return ['status' => self::STATUS_NOT_FOUND, 'data' => null];
            }

            return ['status' => self::STATUS_OK, 'data' => [
                'lat' => (float) $results[0]['lat'],
                'lng' => (float) $results[0]['lon'],
                'display_name' => $results[0]['display_name'],
            ]];
        } catch (\Throwable $e) {
            // Sem a mensagem da exceção: o Guzzle inclui o URL com a morada pesquisada.
            Log::warning('Geocoding: falha no pedido ao Nominatim.', [
                'exception' => $e::class,
                'http_status' => method_exists($e, 'getCode') ? $e->getCode() : null,
            ]);

            return ['status' => self::STATUS_ERROR, 'data' => null];
        }
    }
}
