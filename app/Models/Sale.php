<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'client_id',
        'sale_date',
        'sale_price',
        'vat_rate',
        'payment_method',
        'observation',
        'gross_margin',
        'net_margin',
        'vat_paid',
        'vat_deducible_purchase',
        'vat_settle_sale',
        'totalCost',
        'totalExpenses',
        'net_profitability',
        'gross_profitability',
        'has_trade_in',
        'trade_in_vehicle_id',
        'trade_in_value',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
