<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class CmsPage extends Model
{
    protected $fillable = ['name', 'slug', 'active', 'order'];

    protected $casts = ['active' => 'boolean'];

    public function blocks()
    {
        return $this->hasMany(CmsBlock::class)->orderBy('order');
    }

    public function activeBlocks()
    {
        return $this->hasMany(CmsBlock::class)->where('active', true)->orderBy('order');
    }

    // Shortcut: load active blocks indexed by name for easy lookup in views
    public function blocksByName(): Collection
    {
        return $this->activeBlocks()->get()->keyBy('name');
    }
}
