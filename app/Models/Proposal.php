<?php
// App/Models/Proposal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSeo;


class Proposal extends Model
{
    use HasFactory;
    use HasSeo;

    protected $fillable = [
        'client_id',
        'brand',
        'model',
        'version',
        'year',
        'mileage',
        'engine_capacity',
        'co2',
        'notes',
        'transport_cost',
        'ipo_cost',
        'imt_cost',
        'registration_cost',
        'isv_cost',
        'license_plate_cost',
        'inspection_commission_cost',
        'commission_cost',
        'proposed_car_mileage',
        'proposed_car_year_month',
        'proposed_car_value',
        'proposed_car_notes',
        'images',
        'fuel',
        'value',
        'status',
        'url',
        'proposed_car_features',
        'other_links',
        'proposal_code'
    ];

    protected $casts = [
        'images' => 'array', // To store images as an array
       
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function attributeValues()
    {
        return $this->hasMany(ProposalAttributeValue::class);
    }
    public function seo()
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}
