<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadarEquipmentAlias extends Model
{
    protected $table = 'radar_equipment_aliases';

    protected $fillable = ['radar_equipment_id', 'source', 'raw_key', 'raw_label'];

    public function equipment()
    {
        return $this->belongsTo(RadarEquipment::class, 'radar_equipment_id');
    }
}
