<?php

namespace App\Console\Commands;

use App\Models\Partner;
use App\Models\Setting;
use App\Services\ThumbnailService;
use Illuminate\Console\Command;

class GenerateImageThumbnails extends Command
{
    protected $signature = 'images:thumbnails {--dry-run : Só lista o que seria gerado}';

    protected $description = 'Gera miniaturas WebP do logo do site e dos logos dos parceiros (não altera originais nem a BD)';

    public function handle(ThumbnailService $thumbs): int
    {
        $jobs = [];

        if ($logo = Setting::where('label', 'logo')->value('value')) {
            $jobs[] = [$logo, 'logo'];
        }

        foreach (Partner::whereNotNull('image')->pluck('image') as $image) {
            $jobs[] = [$image, 'partner'];
        }

        foreach ($jobs as [$path, $kind]) {
            if ($this->option('dry-run')) {
                $this->line("[dry-run] {$kind}: {$path}");
                continue;
            }

            $out = $kind === 'logo'
                ? $thumbs->generateForLogo($path)
                : $thumbs->generate($path, ThumbnailService::PARTNER_WIDTH);
            $this->line($out ? "OK  {$out}" : "--  {$path} (sem alterações)");
        }

        return self::SUCCESS;
    }
}
