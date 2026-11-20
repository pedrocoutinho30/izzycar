<?php

namespace App\Services;

use App\Marketing\CaptionLimitExceeded;
use App\Marketing\PostContent;
use App\Models\MarketingPost;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Legendas para os posts. A chave da OpenAI só existe no servidor; o prompt leva
 * apenas os factos do post (o mesmo texto que está na imagem). Sem chave ou em
 * qualquer falha há uma legenda determinística a partir dos dados reais.
 */
class MarketingCaptionService
{
    public const TONES = [
        'profissional' => 'Profissional: claro, sóbrio e rigoroso.',
        'premium' => 'Premium: elegante e aspiracional, sem exageros.',
        'comercial' => 'Comercial: direto, a destacar vantagens e a pedir contacto.',
        'informativo' => 'Informativo: explica de forma simples e útil.',
        'descontraido' => 'Descontraído: próximo e simpático, sem gíria excessiva.',
    ];

    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    private const GUIDANCE = [
        'vehicle' => 'Destaca características e benefícios concretos da viatura.',
        'text' => 'Conteúdo informativo e útil: resume o tema e convida a saber mais.',
        'quote' => 'Apresenta o testemunho com gratidão, sem exageros nem promessas.',
        'import' => 'Conta a experiência e o resultado da importação, sem expor dados pessoais.',
    ];

