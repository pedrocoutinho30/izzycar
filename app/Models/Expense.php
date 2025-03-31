<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'vehicle_id',
        'title',
        'amount',
        'vat_rate',
        'expense_date',
        'partner_id',
        'observations',
    ];

    // Relacionamentos (Se necessário)
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
