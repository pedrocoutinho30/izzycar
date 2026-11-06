<?php

namespace App\QuickEntry;

/**
 * Texto de trabalho de um interpretador: o original, a forma normalizada
 * (1 byte por caractere) e uma máscara com o que já foi reconhecido.
 * Reconhecer um campo "consome" o seu trecho, para que as regras seguintes
 * (mais genéricas) não o voltem a ler — é assim que "por pagar" não chega a
 * ser lido como "pagar" e que um valor não se confunde com uma data.
 */
final class TextCursor
{
    private string $normalized;

    /** @var array<int, bool> */
    private array $masked = [];

    public function __construct(private string $original)
    {
        $this->normalized = TextNormalizer::normalize($original);
    }

    public function original(): string
    {
        return $this->original;
    }

    /** Texto normalizado com o já consumido substituído por espaços. */
    public function view(): string
    {
        $view = $this->normalized;
        foreach (array_keys($this->masked) as $i) {
            $view[$i] = ' ';
        }

        return $view;
    }

    public function consume(int $start, int $length): void
    {
        for ($i = $start; $i < $start + $length; $i++) {
            $this->masked[$i] = true;
        }
    }

    /** Trecho do texto original (com maiúsculas e acentos) para uma posição da vista. */
    public function originalSlice(int $start, int $length): string
    {
        return mb_substr($this->original, $start, $length);
    }

    /**
     * Palavras ainda não consumidas, com posição; "break" indica que há texto
     * consumido entre esta palavra e a anterior (uma fronteira de campo).
     *
     * @return list<array{text: string, start: int, length: int, break: bool}>
     */
    public function words(): array
    {
        $view = $this->view();
        preg_match_all('/\S+/', $view, $found, PREG_OFFSET_CAPTURE);

        $words = [];
        $previousEnd = null;
        foreach ($found[0] as [$text, $start]) {
            $break = $previousEnd === null;
            if ($previousEnd !== null) {
                for ($i = $previousEnd; $i < $start; $i++) {
                    if (isset($this->masked[$i])) {
                        $break = true;
                        break;
                    }
                }
            }
            $words[] = ['text' => $text, 'start' => $start, 'length' => strlen($text), 'break' => $break];
            $previousEnd = $start + strlen($text);
        }

        return $words;
    }

    /** Texto original ainda não reconhecido, com as palavras no original. */
    public function leftover(): string
    {
        $parts = [];
        foreach ($this->words() as $word) {
            $parts[] = $this->originalSlice($word['start'], $word['length']);
        }

        return trim(implode(' ', $parts));
    }
}
