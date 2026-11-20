<?php

namespace App\Models;

use App\Enums\MarketingFormat;
use App\Enums\MarketingPostStatus;
use App\Enums\MarketingPostType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Post do Marketing Studio. `data` é o instantâneo dos dados usados na
 * criação (não muda quando a viatura muda); `config` é a configuração visual.
 */
class MarketingPost extends Model
{
    /** Redes do planeamento; 'ambos' aparece nos filtros de qualquer rede. */
    public const CHANNELS = ['instagram', 'facebook', 'ambos'];

    protected $fillable = [
        'type', 'template', 'format', 'status', 'title', 'caption', 'hashtags', 'cta', 'tone',
        'source_type', 'source_id', 'data', 'config', 'scheduled_for', 'published_at', 'channel',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'type' => MarketingPostType::class,
        'format' => MarketingFormat::class,
        'status' => MarketingPostStatus::class,
        'data' => 'array',
        'config' => 'array',
        'scheduled_for' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function slides(): HasMany
    {
        return $this->hasMany(MarketingPostSlide::class, 'post_id')->orderBy('position');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
