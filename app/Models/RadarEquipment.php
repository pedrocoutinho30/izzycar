<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadarEquipment extends Model
{
    protected $table = 'radar_equipment';

    protected $fillable = ['label', 'slug', 'show_in_filters'];

    protected $casts = [
        'show_in_filters' => 'boolean',
    ];

    public function aliases()
    {
        return $this->hasMany(RadarEquipmentAlias::class);
    }

    public function listings()
    {
        return $this->belongsToMany(RadarListing::class, 'radar_listing_equipment');
    }

    public function searches()
    {
        return $this->belongsToMany(RadarSearch::class, 'radar_search_equipment');
    }
}
