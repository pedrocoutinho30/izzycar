<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadarPriceHistory extends Model
{
    protected $table = 'radar_price_history';

    public $timestamps = false;

    protected $fillable = ['radar_listing_id', 'price_eur', 'scraped_at'];

    protected $casts = [
        'scraped_at' => 'datetime',
    ];

    public function listing()
    {
        return $this->belongsTo(RadarListing::class);
    }
}
