<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = ['name', 'logo_path'];

    /**
     * URL público do logo, ou null se a marca não tiver. Usa asset() (como no
     * resto da app) e não Storage::url(): este parte do APP_URL e o logo
     * deixa de carregar quando o BO é aberto por outro host.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? asset('storage/'.$this->logo_path) : null;
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
