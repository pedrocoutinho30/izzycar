<?php

namespace App\Marketing;

use App\Enums\MarketingPostType;
use App\Models\MarketingPost;

/**
 * Converte o instantâneo (`data`) e as edições (`config`) de um post no
 * conteúdo normalizado que os templates desenham. As edições do utilizador
 * ficam em `config.overrides`; o instantâneo nunca é alterado por elas.
 */
class PostContent
{
    public const SITE = 'izzycar.pt';
    public const MAX_PHOTOS = 12;
    public const MAX_HIGHLIGHTS = 6;
    public const LAYOUTS_TRIO = ['trio-left', 'trio-top', 'trio-bottom'];

    /** Valores por omissão a partir do instantâneo (o que o editor mostra antes de qualquer edição). */
    public static function defaults(MarketingPost $post): array
    {
        $d = $post->data ?? [];
        if (self::kind($post->type) === 'text') {
            return [
                'title' => trim((string) ($d['title'] ?? '')),
                'subtitle' => trim((string) ($d['subtitle'] ?? '')),
                'price' => null,
                'savings' => null,
                'highlights' => [],
                'items' => TextPostData::cleanItems($d['items'] ?? []),
                'cta' => trim((string) ($d['cta'] ?? '')) ?: 'Fala connosco',
                'badge' => null,
            ];
        }
        $versao = trim((string) ($d['version'] ?? ''));
        $ano = $d['year'] ?? null;
        if (self::kind($post->type) === 'quote') {
            $comment = trim((string) ($d['comment'] ?? ''));

            return [
                'title' => '', 'subtitle' => '', 'price' => null, 'savings' => null, 'highlights' => [], 'items' => [],
                'cta' => 'Fala connosco', 'badge' => null,
                // Comentário curto = inteiro; comprido = o utilizador escolhe um excerto.
                'quote' => mb_strlen($comment) <= TestimonialPostData::MAX_QUOTE ? $comment : '',
                'show_full_name' => false,
            ];
        }
        if (self::kind($post->type) === 'import') {
            $brand = trim((string) ($d['brand'] ?? ''));
            $pais = trim((string) ($d['origin_country'] ?? ''));

            return [
                'title' => $brand !== '' ? "Mais um {$brand} entregue em Portugal!" : 'Mais uma viatura entregue em Portugal!',
                'subtitle' => $pais !== '' ? "Importado de {$pais}" : '',
                'price' => null,
                'savings' => $d['savings'] ?? null,
                'highlights' => array_slice(array_values(array_filter($d['highlights'] ?? [])), 0, 4),
                'items' => [],
                'cta' => 'Queres importar o teu? Fala connosco',
                'badge' => null,
            ];
        }
        $equip = array_values(array_filter($d['equipment'] ?? []));

        return [
            'title' => trim(($d['brand'] ?? '') . ' ' . ($d['model'] ?? '')),
            'subtitle' => trim($versao . ($versao !== '' && $ano ? ' · ' : '') . ($ano ?: '')),
            'price' => $d['price'] ?? null,
            'savings' => $d['savings'] ?? null,
            'highlights' => array_slice($equip, 0, 4),
            'items' => [],
            'cta' => $post->type === MarketingPostType::Recomendacao
                ? 'Pede uma simulação sem compromisso'
                : 'Contacta-nos para saber mais',
            'badge' => null,
        ];
    }

    /** Remove das edições os valores iguais aos da omissão: `config.overrides` só guarda diferenças reais. */
    public static function normalizeOverrides(MarketingPost $post, array $overrides): array
    {
        $def = self::defaults($post);
        $out = [];
        foreach (['title', 'subtitle', 'cta', 'badge'] as $k) {
            if (! array_key_exists($k, $overrides)) {
                continue;
            }
            $v = trim((string) $overrides[$k]);
            if ($v !== (string) ($def[$k] ?? '')) {
                $out[$k] = $v;
            }
        }
        foreach (['price', 'savings'] as $k) {
            if (! array_key_exists($k, $overrides)) {
                continue;
            }
            $v = $overrides[$k];
            $v = ($v === null || $v === '') ? null : (float) $v;
            if ($v !== ($def[$k] !== null ? (float) $def[$k] : null)) {
                $out[$k] = $v;
            }
        }
        if (isset($overrides['highlights']) && is_array($overrides['highlights'])) {
            $h = array_slice(array_values(array_filter(array_map(fn ($x) => trim((string) $x), $overrides['highlights']))), 0, self::MAX_HIGHLIGHTS);
            if ($h !== $def['highlights']) {
                $out['highlights'] = $h;
            }
        }

        if (array_key_exists('quote', $overrides) && isset($def['quote'])) {
            $q = trim((string) $overrides['quote']);
            if ($q !== (string) $def['quote']) {
                $out['quote'] = $q;
            }
        }
        if (! empty($overrides['show_full_name']) && isset($def['show_full_name'])) {
            $out['show_full_name'] = true;
        }
        if (isset($overrides['items']) && is_array($overrides['items'])) {
            $it = TextPostData::cleanItems($overrides['items']);
            if ($it !== ($def['items'] ?? [])) {
                $out['items'] = $it;
            }
        }

        return $out;
    }

