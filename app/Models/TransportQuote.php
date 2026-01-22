<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportQuote extends Model
{
    protected $fillable = [
        'brand',
        'model',
        'origin_city',
        'origin_country',
        'origin_postal_code',
        'origin_latitude',
        'origin_longitude',
        'destination_city',
        'destination_country',
        'destination_latitude',
        'destination_longitude',
        'supplier_id',
        'price',
        'quote_date',
        'estimated_delivery_days',
        'observations',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'price' => 'decimal:2',
        'origin_latitude' => 'decimal:7',
        'origin_longitude' => 'decimal:7',
        'destination_latitude' => 'decimal:7',
        'destination_longitude' => 'decimal:7',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
