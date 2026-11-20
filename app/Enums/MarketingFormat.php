<?php

namespace App\Enums;

/** Formatos de exportação (px). */
enum MarketingFormat: string
{
    use HasOptions;

    case FeedVertical = 'feed_vertical';
    case FeedSquare = 'feed_square';
    case Story = 'story';

    public function width(): int
    {
        return 1080;
    }

    public function height(): int
    {
        return match ($this) {
            self::FeedVertical => 1350,
            self::FeedSquare => 1080,
            self::Story => 1920,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::FeedVertical => 'Feed vertical (1080×1350)',
            self::FeedSquare => 'Feed quadrado (1080×1080)',
            self::Story => 'Stories (1080×1920)',
        };
    }
}