    /** Família de conteúdo de um tipo de post: vehicle | text | quote | import. */
    public static function kind(MarketingPostType $type): string
    {
        return match ($type) {
            MarketingPostType::Educativo, MarketingPostType::Servico => 'text',
            MarketingPostType::Testemunho => 'quote',
            MarketingPostType::Importacao => 'import',
            default => 'vehicle',
        };
    }

    public static function priceDiffersFromSource(MarketingPost $post, array $config): bool
    {
        $o = $config['overrides'] ?? [];

        return array_key_exists('price', $o) && $o['price'] !== null
            && (float) $o['price'] !== (float) ($post->data['price'] ?? 0);
    }

    public static function build(MarketingPost $post, array $config, string $templateKey): array
    {
        $d = $post->data ?? [];
        $def = self::defaults($post);
        $o = $config['overrides'] ?? [];
        $pick = fn (string $k) => array_key_exists($k, $o) ? $o[$k] : $def[$k];

        $specs = [];
        if (! empty($d['year'])) $specs[] = ['Ano', (string) $d['year']];
        if (! empty($d['kilometers'])) $specs[] = ['Quilometragem', self::num($d['kilometers']) . ' km'];
        elseif (! empty($d['mileage'])) $specs[] = ['Quilometragem', self::num($d['mileage']) . ' km'];
        if (! empty($d['fuel'])) $specs[] = ['Combustível', (string) $d['fuel']];
        if (! empty($d['power'])) $specs[] = ['Potência', $d['power'] . ' cv'];
        if (! empty($d['transmission'])) $specs[] = ['Caixa', (string) $d['transmission']];

        $title = trim((string) $pick('title'));
        $len = mb_strlen($title);
        $price = $pick('price');
        $savings = $pick('savings');
        $subtitle = trim((string) $pick('subtitle'));
        $equipAll = array_values(array_filter($d['equipment'] ?? []));
        $highlights = array_values((array) $pick('highlights'));
        $kind = self::kind($post->type);
        $maxItems = TemplateRegistry::get($templateKey)->maxItems;
        $items = $kind === 'text' ? array_slice(TextPostData::cleanItems($pick('items') ?? []), 0, $maxItems) : [];

        $quote = [];
        if ($kind === 'quote') {
            $q = trim((string) $pick('quote'));
            $rating = (float) ($d['rating'] ?? 0);
            $quote = [
                'quote' => $q,
                'quote_pre' => $q !== '' && TestimonialPostData::trimmedEnds((string) ($d['comment'] ?? ''), $q)[0],
                'quote_post' => $q !== '' && TestimonialPostData::trimmedEnds((string) ($d['comment'] ?? ''), $q)[1],
                'quote_size' => mb_strlen($q) <= 90 ? 'lg' : (mb_strlen($q) <= 180 ? 'md' : 'sm'),
                'author' => ! empty($o['show_full_name']) ? (string) ($d['full_name'] ?? '') : (string) ($d['first_name'] ?? ''),
                'origin_label' => \App\Models\Testimonial::ORIGINS[$d['origin'] ?? ''] ?? '',
                'rating' => $rating,
                'rating_pct' => max(0, min(100, $rating / 5 * 100)),
            ];
        }

        return $quote + [
            'kind' => $kind,
            'items' => $items,
            'legal_check' => (bool) ($d['legal_check'] ?? false),
            'template' => $templateKey,
            'title' => $title,
            'title_size' => $len > 26 ? 'sm' : ($len > 16 ? 'md' : 'lg'),
            'subtitle' => $subtitle,
            'specs_line' => implode(' · ', array_column(array_slice($specs, 0, 4), 1)),
            'price' => $price !== null ? self::euro((float) $price) : null,
            'savings' => $savings !== null ? self::euro((float) $savings) : null,
            'highlights' => $highlights,
            'equipment' => array_slice($highlights ?: $equipAll, 0, 10),
            'specs' => $specs,
            'cta' => trim((string) $pick('cta')),
            'badge' => trim((string) ($o['badge'] ?? '')) ?: null,
            'brand' => (string) ($d['brand'] ?? ''),
            'photos' => self::photos($post, $config),
            'site' => self::SITE,
            'logo' => asset('img/marketing/izzycar-logo-dourado.png'),
        ];
    }

