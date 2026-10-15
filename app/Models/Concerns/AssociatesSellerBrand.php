<?php

namespace App\Models\Concerns;

use App\Services\SellerService;

/**
 * Para modelos com `seller_id` e `brand` (texto): ao ficarem associados a um
 * vendedor — ou ao mudar o vendedor ou a marca — a marca é adicionada às
 * marcas do vendedor. Nunca remove nada; sem seller_id/marca, ou sem alteração
 * a esses campos, não faz nada (nem consulta a BD).
 */
trait AssociatesSellerBrand
{
    public static function bootAssociatesSellerBrand(): void
    {
        static::saved(function ($model) {
            if ($model->seller_id && filled($model->brand)
                && ($model->wasRecentlyCreated || $model->wasChanged(['seller_id', 'brand']))) {
                app(SellerService::class)->attachBrandByName($model->seller_id, $model->brand);
            }
        });
    }
}
