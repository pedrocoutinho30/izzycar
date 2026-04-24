<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CarComparatorController extends Controller
{
    /**
     * Exibe a página de comparação de veículos.
     */
    public function index()
    {
        return view('admin.v2.comparator.index');
    }

    /**
     * Analisa os veículos com IA (OpenAI) e retorna a análise.
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'cars' => 'required|array|min:2|max:5',
            'cars.*.brand'   => 'required|string|max:100',
            'cars.*.model'   => 'required|string|max:100',
            'cars.*.year'    => 'required|integer|min:1980|max:2030',
            'cars.*.km'      => 'required|integer|min:0',
            'cars.*.fuel'    => 'required|string|max:50',
            'cars.*.price'   => 'required|numeric|min:0',
            'cars.*.extras'  => 'nullable|string|max:2000',
        ]);

        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'OPENAI_API_KEY não configurada. Adicione-a ao ficheiro .env.',
            ], 500);
        }

        $cars = $request->input('cars');

        // Build prompt
        $prompt = $this->buildPrompt($cars);

        try {
            $guzzle = new Client(['timeout' => 30]);

            $response = $guzzle->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'    => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role'    => 'system',
                            'content' => 'És um especialista em mercado automóvel português. Analisas veículos e dás recomendações de compra e venda baseadas em dados reais de mercado, relação qualidade-preço, quilometragem, equipamentos e potencial de revenda. Responde sempre em português europeu, de forma clara e direta.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens'  => 1200,
                    'temperature' => 0.7,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $analysis = $body['choices'][0]['message']['content'] ?? 'Sem resposta da IA.';

            return response()->json(['analysis' => $analysis]);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
            $message = $errorBody['error']['message'] ?? $e->getMessage();
            return response()->json(['error' => 'Erro da API OpenAI: ' . $message], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao contactar a IA: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Constrói o prompt para a IA com os dados dos veículos.
     */
    private function buildPrompt(array $cars): string
    {
        $lines = [];
        $lines[] = 'Analisa os seguintes ' . count($cars) . ' veículos e determina qual o melhor negócio para comprar e qual tem melhor potencial de revenda. Justifica a tua escolha.';
        $lines[] = '';

        foreach ($cars as $i => $car) {
            $n = $i + 1;
            $lines[] = "### Carro {$n}: {$car['brand']} {$car['model']} ({$car['year']})";
            $lines[] = "- Quilómetros: " . number_format((int)$car['km'], 0, ',', '.') . " km";
            $lines[] = "- Combustível: {$car['fuel']}";
            $lines[] = "- Preço: " . number_format((float)$car['price'], 2, ',', '.') . " €";
            if (!empty($car['extras'])) {
                $lines[] = "- Extras/Equipamentos: {$car['extras']}";
            }
            $lines[] = '';
        }

        $lines[] = 'Por favor:';
        $lines[] = '1. Indica qual o melhor veículo para COMPRAR (melhor relação qualidade-preço / negócio).';
        $lines[] = '2. Indica qual o veículo com maior potencial de REVENDA (mais valorizado no mercado).';
        $lines[] = '3. Dá uma pontuação de 1 a 10 a cada carro (como negócio de compra).';
        $lines[] = '4. Aponta pontos fortes e fracos de cada um.';

        return implode("\n", $lines);
    }
}
