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
        'is_lead',
        'converted_at',
        'lead_source',
        'lead_status',
        'next_followup_at',
        'followup_note',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'validate_identification_number' => 'date',
        'is_lead' => 'boolean',
        'converted_at' => 'datetime',
        'next_followup_at' => 'datetime',
    ];

    public function convertToClient(): void
    {
        $this->update([
            'is_lead' => false,
            'converted_at' => now(),
        ]);
    }

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

    public function activities()
    {
        return $this->hasMany(LeadActivity::class, 'client_id')->orderBy('created_at', 'desc');
    }

    public function latestActivity()
    {
        return $this->hasOne(LeadActivity::class, 'client_id')->latestOfMany();
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'client_id');
    }
}
