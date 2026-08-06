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
        'owner_id',
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
        'comissao_paga',
        'comissao_paga_em',
        'comprovativo_pagamento',
        'contactos_stand',
        'observacoes',
    ];

    protected $casts = [
        'comissao_paga' => 'boolean',
        'comissao_paga_em' => 'date',
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

    /**
     * Angariador desta cotação convertida — independente do owner_id do
     * Client, para que uma segunda cotação/venda ao mesmo cliente possa não
     * estar associada a nenhum angariador (ou a um diferente).
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
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

    /**
     * Momento em que esta cotação convertida entrou no estado "Entrega",
     * lido do histórico de estados (não há campo manual separado para isto).
     */
    public function deliveredAt(): ?\Illuminate\Support\Carbon
    {
        return $this->statusHistories()
            ->where('new_status', 'Entrega')
            ->latest('created_at')
            ->first()?->created_at;
    }

    /**
     * Comissão do angariador desta cotação convertida — valor fixo definido
     * no perfil do angariador (users.commission_fixed_value). Null se não
     * tiver angariador associado ou se este não tiver comissão definida.
     */
    public function angariadorCommissionAmount(): ?float
    {
        if (!$this->owner_id || $this->owner?->commission_fixed_value === null) {
            return null;
        }

        return round((float) $this->owner->commission_fixed_value, 2);
    }

    /**
     * A comissão está em atraso quando a cotação já está (ou já esteve) no
     * estado "Entrega" há mais de 48h e a comissão ainda não foi paga.
     */
    public function isCommissionOverdue(): bool
    {
        if ($this->comissao_paga) {
            return false;
        }

        $deliveredAt = $this->deliveredAt();

        if (!$deliveredAt) {
            return false;
        }

        return $deliveredAt->copy()->addHours(48)->isPast();
    }

    /**
     * Soma os totais [recebido, pendente] de uma coleção de cotações convertidas.
     */
    public static function commissionTotals($convertedProposals): array
    {
        $recebido = 0.0;
        $pendente = 0.0;

        foreach ($convertedProposals as $convertedProposal) {
            $amount = $convertedProposal->angariadorCommissionAmount();
            if ($amount === null) {
                continue;
            }
            if ($convertedProposal->comissao_paga) {
                $recebido += $amount;
            } else {
                $pendente += $amount;
            }
        }

        return [round($recebido, 2), round($pendente, 2)];
    }
}
