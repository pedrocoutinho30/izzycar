<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerDomain extends Model
{
    protected $fillable = [
        'seller_id',
        'domain',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
