<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsArticle extends Model
{
    protected $fillable = [
        'title', 'slug', 'subtitle', 'content', 'summary',
        'cover_image', 'gallery', 'status', 'published_at',
        'seo_title', 'seo_description', 'seo_keywords',
    ];

    protected $casts = [
        'gallery'      => 'array',
        'published_at' => 'datetime',
    ];

    // Scope: only published articles
    public function scopePublished($query)
    {
        return $query->where('status', 'Publicado');
    }

    // Reading time in minutes
    public function getReadTimeAttribute(): int
    {
        return max(1, (int)(str_word_count(strip_tags($this->content)) / 200));
    }

    // Auto-generate slug from title
    public static function generateSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $base = $slug;
        $i    = 1;
        while (
            static::where('slug', $slug)
                  ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                  ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