    /** @return list<array{url:string,x:float,y:float}> fotos escolhidas, a principal primeiro */
    public static function photos(MarketingPost $post, array $config): array
    {
        $fotos = $post->data['photos'] ?? [];
        $ordem = $config['photo_order'] ?? array_keys($fotos);
        $out = [];
        foreach ($ordem as $idx) {
            $idx = (int) $idx;
            if (! isset($fotos[$idx]) || count($out) >= self::MAX_PHOTOS) {
                continue;
            }
            $f = $fotos[$idx];
            $focal = $config['focal'][$idx] ?? null;
            $url = self::photoUrl($post, $idx, $f);
            if (! $url) {
                continue;
            }
            $out[] = [
                'url' => $url,
                'x' => (float) ($focal[0] ?? $f['focal_x'] ?? 50),
                'y' => (float) ($focal[1] ?? $f['focal_y'] ?? 50),
            ];
        }

        return $out;
    }

    /** URL (do mesmo domínio) da foto `$idx` do instantâneo: do catálogo se tem id, senão carregada para o post. */
    public static function photoUrl(MarketingPost $post, int $idx, array $f): ?string
    {
        if (isset($f['id'])) {
            return route('admin.marketing.photos.show', $f['id']);
        }

        return $post->type === MarketingPostType::Veiculo ? null : route('admin.marketing.post-photos.show', [$post, $idx]);
    }

    /** Slides do post: 1 para os templates simples; várias para o carrossel. */
    public static function slides(array $content, TemplateDefinition $tpl, array $config): array
    {
        if ($tpl->maxSlides <= 1) {
            return [['layout' => 'single']];
        }

        if ($content['kind'] === 'text') {
            $items = array_slice($content['items'], 0, max(0, $tpl->maxSlides - 2));
            $slides = [['layout' => 'cover']];
            foreach ($items as $i => $item) {
                $slides[] = ['layout' => 'item', 'item' => $item, 'n' => $i + 1, 'of' => count($items)];
            }
            $slides[] = ['layout' => 'cta'];

            return $slides;
        }

        $slides = [['layout' => 'cover']];
        $resto = array_slice($content['photos'], 1);
        $porSlide = ($config['gallery_photos_per_slide'] ?? 1) == 3 ? 3 : 1;
        $extras = (! empty($content['equipment']) ? 1 : 0) + (! empty($content['specs']) ? 1 : 0) + 1; // equip, specs, CTA
        $maxFotoSlides = max(0, $tpl->maxSlides - 1 - $extras);

        foreach (array_slice(array_chunk($resto, $porSlide), 0, $maxFotoSlides) as $i => $grupo) {
            $layout = $config['gallery_layouts'][count($slides)] ?? null;
            $n = count($grupo);
            $auto = $n === 3 ? self::LAYOUTS_TRIO[$i % 3] : ($n === 2 ? 'duo' : 'full');
            $slides[] = ['layout' => in_array($layout, [...self::LAYOUTS_TRIO, 'duo', 'full'], true) && self::fits($layout, $n) ? $layout : $auto, 'photos' => $grupo];
        }
        if (! empty($content['equipment'])) $slides[] = ['layout' => 'equipment'];
        if (! empty($content['specs'])) $slides[] = ['layout' => 'specs'];
        $slides[] = ['layout' => 'cta'];

        return $slides;
    }

    private static function fits(string $layout, int $n): bool
    {
        return match (true) {
            str_starts_with($layout, 'trio') => $n === 3,
            $layout === 'duo' => $n === 2,
            default => $n === 1,
        };
    }

    public static function euro(float $v): string
    {
        return number_format($v, 0, ',', '.') . ' €';
    }

    private static function num($v): string
    {
        return number_format((float) $v, 0, ',', '.');
    }
}
