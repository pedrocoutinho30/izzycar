<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Support\BrandLogos;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportBrandLogos extends Command
{
    protected $signature = 'brands:import-logos {file : Caminho do data.json (car-logos-dataset)}
        {--dry-run : Só mostra o que seria feito, sem descarregar nem gravar}
        {--force : Volta a descarregar marcas que já têm logo}';

    protected $description = 'Descarrega os logos das marcas (versão thumb) para o disco public e preenche brands.logo_path';

    /** Marcas do catálogo cujo nome difere do dataset. */
    private const ALIASES = [
        'vw' => 'volkswagen',
    ];

    /** Tamanho máximo do logo descarregado. */
    private const MAX_BYTES = 2 * 1024 * 1024;

    public function handle(): int
    {
        $file = $this->argument('file');
        if (! is_file($file)) {
            $this->error("Ficheiro não encontrado: $file");

            return self::FAILURE;
        }

        $dataset = json_decode(file_get_contents($file), true);
        if (! is_array($dataset)) {
            $this->error('JSON inválido.');

            return self::FAILURE;
        }

        $bySlug = [];
        foreach ($dataset as $item) {
            if (! is_array($item)) {
                continue;
            }
            foreach (['name', 'slug'] as $field) {
                if (is_string($item[$field] ?? null) && $item[$field] !== '') {
                    $bySlug[BrandLogos::normalize($item[$field])] = $item;
                }
            }
        }

        $dry = $this->option('dry-run');
        $done = 0;
        $skipped = 0;
        $missing = [];

        foreach (Brand::orderBy('name')->get() as $brand) {
            $key = BrandLogos::normalize($brand->name);
            $item = $bySlug[self::ALIASES[$key] ?? $key] ?? null;

            if ($item === null) {
                $missing[] = $brand->name;

                continue;
            }

            if ($brand->logo_path && ! $this->option('force')) {
                $skipped++;

                continue;
            }

            $slug = $item['slug'] ?? null;
            $thumb = $item['image']['thumb'] ?? null;
            if (! is_string($slug) || $slug === '' || ! is_string($thumb) || $thumb === '') {
                $missing[] = $brand->name;

                continue;
            }

            $path = 'brands/'.Str::slug($slug).'.png';

            if ($dry) {
                $this->line("{$brand->name} -> {$slug}");
                $done++;

                continue;
            }

            try {
                if (! str_starts_with(strtolower($thumb), 'https://')) {
                    throw new \RuntimeException('URL não é https');
                }

                $response = Http::timeout(20)->get($thumb);
                if (! $response->successful() || ! str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                    throw new \RuntimeException('resposta inválida ('.$response->status().')');
                }
                if (strlen($response->body()) >= self::MAX_BYTES) {
                    throw new \RuntimeException('imagem com 2 MB ou mais');
                }

                Storage::disk('public')->put($path, $response->body());
            } catch (\Throwable $e) {
                $this->warn("Falhou o download de {$brand->name}: {$e->getMessage()}");
                $missing[] = $brand->name;

                continue;
            }

            $old = $brand->logo_path;
            $brand->update(['logo_path' => $path]);
            $done++;

            // Com --force, não deixar órfão o ficheiro anterior (ex.: upload manual) se mais ninguém o usa.
            if ($old && $old !== $path && ! Brand::where('logo_path', $old)->where('id', '!=', $brand->id)->exists()) {
                Storage::disk('public')->delete($old);
            }
        }

        $this->info("Logos ".($dry ? 'a atribuir' : 'atribuídos').": $done | já tinham: $skipped | sem logo: ".count($missing));
        if ($missing) {
            $this->line('Sem logo: '.implode(', ', $missing));
        }

        return self::SUCCESS;
    }
}
