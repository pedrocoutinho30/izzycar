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
        'review_date',
    ];

    protected $casts = [
        'rating'      => 'float',
        'published'   => 'boolean',
        'review_date' => 'date',
    ];
}
