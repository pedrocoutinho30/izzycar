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
        'client_id',
        'angariador_code',
        'status',
        'version',
        'proposal_id'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
