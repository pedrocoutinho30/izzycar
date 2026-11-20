<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingPostSlide extends Model
{
    protected $fillable = ['post_id', 'position', 'layout', 'content'];

    protected $casts = ['content' => 'array', 'position' => 'integer'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(MarketingPost::class, 'post_id');
    }
}
