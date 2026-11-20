<?php

namespace App\Marketing;

use App\Enums\MarketingFormat;
use App\Models\MarketingPost;

/**
 * Desenha os slides de um post com o partial do template. É o mesmo HTML que
 * o editor mostra e que a exportação rasteriza (pré-visualização = ficheiro).
 */
class PostRenderer
{
    /**
     * @param  array  $state  edições ainda não guardadas (template, format, overrides, photo_order, focal, gallery_*)
     * @return array{html:string,width:int,height:int,slides:int,template:string,format:string}
     */
    public static function render(MarketingPost $post, array $state = []): array
    {
        $config = array_replace($post->config ?? [], array_intersect_key($state, array_flip([
            'overrides', 'photo_order', 'focal', 'gallery_layouts', 'gallery_photos_per_slide',
        ])));
        if (isset($config['overrides'])) {
            $config['overrides'] = PostContent::normalizeOverrides($post, (array) $config['overrides']);
        }

        $tpl = TemplateRegistry::get($state['template'] ?? $post->template);
        $format = isset($state['format']) ? MarketingFormat::from($state['format']) : $post->format;
        if (! $tpl->supports($format)) {
            $format = $tpl->formats[0];
        }

        $content = PostContent::build($post, $config, $tpl->key);
        $slides = PostContent::slides($content, $tpl, $config);

        $html = view($tpl->view, [
            'content' => $content,
            'slides' => $slides,
            'format' => $format,
            'w' => $format->width(),
            'h' => $format->height(),
        ])->render();

        return [
            'html' => $html,
            'width' => $format->width(),
            'height' => $format->height(),
            'slides' => count($slides),
            'template' => $tpl->key,
            'format' => $format->value,
        ];
    }
}
