<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Definindo os campos que podem ser preenchidos (Mass Assignment)
    protected $fillable = [
        'name',
        'vat_number',
        'birth_date',
        'gender',
        'phone',
        'email',
        'identification_number',
        'validate_identification_number',
        'address',
        'postal_code',
        'city',
        'client_type',
        'origin',
        'observation',
    ];

    // Definindo os tipos de dados para a tabela
    protected $casts = [
        'birth_date' => 'date',
        'validate_identification_number' => 'date',
    ];
}
