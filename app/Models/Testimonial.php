<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    const ORIGINS = [
        'google'    => 'Google',
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'email'     => 'Email',
        'outro'     => 'Outro',
    ];

    protected $fillable = [
        'name',
        'rating',
        'comment',
        'origin',
        'published',
        'marketing_consent',
        'review_date',
        'google_review_id',
    ];

    protected $casts = [
        'rating'      => 'float',
        'published'   => 'boolean',
        'marketing_consent' => 'boolean',
        'review_date' => 'date',
    ];
}
