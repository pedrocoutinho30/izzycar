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
        'payment_type',
        'estimated_purchase_date',
        'data_processing_consent',
        'newsletter_consent',
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
        'retoma_option',
        'retoma_brand',
        'retoma_model',
        'retoma_year',
        'retoma_km',
        'retoma_fuel',
        'retoma_info',
        'retoma_photos',
        'client_id',
        'angariador_code',
        'status',
        'version',
        'proposal_id'
    ];

    protected $casts = [
        'retoma_photos' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
