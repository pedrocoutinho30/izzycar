<?php

namespace App\Services;

use App\Models\RadarListing;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Usa a OpenAI para estimar, a partir da marca/modelo/versão/potência de cada
 * anúncio, um "multiplicador de preço de trim": quanto essa versão específica
 * costuma custar a mais (ou a menos) do que a versão base do mesmo modelo,
 * segundo o conhecimento geral de mercado da IA (ex.: um Porsche Taycan
 * "Turbo S" custa tipicamente bastante mais do que um "4S" do mesmo ano).
 *
 * Isto existe porque RadarValueScoreService compara anúncios da MESMA
 * pesquisa (mesmo modelo) só por preço/km/ano - sem isto, uma versão topo de
 * gama parece sempre "má compra" só por ser mais cara, mesmo sendo um preço
 * justo para o que é. O multiplicador é usado para ajustar o preço antes do
 * score (ver RadarValueScoreService::fetchRows), nunca para mostrar ao
 * utilizador em vez do preço real do anúncio.
 *
 * Chamado só uma vez por anúncio (ver ClassifyRadarListingTrims), nunca a
 * pedido de uma página - o resultado fica guardado em
 * radar_listings.trim_price_multiplier.
 */
class RadarTrimPriceAiService
{
    private const MODEL = 'gpt-4o';

    /**
     * @param  Collection<int, RadarListing>  $listings  anúncios da MESMA pesquisa (mesmo make/model)
     * @return array{success: bool, results?: array<int, float>, error?: string} results: id do anúncio => multiplicador
     */
    public function classifyBatch(string $make, string $model, Collection $listings): array
    {
        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'OPENAI_API_KEY não configurada.'];
        }

        if ($listings->isEmpty()) {
            return ['success' => true, 'results' => []];
        }

        try {
            $guzzle = new Client(['timeout' => 60]);

            $response = $guzzle->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => self::MODEL,
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0,
                    'max_tokens' => 3000,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->buildSystemPrompt()],
                        ['role' => 'user', 'content' => $this->buildUserPrompt($make, $model, $listings)],
                    ],
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $content = $body['choices'][0]['message']['content'] ?? null;
            $decoded = $content ? json_decode($content, true) : null;

            if (!is_array($decoded) || !isset($decoded['results']) || !is_array($decoded['results'])) {
                Log::warning('RadarTrimPriceAiService: resposta da IA não é um JSON válido', ['content' => $content]);

                return ['success' => false, 'error' => 'Resposta da IA em formato inválido.'];
            }

            return ['success' => true, 'results' => $this->sanitize($decoded['results'], $listings)];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
            $message = $errorBody['error']['message'] ?? $e->getMessage();

            return ['success' => false, 'error' => 'Erro da API OpenAI: ' . $message];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Erro ao contactar a IA: ' . $e->getMessage()];
        }
    }

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
És um especialista em mercado automóvel europeu. Vais receber uma lista de anúncios de venda de carros, todos da MESMA marca e modelo, cada um com um id, versão/trim (texto livre, tal como aparece no anúncio, nalguns casos em alemão) e potência em cv, quando disponível.

A tua tarefa é estimar, para CADA anúncio, um "multiplicador de preço de trim": um número que representa quanto essa versão específica costuma custar, em termos de preço de tabela (novo), COMPARADA com a versão de entrada/base do mesmo modelo (ano/geração equivalente). Usa o teu conhecimento geral sobre gamas e posicionamento de preço destes modelos.

Regras:
- A versão de entrada/base do modelo tem multiplicador 1.0.
- Uma versão claramente acima da base (mais potência, acabamento superior, ex. "S", "GTS", "Turbo") tem multiplicador MAIOR que 1.0 (ex.: 1.2 a 1.9, conforme o quão acima da base está).
- Não existem versões abaixo da base no mesmo modelo - o multiplicador nunca é inferior a 1.0.
- Se o texto da versão não tiver informação suficiente para diferenciar (vazio, genérico, ou não reconheces o trim), usa 1.0 - não adivinhes.
- Ignora extras/opcionais mencionados no texto (jantes, cor, pacotes de equipamento) - o multiplicador é só sobre o NÍVEL DE GAMA/TRIM em si, não sobre extras pontuais.
- A resposta TEM de ser um único objeto JSON válido, sem comentários, sem texto fora do JSON, no formato exato: {"results": [{"id": 123, "multiplier": 1.35}, ...]}, com uma entrada por cada id recebido.
PROMPT;
    }

    private function buildUserPrompt(string $make, string $model, Collection $listings): string
    {
        $lines = $listings->map(function (RadarListing $listing) {
            $version = trim((string) $listing->version) ?: '(sem versão indicada)';
            $power = $listing->power_hp ? "{$listing->power_hp} cv" : 'potência desconhecida';

            return "id={$listing->id} | versão: \"{$version}\" | {$power}";
        })->implode("\n");

        return "Marca/modelo: {$make} {$model}\n\nAnúncios:\n{$lines}\n\nDevolve apenas o objeto JSON com um multiplicador por cada id.";
    }

    /** @return array<int, float> */
    private function sanitize(array $results, Collection $listings): array
    {
        $validIds = $listings->pluck('id')->all();
        $sanitized = [];

        foreach ($results as $row) {
            if (!is_array($row) || !isset($row['id'], $row['multiplier'])) {
                continue;
            }

            $id = (int) $row['id'];
            $multiplier = (float) $row['multiplier'];

            if (!in_array($id, $validIds, true) || $multiplier < 1.0 || $multiplier > 5.0) {
                continue;
            }

            $sanitized[$id] = $multiplier;
        }

        return $sanitized;
    }
}
