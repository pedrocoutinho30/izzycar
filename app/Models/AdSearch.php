<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSearch extends Model
{
    protected $fillable = ['brand', 'model', 'submodel', 'year_start', 'year_end', 'fuel', 'description'];

    public function listings()
    {
        return $this->hasMany(AdListing::class)->orderBy('price', 'desc');
    }
}
