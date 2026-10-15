<?php

namespace App\Console\Commands;

use App\Models\Brand;
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
            $bySlug[$this->normalize($item['name'])] = $item;
            $bySlug[$this->normalize($item['slug'])] = $item;
        }

        $dry = $this->option('dry-run');
        $done = 0;
        $skipped = 0;
        $missing = [];

        foreach (Brand::orderBy('name')->get() as $brand) {
            $key = $this->normalize($brand->name);
            $item = $bySlug[self::ALIASES[$key] ?? $key] ?? null;

            if ($item === null) {
                $missing[] = $brand->name;

                continue;
            }

            if ($brand->logo_path && ! $this->option('force')) {
                $skipped++;

                continue;
            }

            $path = 'brands/'.Str::slug($item['slug']).'.png';

            if ($dry) {
                $this->line("{$brand->name} -> {$item['slug']}");
                $done++;

                continue;
            }

            $response = Http::timeout(20)->get($item['image']['thumb']);
            if (! $response->successful() || ! str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                $this->warn("Falhou o download de {$brand->name} ({$response->status()})");
                $missing[] = $brand->name;

                continue;
            }

            Storage::disk('public')->put($path, $response->body());
            $brand->update(['logo_path' => $path]);
            $done++;
        }

        $this->info("Logos ".($dry ? 'a atribuir' : 'atribuídos').": $done | já tinham: $skipped | sem logo: ".count($missing));
        if ($missing) {
            $this->line('Sem logo: '.implode(', ', $missing));
        }

        return self::SUCCESS;
    }

    private function normalize(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii($value)));
    }
}
