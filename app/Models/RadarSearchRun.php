<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadarSearchRun extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['radar_search_id', 'source', 'started_at', 'finished_at', 'status', 'listings_found', 'pages_scraped', 'error_message'];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function search()
    {
        return $this->belongsTo(RadarSearch::class, 'radar_search_id');
    }
}
