<?php

namespace App\Models;

use App\Enums\ChecklistItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportOpportunityChecklistEntry extends Model
{
    protected $fillable = [
        'import_opportunity_id',
        'opportunity_checklist_item_id',
        'status',
        'updated_by',
    ];

    protected $casts = [
        'status' => ChecklistItemStatus::class,
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(ImportOpportunity::class, 'import_opportunity_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(OpportunityChecklistItem::class, 'opportunity_checklist_item_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
