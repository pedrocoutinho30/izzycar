<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvertedProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'url',
        'client_id',
        'proposal_id',
        'brand',
        'modelCar',
        'version',
        'year',
        'km',
        'matricula_origem',
        'matricula_destino',
        'custo_inspecao_origem',
        'inspecao_origem_pago',
        'custo_transporte',
        'transporte_pago',
        'custo_ipo',
        'ipo_pago',
        'isv',
        'isv_pago',
        'custo_imt',
        'imt_pago',
        'custo_matricula',
        'matricula_pago_impressa',
        'custo_registo_automovel',
        'registo_pago',
        'valor_primeira_tranche',
        'valor_segunda_tranche',
        'primeira_tranche_pago',
        'segunda_tranche_pago',
        'valor_carro',
        'carro_pago',
        'valor_comissao',
        'valor_comissao_final',
        'contactos_stand',
        'observacoes',
    ];

    // Relacionamentos
    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function proposal() {
        return $this->belongsTo(Proposal::class);
    }

    public function statusHistories()
{
    return $this->hasMany(StatusProposalHistory::class, 'converted_proposal_id');
}

    protected static function booted()
    {
        static::updated(function ($proposal) {
            if ($proposal->wasChanged('status')) {
                \App\Models\StatusProposalHistory::create([
                    'converted_proposal_id' => $proposal->id,
                    'old_status' => $proposal->getOriginal('status'),
                    'new_status' => $proposal->status,
                ]);
            }
        });
    }
}
