<?php

namespace App\Support;

use App\Models\Brand;
use Illuminate\Support\Str;

/**
 * Resolve o logo de uma marca a partir do nome (as cotações, veículos e
 * oportunidades guardam a marca como texto). Carrega o mapa nome → URL numa só
 * query e guarda-o durante o pedido (registado como "scoped"), sem N+1.
 */
class BrandLogos
{
    /** @var array<string, string>|null */
    private ?array $map = null;

    public function urlFor(?string $brand): ?string
    {
        $key = self::normalize($brand);

        return $key === '' ? null : ($this->map()[$key] ?? null);
    }

    /** Esquece o mapa (útil quando um logo acabou de mudar no mesmo pedido). */
    public function flush(): void
    {
        $this->map = null;
    }

    /** @return array<string, string> */
    private function map(): array
    {
        if ($this->map === null) {
            $this->map = [];

            foreach (Brand::query()->whereNotNull('logo_path')->where('logo_path', '!=', '')->get(['id', 'name', 'logo_path']) as $brand) {
                if ($url = $brand->logo_url) {
                    $this->map[self::normalize($brand->name)] = $url;
                }
            }
        }

        return $this->map;
    }

    /**
     * Chave única para comparar nomes de marca: sem acentos, minúsculas e só
     * letras/dígitos ("Citroën" = "citroen", "Mercedes-Benz" = "mercedesbenz").
     */
    public static function normalize(?string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii((string) $value)));
    }
}
