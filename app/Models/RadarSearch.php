<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadarSearch extends Model
{
    protected $fillable = ['name', 'make', 'model', 'filters', 'base_url', 'standvirtual_base_url', 'carmine_base_url', 'import_cost_eur', 'is_active', 'new_listings_seen_at'];

    protected $casts = [
        'filters' => 'array',
        'import_cost_eur' => 'decimal:2',
        'is_active' => 'boolean',
        'new_listings_seen_at' => 'datetime',
    ];

    public function listings()
    {
        return $this->hasMany(RadarListing::class);
    }

    public function runs()
    {
        return $this->hasMany(RadarSearchRun::class);
    }

    public function latestRun()
    {
        return $this->hasOne(RadarSearchRun::class)->latestOfMany('started_at');
    }

    public function latestRunFor(string $source)
    {
        return $this->runs()->where('source', $source)->orderByDesc('started_at')->first();
    }

    /** Equipamento exigido por esta pesquisa (filtro "E" - ver radar_search_equipment). */
    public function equipment()
    {
        return $this->belongsToMany(RadarEquipment::class, 'radar_search_equipment');
    }
}
