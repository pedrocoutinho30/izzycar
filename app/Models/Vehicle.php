<?php

// app/Models/Vehicle.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;



    protected $fillable = [
        'reference',
        'brand',
        'model',
        'version',
        'year',
        'purchase_price',
        'sell_price',
        'supplier_id',
        'color',
        'purchase_type',
        'purchase_date',
        'business_type',
        'kilometers',
        'power',
        'cylinder_capacity',
        'consigned_vehicle',
        'fuel',
        'vin',
        'manufacture_date',
        'register_date',
        'available_to_sell_date',
        'registration',
        'show_online',
        'warranty_price',
        'warranty_start_date',
        'warranty_end_date',
        'warranty_type',
        'payment_type',
    ];



    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function images()
    {
        return $this->hasMany(VehicleImage::class);
    }
    public function attributeValues()
    {
        return $this->hasMany(VehicleAttributeValue::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'vehicle_id');
    }

    public function sale()
    {
        return $this->hasOne(Sale::class, 'vehicle_id');
    }
}
