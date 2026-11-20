<?php

namespace App\Marketing;

use App\Models\Testimonial;

/** Instantâneo de um post de testemunho. O texto do cliente nunca é reescrito. */
class TestimonialPostData
{
    public const MAX_QUOTE = 280;

    /**
     * @return array{testimonial_id:int,comment:string,rating:float,origin:string,first_name:string,full_name:string,review_date:?string}
     *
     * @throws \DomainException sem autorização de uso ou sem comentário
     */
    public static function fromTestimonial(Testimonial $t): array
    {
        if (! $t->marketing_consent) {
            throw new \DomainException('Este testemunho não tem autorização de uso em publicações.');
        }
        $comment = (string) $t->comment;
        if (trim($comment) === '') {
            throw new \DomainException('Este testemunho não tem comentário.');
        }
        $full = trim((string) $t->name);

        return [
            'testimonial_id' => $t->id,
            'comment' => $comment,
            'rating' => (float) $t->rating,
            'origin' => (string) $t->origin,
            'first_name' => preg_split('/\s+/u', $full)[0] ?? '',
            'full_name' => $full,
            'review_date' => $t->review_date?->toDateString(),
        ];
    }

    /** O excerto tem de existir, contínuo, no comentário (ignora maiúsculas e espaços repetidos). */
    public static function isExcerpt(string $comment, string $quote): bool
    {
        $q = self::norm($quote);
        if ($q === '' || mb_strlen($q) > self::MAX_QUOTE) {
            return false;
        }

        // Limites de palavra nas duas pontas: não se corta "impecável" em "pecável".
        return (bool) preg_match('/(?<![\p{L}\p{N}])' . preg_quote($q, '/') . '(?![\p{L}\p{N}])/u', self::norm($comment));
    }

    /** @return array{0:bool,1:bool} o excerto não começa / não acaba onde o comentário começa / acaba */
    public static function trimmedEnds(string $comment, string $quote): array
    {
        $c = self::norm($comment);
        $q = self::norm($quote);

        return [$q !== '' && ! str_starts_with($c, $q), $q !== '' && ! str_ends_with($c, $q)];
    }

    private static function norm(string $s): string
    {
        return trim(preg_replace('/\s+/u', ' ', mb_strtolower($s)));
    }
}
