<?php

namespace App\Marketing;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serve fotos do disco "public" como JPEG redimensionado (máx. 2160 px no lado
 * maior, sem ampliar). O caminho vem sempre da base de dados, nunca do pedido.
 */
class ImageResizer
{
    public const MAX_SIDE = 2160;

    public static function response(?string $path, ?int $maxSide = null): Response
    {
        $disk = Storage::disk('public');
        abort_if(! $path || ! $disk->exists($path), 404);

        $image = (new ImageManager(new Driver()))->read($disk->get($path));
        $max = $maxSide ? min(max($maxSide, 100), self::MAX_SIDE) : self::MAX_SIDE;
        $image->scaleDown($max, $max);

        return response($image->toJpeg(90)->toString(), 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
