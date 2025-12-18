<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeGroup extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'order'];

    public function attributes()
    {
        return $this->hasMany(VehicleAttribute::class, 'attribute_group', 'name');
    }
}
