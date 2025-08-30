<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $fillable = ['page_id', 'field_name', 'field_value'];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
