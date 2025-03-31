<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'company_name',
        'contact_name',
        'address',
        'postal_code',
        'city',
        'email',
        'country',
        'phone',
        'vat',
        'identification_number',
        'identification_number_validity',
    ];
}
