<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarCandidate extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'link',
        'price',
        'description',
        'status',
        'notes',
        'order_position',
    ];

    public const STATUS_OPTIONS = [
        'primeiro_contacto' => ['label' => 'Enviei primeiro contacto', 'color' => 'info'],
        'em_conversacao'    => ['label' => 'Em conversação', 'color' => 'primary'],
        'sem_resposta'      => ['label' => 'Não responde/atende', 'color' => 'warning'],
        'descartado'        => ['label' => 'Descartado', 'color' => 'secondary'],
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
