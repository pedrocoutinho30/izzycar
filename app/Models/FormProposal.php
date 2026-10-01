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
        'proposal_id',
        'origin',
        'title',
        'created_by',
    ];

    public const ORIGIN_SITE = 'site';
    public const ORIGIN_MANUAL = 'manual';

    protected $casts = [
        'retoma_photos' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function opportunities()
    {
        return $this->hasMany(ImportOpportunity::class)->latest();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isManual(): bool
    {
        return $this->origin === self::ORIGIN_MANUAL;
    }

    /** Nome do pedido para listas e títulos: título, ou o carro pretendido. */
    public function getLabelAttribute(): string
    {
        if (filled($this->title)) {
            return $this->title;
        }

        $car = trim(implode(' ', array_filter([$this->brand, $this->model])));

        return $car !== '' ? $car : 'Pedido de importação';
    }
}
