<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalizationDocument extends Model
{
    protected $fillable = [
        'legalization_id',
        'tipo',
        'nome_original',
        'caminho',
    ];

    public function legalization(): BelongsTo
    {
        return $this->belongsTo(Legalization::class);
    }
}
