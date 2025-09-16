<?php

namespace App\Traits;

use App\Models\SeoMeta;


trait HasSeo
{
    public function seo()
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    /**
     * Atualiza ou cria SEO a partir do array fornecido.
     */
    public function updateSeo(array $data)
    {
        $this->seo()->updateOrCreate([], $data);
    }
}
