<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterOffer extends Model
{
    protected $fillable = [
        'newsletter_id',
        'image',
        'brand',
        'model',
        'year',
        'kms',
        'price',
        'savings',
        'equipamentos',
        'combustivel',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
