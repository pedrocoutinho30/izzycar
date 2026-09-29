<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityChecklistItem extends Model
{
    protected $fillable = [
        'opportunity_checklist_id',
        'slug',
        'label',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(OpportunityChecklist::class, 'opportunity_checklist_id');
    }
}
