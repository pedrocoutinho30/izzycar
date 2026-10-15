<?php
// app/Models/Partner.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'postal_code',
        'city',
        'country',
        'email',
        'vat',
        'contact_name',
        'image',
        'url',
        'show_on_site',
    ];

    /** Miniatura WebP (se existir no disco), senão o original — caminho relativo ao disco public. */
    public function getImageThumbAttribute(): ?string
    {
        return \App\Services\ThumbnailService::existing($this->image, \App\Services\ThumbnailService::PARTNER_WIDTH)
            ?? $this->image;
    }

    protected $casts = [
        'show_on_site' => 'boolean',
    ];
}
