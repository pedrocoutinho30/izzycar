<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreLead extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'message',
        'status',
    ];
}
