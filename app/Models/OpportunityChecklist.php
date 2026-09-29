<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tipo de checklist das Oportunidades ("base", "electric", ...). Com
 * applies_to_fuels vazio aplica-se a todas as oportunidades.
 */
class OpportunityChecklist extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'applies_to_fuels',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'applies_to_fuels' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OpportunityChecklistItem::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function appliesToFuel(?string $fuel): bool
    {
        if (empty($this->applies_to_fuels)) {
            return true;
        }

        return in_array($fuel, $this->applies_to_fuels, true);
    }
}
