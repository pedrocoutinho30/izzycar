<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDocument extends Model
{
    protected $fillable = [
        'vehicle_id',
        'tipo',
        'nome_original',
        'caminho',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * The document types shared with the legalization module.
     */
    public static function tipos(): array
    {
        return Legalization::DOCUMENTOS;
    }
}
