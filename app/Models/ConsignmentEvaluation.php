<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsignmentEvaluation extends Model
{
    protected $fillable = [
        'reference', 'brand', 'model', 'version', 'year', 'kilometers',
        'plate', 'fuel', 'gearbox', 'power', 'displacement', 'color',
        'condition', 'description',
        'has_service_book', 'has_2nd_key', 'has_iuc', 'has_inspection',
        'photos',
        'name', 'phone', 'email', 'location', 'price_expectation',
        'status', 'notes',
    ];

    protected $casts = [
        'photos'           => 'array',
        'has_service_book' => 'boolean',
        'has_2nd_key'      => 'boolean',
        'has_iuc'          => 'boolean',
        'has_inspection'   => 'boolean',
    ];

    public static $statusLabels = [
        'novo'            => ['label' => 'Novo',             'color' => 'primary'],
        'contactado'      => ['label' => 'Contactado',       'color' => 'info'],
        'avaliado'        => ['label' => 'Avaliado',         'color' => 'warning'],
        'em_consignacao'  => ['label' => 'Em Consignação',   'color' => 'success'],
        'vendido'         => ['label' => 'Vendido',          'color' => 'dark'],
        'cancelado'       => ['label' => 'Cancelado',        'color' => 'danger'],
    ];
}
