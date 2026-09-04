<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadarListing extends Model
{
    protected $fillable = [
        'external_id', 'source', 'radar_search_id', 'make', 'model', 'version',
        'first_registration_year', 'mileage_km', 'power_hp', 'fuel', 'gearbox',
        'body_type', 'seller_type', 'seller_name', 'seller_phone', 'location_zip', 'location_city',
        'price_eur', 'include_in_average', 'duplicate_of_listing_id', 'url',
        'first_seen_at', 'last_seen_at', 'removed_at',
    ];

    protected $casts = [
        'include_in_average' => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    public function search()
    {
        return $this->belongsTo(RadarSearch::class, 'radar_search_id');
    }

    public function priceHistory()
    {
        return $this->hasMany(RadarPriceHistory::class)->orderBy('scraped_at');
    }

    public function duplicateOf()
    {
        return $this->belongsTo(self::class, 'duplicate_of_listing_id');
    }

    /** Equipamento deste anúncio - só preenchido para anúncios novos (ver equipment_client.py no scraper Python). */
    public function equipment()
    {
        return $this->belongsToMany(RadarEquipment::class, 'radar_listing_equipment');
    }
}
