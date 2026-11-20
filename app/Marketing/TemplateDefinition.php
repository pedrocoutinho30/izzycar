<?php

namespace App\Marketing;

use App\Enums\MarketingFormat;
use App\Enums\MarketingPostType;

/** Descrição de um template gráfico: o que suporta e que campos o editor mostra. */
final class TemplateDefinition
{
    /**
     * @param  MarketingPostType[]  $types
     * @param  MarketingFormat[]    $formats
     * @param  string[]             $fields  campos editáveis (title, subtitle, price, highlights, cta, photos, items)
     */
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly string $description,
        public readonly array $types,
        public readonly array $formats,
        public readonly array $fields,
        public readonly int $minSlides = 1,
        public readonly int $maxSlides = 1,
        public readonly int $maxItems = 0,
    ) {
        $this->view = 'admin.marketing.templates.' . $key;
    }

    public readonly string $view;

    public function supports(MarketingFormat $format): bool
    {
        return in_array($format, $this->formats, true);
    }

    public function supportsType(MarketingPostType $type): bool
    {
        return in_array($type, $this->types, true);
    }
}
