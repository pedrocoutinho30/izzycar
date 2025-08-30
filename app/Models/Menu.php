<?php

// app/Models/Menu.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['title', 'url', 'parent_id', 'order', 'route_name'];

    // Relação com submenus
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    // Relação com menu pai
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

     public function scopeMain($query)
    {
        return $query->whereNull('parent_id')->orderBy('order');
    }
}
