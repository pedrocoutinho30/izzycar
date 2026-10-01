<?php

namespace App\Support;

use App\Models\Brand;
use Closure;
use Illuminate\Support\Str;

/**
 * Catálogo de marcas/modelos (tabelas brands e model_cars) usado pelos
 * selects de marca → modelo e pela validação: marca e modelo vêm sempre do
 * catálogo, nunca de texto livre.
 */
class VehicleCatalog
{
    /** @return array<string, list<string>> marca => modelos, por ordem alfabética */
    public static function map(): array
    {
        return once(fn () => Brand::with(['models' => fn ($q) => $q->select('id', 'brand_id', 'name')->orderBy('name')])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn (Brand $brand) => [$brand->name => $brand->models->pluck('name')->unique()->values()->all()])
            ->all());
    }

    /** Nome da marca tal como está no catálogo (comparação sem maiúsculas). */
    public static function brand(?string $name): ?string
    {
        if (blank($name)) {
            return null;
        }

        return collect(array_keys(self::map()))->first(fn ($brand) => Str::lower($brand) === Str::lower(trim($name)));
    }

    public static function model(?string $brand, ?string $model): ?string
    {
        $brand = self::brand($brand);
        if ($brand === null || blank($model)) {
            return null;
        }

        return collect(self::map()[$brand])->first(fn ($m) => Str::lower($m) === Str::lower(trim($model)));
    }

    /**
     * Regras de validação para um par marca/modelo. Um valor já guardado que
     * não exista no catálogo (registos antigos) é aceite se não mudar.
     *
     * @return array{0: Closure, 1: Closure} [regra da marca, regra do modelo]
     */
    public static function rules(?string $brandInput, ?string $currentBrand = null, ?string $currentModel = null): array
    {
        $brandRule = function (string $attribute, mixed $value, Closure $fail) use ($currentBrand) {
            if (filled($value) && $value !== $currentBrand && self::brand($value) === null) {
                $fail('Escolha uma marca da lista.');
            }
        };

        $modelRule = function (string $attribute, mixed $value, Closure $fail) use ($brandInput, $currentBrand, $currentModel) {
            if (blank($value) || ($value === $currentModel && $brandInput === $currentBrand)) {
                return;
            }
            if (self::model($brandInput, $value) === null) {
                $fail('Escolha um modelo da lista para a marca escolhida.');
            }
        };

        return [$brandRule, $modelRule];
    }
}
