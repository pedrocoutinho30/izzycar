<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageTypeField extends Model
{
    protected $fillable = ['page_type_id', 'name', 'label', 'type', 'order', 'options',  'required'];

    public function pageType()
    {
        return $this->belongsTo(PageType::class);
    }
}