    /** @return array{caption:string,hashtags:string,cta:string,ai:bool,warning:?string} */
    public function generate(MarketingPost $post, string $tone, int $userId): array
    {
        $facts = $this->facts($post);
        $key = config('services.openai.key');

        if (! $key) {
            return $this->done($post, $tone, $userId, $this->fallback($facts, $post, 'Sem chave de IA configurada: legenda gerada a partir dos dados do post.'));
        }

        $this->consume($userId);

        try {
            $resp = Http::withToken($key)->timeout(25)->post(self::ENDPOINT, [
                'model' => config('marketing.ai_model'),
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $this->userPrompt($facts, $tone)],
                ],
                'response_format' => ['type' => 'json_object'],
                'max_tokens' => 700,
                'temperature' => 0.7,
            ]);
        } catch (\Throwable) {
            return $this->done($post, $tone, $userId, $this->fallback($facts, $post, 'Não foi possível contactar a IA: legenda gerada a partir dos dados do post.'));
        }

        if (! $resp->successful()) {
            return $this->done($post, $tone, $userId, $this->fallback($facts, $post, 'A IA não respondeu (erro do serviço): legenda gerada a partir dos dados do post.'));
        }

        try {
            $parsed = $this->parse((string) $resp->json('choices.0.message.content'), $facts);
        } catch (\Throwable) {
            $parsed = null;
        }

        return $parsed
            ? $this->done($post, $tone, $userId, $parsed + ['ai' => true, 'warning' => null], (int) $resp->json('usage.total_tokens'))
            : $this->done($post, $tone, $userId, $this->fallback($facts, $post, 'A IA devolveu uma resposta inválida: legenda gerada a partir dos dados do post.'));
    }

    /** Conta o pedido e recusa se o limite diário do utilizador já foi atingido (antes de chamar a OpenAI). */
    private function consume(int $userId): void
    {
        $key = 'marketing:ai:' . $userId . ':' . now()->toDateString();
        Cache::add($key, 0, now()->endOfDay());
        if (Cache::increment($key) > (int) config('marketing.ai_daily_limit')) {
            throw new CaptionLimitExceeded('Atingiste o limite diário de legendas com IA. Escreve a legenda à mão ou volta amanhã.');
        }
    }

    /** Só os factos que estão (ou podem estar) na imagem; nunca cliente, matrícula, VIN nem ids. */
    private function facts(MarketingPost $post): array
    {
        $c = PostContent::build($post, $post->config ?? [], $post->template);
        $facts = [
            'tipo' => $post->type->label(),
            'titulo' => $c['title'],
            'subtitulo' => $c['subtitle'],
            'marca' => $c['brand'],
            'caracteristicas' => array_map(fn ($s) => "{$s[0]}: {$s[1]}", $c['specs']),
            'destaques' => $c['kind'] === 'vehicle' ? $c['equipment'] : $c['highlights'],
            'preco' => $c['price'],
            'poupanca' => $c['savings'],
            'pontos' => array_map(fn ($i) => trim($i['a'] . ($i['b'] !== '' ? ' — ' . $i['b'] : ''), ' —'), $c['items']),
            'citacao' => $c['quote'] ?? null,
            'autor' => $c['author'] ?? null,
            'chamada_a_acao' => $c['cta'],
        ];

        return ['kind' => $c['kind'], 'brand' => $c['brand'], 'cta' => $c['cta'], 'view' => array_filter($facts, fn ($v) => $v !== null && $v !== '' && $v !== [])];
    }

    private function systemPrompt(): string
    {
        return 'És o gestor de redes sociais da Izzycar, empresa portuguesa de importação e venda de automóveis. '
            . 'Escreve em português de Portugal. Usa APENAS os factos fornecidos: não inventes dados, preços, prazos nem condições. '
            . 'Não faças afirmações fiscais ou legais (impostos, isenções, prazos). '
            . 'A legenda tem no máximo 900 caracteres, uma chamada à ação relevante e 5 a 8 hashtags. '
            . 'Responde só com JSON: {"caption": string, "hashtags": string[], "cta": string}.';
    }

    private function userPrompt(array $facts, string $tone): string
    {
        return 'Tom: ' . $tone . ' — ' . (self::TONES[$tone] ?? '') . "\n"
            . 'Orientação: ' . self::GUIDANCE[$facts['kind']] . "\n"
            . 'Factos do post (JSON):' . "\n" . json_encode($facts['view'], JSON_UNESCAPED_UNICODE);
    }

    /** @return array{caption:string,hashtags:string,cta:string}|null */
    private function parse(string $content, array $facts): ?array
    {
        $j = json_decode($content, true);
        if (! is_array($j)) {
            return null;
        }
        if (! is_string($j['caption'] ?? null) || (isset($j['cta']) && ! is_string($j['cta']))) {
            return null;
        }
        $caption = trim($j['caption']);
        if ($caption === '' || mb_strlen($caption) > 1500) {
            return null;
        }
        $tags = $j['hashtags'] ?? '';
        $tags = is_array($tags) ? implode(' ', array_filter($tags, 'is_string')) : (is_string($tags) ? $tags : '');
        preg_match_all('/#?([\p{L}\p{N}_]+)/u', $tags, $m);
        $hashtags = collect($m[1])->unique()->take(8)->map(fn ($t) => '#' . $t)->implode(' ');

        return [
            'caption' => $caption,
            'hashtags' => $hashtags !== '' ? $hashtags : $this->baseHashtags($facts),
            'cta' => trim((string) ($j['cta'] ?? '')) ?: $facts['cta'],
        ];
    }

    /** Legenda montada com os dados reais do post, sem IA. */
    private function fallback(array $facts, MarketingPost $post, string $warning): array
    {
        $v = $facts['view'];
        $lines = [trim(($v['titulo'] ?? '') . (isset($v['subtitulo']) ? ' · ' . $v['subtitulo'] : ''))];
        if (isset($v['citacao'])) {
            $lines = ['“' . $v['citacao'] . '”' . (isset($v['autor']) ? ' — ' . $v['autor'] : '')];
        }
        if (! empty($v['caracteristicas'])) {
            $lines[] = implode(' · ', $v['caracteristicas']);
        }
        if (! empty($v['destaques'])) {
            $lines[] = 'Destaques: ' . implode(', ', array_slice($v['destaques'], 0, 6)) . '.';
        }
        foreach ($v['pontos'] ?? [] as $p) {
            $lines[] = '• ' . $p;
        }
        if (isset($v['preco'])) {
            $lines[] = 'Preço: ' . $v['preco'];
        }
        if (isset($v['poupanca'])) {
            $lines[] = 'Poupança: ' . $v['poupanca'];
        }

        return [
            'caption' => implode("\n", array_filter($lines)),
            'hashtags' => $this->baseHashtags($facts),
            'cta' => $facts['cta'],
            'ai' => false,
            'warning' => $warning,
        ];
    }

    private function baseHashtags(array $facts): string
    {
        $porTipo = [
            'vehicle' => ['#viaturas', '#automovel'],
            'text' => ['#importacaoautomovel', '#dicasauto'],
            'quote' => ['#testemunho', '#opiniao'],
            'import' => ['#importacaoautomovel', '#automovel'],
        ][$facts['kind']];
        $marca = Str::slug($facts['brand'], '');

        return implode(' ', array_filter(['#izzycar', ...$porTipo, $marca !== '' ? '#' . $marca : null]));
    }

    private function done(MarketingPost $post, string $tone, int $userId, array $result, ?int $tokens = null): array
    {
        // O texto gerado nunca vai para o registo.
        Log::info('marketing.caption', ['post' => $post->id, 'user' => $userId, 'tone' => $tone, 'ai' => $result['ai'], 'tokens' => $tokens]);

        return $result;
    }
}
