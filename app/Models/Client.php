<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Definindo os campos que podem ser preenchidos (Mass Assignment)
    protected $fillable = [
        'owner_id',
        'angariador_code',
        'name',
        'vat_number',
        'birth_date',
        'gender',
        'phone',
        'email',
        'language',
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

    protected static function booted(): void
    {
        // Chaves de procura de duplicados (ver ClientMatcher): email sem
        // maiúsculas/espaços e telefone pelos últimos 9 dígitos.
        static::saving(function (Client $client) {
            $matcher = app(\App\Services\ClientMatcher::class);
            $client->email_normalized = $matcher->normalizeEmail($client->email);
            $client->phone_key = $matcher->phoneKey($client->phone);
        });
    }

    /**
     * Filtros da listagem de clientes (search, client_type). Partilhado pela
     * listagem e pela exportação XLSX para darem sempre os mesmos resultados.
     * O tipo compara-se sem distinguir maiúsculas (a BD gravou "empresa" e
     * "Empresa"; no MySQL o "=" já ignorava, o sqlite não).
     *
     * @param  array{search?: mixed, client_type?: mixed}  $filtros
     */
    public function scopeFiltrarClientes(Builder $query, array $filtros): Builder
    {
        if (filled($filtros['search'] ?? null)) {
            $search = $filtros['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%");
            });
        }

        if (filled($filtros['client_type'] ?? null)) {
            $query->whereRaw('LOWER(client_type) = ?', [mb_strtolower($filtros['client_type'])]);
        }

        return $query;
    }

    /**
     * Filtros da listagem de leads (search, lead_source, lead_status, owner_id).
     * Partilhado pela listagem e pela exportação XLSX.
     *
     * @param  array{search?: mixed, lead_source?: mixed, lead_status?: mixed, owner_id?: mixed}  $filtros
     */
    public function scopeFiltrarLeads(Builder $query, array $filtros): Builder
    {
        if (filled($filtros['search'] ?? null)) {
            $search = $filtros['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        foreach (['lead_source', 'lead_status', 'owner_id'] as $campo) {
            if (filled($filtros[$campo] ?? null)) {
                $query->where($campo, $filtros[$campo]);
            }
        }

        return $query;
    }

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

    /** Viaturas do cliente sem venda (importações). */
    public function vehicles()
    {
        return $this->hasMany(V3Vehicle::class, 'client_id');
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

    public function carCandidates()
    {
        return $this->hasMany(CarCandidate::class, 'client_id')->orderBy('order_position');
    }

    public function convertedProposals()
    {
        return $this->hasMany(ConvertedProposal::class, 'client_id');
    }

    public function formProposals()
    {
        return $this->hasMany(FormProposal::class, 'client_id');
    }

    /**
     * Proprietário da lead (angariador responsável).
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
