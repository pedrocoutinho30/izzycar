<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{

    protected $fillable = [
        'title',
        'meta_description',
        'meta_image',
        'meta_keywords',
        'meta_secundary_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'og_url',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
    ];

    public function seoable()
    {
        return $this->morphTo();
    }
}
