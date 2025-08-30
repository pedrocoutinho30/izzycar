<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormProposal extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'source',
        'message',
        'ad_option',
        'ad_links',
        'brand',
        'model',
        'fuel',
        'year_min',
        'km_max',
        'color',
        'budget',
        'gearbox',
        'extras',
    ];
}
