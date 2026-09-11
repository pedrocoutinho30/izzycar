<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConvertedProposalDocument extends Model
{
    protected $fillable = [
        'converted_proposal_id',
        'tipo',
        'nome_original',
        'caminho',
        'enviado_em',
    ];

    protected $casts = [
        'enviado_em' => 'datetime',
    ];

    public function convertedProposal(): BelongsTo
    {
        return $this->belongsTo(ConvertedProposal::class);
    }
}
