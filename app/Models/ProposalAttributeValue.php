<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProposalAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'attribute_id',
        'value',
    ];

    /**
     * A que proposta este valor pertence.
     */
    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    /**
     * Qual é o atributo (ex: cor, nº portas, etc).
     */
    public function attribute()
    {
        return $this->belongsTo(VehicleAttribute::class, 'attribute_id');
    }
}
