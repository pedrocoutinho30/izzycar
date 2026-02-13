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
        'data_processing_consent',
        'newsletter_consent',
    ];

    // Definindo os tipos de dados para a tabela
    protected $casts = [
        'birth_date' => 'date',
        'validate_identification_number' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function sale()
    {
        return $this->hasMany(Sale::class, 'client_id');
    }

    public function costSimulators()
    {
        return $this->hasMany(CostSimulator::class, 'client_id');
    }
    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'client_id');
    }
}
