<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusProposalHistory extends Model
{

    protected $table = 'status_proposal_history';
    protected $fillable = [
        'converted_proposal_id',
        'old_status',
        'new_status',
    ];

     public function proposal()
    {
        return $this->belongsTo(ConvertedProposal::class, 'converted_proposal_id');
    }
}
