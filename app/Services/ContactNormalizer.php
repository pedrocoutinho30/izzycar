<?php

namespace App\Services;

/**
 * Normalização de emails, domínios e telefones dos contactos de vendedores,
 * para que a deteção de duplicados compare valores equivalentes.
 *
 * Telefones ficam em dígitos com indicativo (E.164 sem o "+"), para que
 * "+49 171 123 4567", "+491711234567" e "0049 171 123 4567" coincidam.
 * Números nacionais (sem indicativo) só são convertidos se o país do
 * vendedor for conhecido — sem país ficam só em dígitos.
 */
class ContactNormalizer
{
    /**
     * Indicativo e prefixo nacional (trunk) dos países de
     * ImportOpportunity::COUNTRIES. Itália mantém o 0 no número internacional,
     * por isso não tem trunk.
     */
    private const DIAL_CODES = [
        'DE' => ['49', '0'],
        'AT' => ['43', '0'],
        'BE' => ['32', '0'],
        'DK' => ['45', null],
        'ES' => ['34', null],
        'FR' => ['33', '0'],
        'NL' => ['31', '0'],
        'IT' => ['39', null],
        'LU' => ['352', null],
        'PL' => ['48', null],
        'CZ' => ['420', null],
        'SE' => ['46', '0'],
        'NO' => ['47', null],
        'CH' => ['41', '0'],
        'GB' => ['44', '0'],
        'PT' => ['351', null],
    ];

    /**
     * Fornecedores de email genéricos: partilhar o domínio não diz nada sobre
     * a empresa (gmx.de/web.de/t-online.de são muito usados por stands
     * alemães pequenos).
     */
    private const GENERIC_DOMAINS = [
        'gmail.com', 'googlemail.com', 'hotmail.com', 'hotmail.de', 'hotmail.fr', 'hotmail.es', 'hotmail.it',
        'outlook.com', 'outlook.de', 'outlook.pt', 'live.com', 'live.de', 'msn.com',
        'yahoo.com', 'yahoo.de', 'yahoo.fr', 'yahoo.es', 'icloud.com', 'me.com', 'mac.com', 'aol.com',
        'gmx.de', 'gmx.net', 'gmx.at', 'gmx.ch', 'gmx.com', 'web.de', 't-online.de', 'freenet.de', 'posteo.de',
        'mail.de', 'online.de', 'arcor.de', 'orange.fr', 'free.fr', 'wanadoo.fr', 'laposte.net',
        'libero.it', 'virgilio.it', 'telenet.be', 'skynet.be', 'ziggo.nl', 'kpnmail.nl',
        'sapo.pt', 'mail.com', 'proton.me', 'protonmail.com', 'wp.pl', 'o2.pl', 'seznam.cz',
    ];

    public function email(?string $email): ?string
    {
        $email = mb_strtolower(trim((string) $email));

        return $email === '' ? null : $email;
    }

    public function emailDomain(?string $email): ?string
    {
        $email = $this->email($email);

        if ($email === null || !str_contains($email, '@')) {
            return null;
        }

        return $this->domain(substr($email, strrpos($email, '@') + 1));
    }

    /**
     * Aceita "autohaus.de", "@autohaus.de", "www.autohaus.de" ou um URL.
     */
    public function domain(?string $value): ?string
    {
        $value = mb_strtolower(trim((string) $value));
        $value = ltrim($value, '@');

        if ($value === '') {
            return null;
        }

        if (str_contains($value, '/') || str_contains($value, ':')) {
            $host = parse_url(str_contains($value, '://') ? $value : "http://{$value}", PHP_URL_HOST);
            $value = $host ?: $value;
        }

        $value = preg_replace('/^www\./', '', $value);

        return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $value) ? $value : null;
    }

    /**
     * Lista de domínios a partir de texto livre (vírgulas, espaços ou linhas).
     *
     * @return list<string>
     */
    public function domains(?string $value): array
    {
        return collect(preg_split('/[\s,;]+/', (string) $value))
            ->map(fn ($domain) => $this->domain($domain))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function isGenericDomain(?string $domain): bool
    {
        return $domain !== null && in_array($domain, self::GENERIC_DOMAINS, true);
    }

    public function phone(?string $phone, ?string $country = null): ?string
    {
        $raw = trim((string) $phone);

        if ($raw === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $raw);

        if (strlen($digits) < 6) {
            return null;
        }

        if (str_starts_with($raw, '+')) {
            return $digits;
        }

        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }

        [$dialCode, $trunk] = self::DIAL_CODES[strtoupper((string) $country)] ?? [null, null];

        if ($dialCode === null) {
            return $digits;
        }

        if ($trunk !== null && str_starts_with($digits, $trunk)) {
            return $dialCode . substr($digits, strlen($trunk));
        }

        // Já traz o indicativo, só sem "+" (ex. "491711234567").
        if (str_starts_with($digits, $dialCode) && strlen($digits) >= strlen($dialCode) + 8) {
            return $digits;
        }

        return $dialCode . $digits;
    }
}
