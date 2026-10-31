<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageOptimizerService
{
    private const QUALITY_AVIF = 65;
    private const QUALITY_WEBP = 70;
    private const MAX_WIDTH    = 1920;
    private const QUALITY_ORIGINAL = 82;
    private const MAX_ORIGINAL_SIDE = 1920;
    private const SIZES        = [
        'thumb'  => 300,
        'medium' => 800,
        'large'  => 1200,
    ];

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(Driver::class);
    }

    /**
     * Optimise an uploaded image: generate AVIF + WebP for each size variant.
     * Returns the canonical path (large AVIF) to persist in the database.
     *
     * @param  UploadedFile  $file         Uploaded file from the request.
     * @param  string        $storagePath  Storage folder, e.g. "vehicles/5".
     * @return string  Canonical path relative to the public storage disk.
     */
    public function optimize(UploadedFile $file, string $storagePath): string
    {
        $source = $this->manager->read($file->getRealPath());

        // Resize original if it exceeds the maximum allowed width.
        if ($source->width() > self::MAX_WIDTH) {
            $source->scaleDown(width: self::MAX_WIDTH);
        }

        $uuid          = (string) Str::uuid();
        $canonicalPath = null;

        foreach (self::SIZES as $sizeName => $maxWidth) {
            // Clone the (already capped) source for each size variant.
            $variant = clone $source;

            if ($variant->width() > $maxWidth) {
                $variant->scaleDown(width: $maxWidth);
            }

            // AVIF — primary format.
            $avifPath = "{$storagePath}/{$uuid}_{$sizeName}.avif";
            Storage::disk('public')->put(
                $avifPath,
                $variant->toAvif(self::QUALITY_AVIF)->toString()
            );

            // WebP — fallback format.
            $webpPath = "{$storagePath}/{$uuid}_{$sizeName}.webp";
            Storage::disk('public')->put(
                $webpPath,
                $variant->toWebp(self::QUALITY_WEBP)->toString()
            );

            if ($sizeName === 'large') {
                $canonicalPath = $avifPath;
            }
        }

        return $canonicalPath;
    }

    /** O GD do servidor consegue escrever WebP? Sem isso mantém-se o formato original. */
    public static function supportsWebp(): bool
    {
        return function_exists('imagewebp')
            && function_exists('gd_info')
            && ! empty(gd_info()['WebP Support']);
    }

    /**
     * Guarda a foto no disco public já otimizada: lado maior limitado a 1920 px, qualidade ~82,
     * WebP se o GD suportar (senão mantém JPEG/PNG). A orientação EXIF é aplicada aos pixéis na
     * leitura; os metadados EXIF (GPS, câmara) não são reescritos. Só usa a versão otimizada se
     * ficar mais pequena que o original; se algo falhar, guarda o original tal e qual.
     *
     * @return string Caminho relativo ao disco public.
     */
    public function storeOptimized(UploadedFile $file, string $storagePath): string
    {
        try {
            $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());

            // GIF/SVG/AVIF e afins: não mexer (animação, vetor).
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                return $file->store($storagePath, 'public');
            }

            $image = $this->manager->read($file->getRealPath());
            $image->scaleDown(self::MAX_ORIGINAL_SIDE, self::MAX_ORIGINAL_SIDE);

            if (self::supportsWebp()) {
                $encoded = $image->toWebp(self::QUALITY_ORIGINAL)->toString();
                $newExt  = 'webp';
            } elseif ($ext === 'png') {
                $encoded = $image->toPng()->toString();
                $newExt  = 'png';
            } else {
                $encoded = $image->toJpeg(self::QUALITY_ORIGINAL)->toString();
                $newExt  = 'jpg';
            }

            if (strlen($encoded) >= $file->getSize()) {
                return $file->store($storagePath, 'public');
            }

            $path = trim($storagePath, '/') . '/' . Str::random(40) . '.' . $newExt;
            Storage::disk('public')->put($path, $encoded);

            return $path;
        } catch (Throwable $e) {
            report($e);

            return $file->store($storagePath, 'public');
        }
    }

    /**
     * Delete all size/format variants for a canonical path.
     *
     * Canonical path format: {folder}/{uuid}_large.avif
     */
    public function deleteAllVariants(string $canonicalPath): void
    {
        // Strip the size+extension suffix to get the base path.
        $basePath = preg_replace('/_(thumb|medium|large)\.(avif|webp)$/', '', $canonicalPath);

        foreach (array_keys(self::SIZES) as $sizeName) {
            foreach (['avif', 'webp'] as $ext) {
                $path = "{$basePath}_{$sizeName}.{$ext}";
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }
    }
}
