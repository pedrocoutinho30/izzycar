<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class V3VehicleDocument extends Model
{
    protected $table = 'v3_vehicle_documents';

    protected $fillable = [
        'v3_vehicle_id',
        'tipo',      // predefined key (from Legalization::DOCUMENTOS) or null
        'titulo',    // custom label for non-predefined documents
        'nome_original',
        'caminho',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(V3Vehicle::class, 'v3_vehicle_id');
    }

    public function getLabel(): string
    {
        if ($this->tipo && isset(Legalization::DOCUMENTOS[$this->tipo])) {
            return Legalization::DOCUMENTOS[$this->tipo];
        }
        return $this->titulo ?? 'Documento';
    }
}
