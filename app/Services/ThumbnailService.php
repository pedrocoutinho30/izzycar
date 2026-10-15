<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Miniaturas WebP ao lado do original (disco public), p.ex. settings/logo.png -> settings/logo-240w.webp.
 * Nunca altera nem apaga o original; se a geração falhar o site continua a servir o original.
 */
class ThumbnailService
{
    public const LOGO_WIDTH    = 240;
    public const PARTNER_WIDTH = 280;

    private const QUALITY = 82;

    /** Caminho (relativo ao disco public) da miniatura de $path com $width px de largura. */
    public static function variantPath(string $path, int $width): string
    {
        $info = pathinfo($path);
        $dir  = ($info['dirname'] ?? '.') === '.' ? '' : $info['dirname'] . '/';

        return $dir . $info['filename'] . "-{$width}w.webp";
    }

    /** Caminho da miniatura se já existir no disco, senão null (o chamador usa o original). */
    public static function existing(?string $path, int $width): ?string
    {
        if (! $path) {
            return null;
        }

        $variant = static::variantPath($path, $width);

        return Storage::disk('public')->exists($variant) ? $variant : null;
    }

    /**
     * Gera a miniatura. Devolve o caminho gerado ou null se não foi possível/necessário
     * (origem em falta, já mais pequena que $width, SVG, erro do GD).
     */
    public function generate(string $path, int $width): ?string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path) || str_ends_with(strtolower($path), '.svg')) {
            return null;
        }

        try {
            $image = (new ImageManager(Driver::class))->read($disk->get($path));

            if ($image->width() <= $width) {
                return null;
            }

            $image->scaleDown(width: $width);
            $variant = static::variantPath($path, $width);
            $disk->put($variant, $image->toWebp(self::QUALITY)->toString());

            return $variant;
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Miniatura do logo do site. O site serve o .webp irmão do logo quando existe, por isso a
     * miniatura sai dessa mesma imagem (senão do próprio ficheiro), mantendo ambas coerentes.
     */
    public function generateForLogo(string $logoPath): ?string
    {
        $webp   = preg_replace('/\.(png|jpe?g)$/i', '.webp', $logoPath);
        $source = Storage::disk('public')->exists($webp) ? $webp : $logoPath;

        return $this->generate($source, self::LOGO_WIDTH);
    }

    public function delete(?string $path, int $width): void
    {
        if ($path && ($variant = static::existing($path, $width))) {
            Storage::disk('public')->delete($variant);
        }
    }
}
