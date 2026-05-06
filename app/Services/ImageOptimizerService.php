<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageOptimizerService
{
    private const QUALITY_AVIF = 65;
    private const QUALITY_WEBP = 70;
    private const MAX_WIDTH    = 1920;
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
