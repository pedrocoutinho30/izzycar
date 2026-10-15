<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    protected $fillable = ['name', 'logo_path'];

    /** URL público do logo, ou null se a marca não tiver. */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    /** Vendedores que trabalham esta marca. */
    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(Seller::class, 'brand_seller')->withTimestamps();
    }

    public function models(): HasMany
    {
        return $this->hasMany(ModelCar::class);
    }

    public function modelCars(): HasMany
    {
        return $this->hasMany(ModelCar::class);
    }
}
