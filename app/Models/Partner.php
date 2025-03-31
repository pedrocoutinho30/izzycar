<?php
// app/Models/Partner.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'postal_code',
        'city',
        'country',
        'email',
        'vat',
        'contact_name',
    ];
}
