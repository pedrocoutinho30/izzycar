<?php

namespace App\Services;

use App\Models\Client;
use App\Models\LeadActivity;
use App\Support\ClientMatch;
use Illuminate\Support\Str;

/**
 * Regra única para saber se um contacto já existe: mesmo email (sem
 * maiúsculas/espaços) OU mesmo telefone (últimos 9 dígitos). O nome nunca
 * conta. Usada por todas as entradas que criam leads/clientes.
 *
 * Email e telefone podem ser de pessoas diferentes (famílias que partilham
 * um número, erros de escrita): quem usa o resultado nunca apaga dados
 * existentes — só preenche o que falta — e fica registo na timeline.
 */
class ClientMatcher
{
    public function normalizeEmail(?string $email): ?string
    {
        $email = mb_strtolower(trim((string) $email));

        return $email === '' ? null : $email;
    }

    /** Últimos 9 dígitos (telemóveis portugueses, com ou sem +351); menos de 9 dígitos não identifica ninguém. */
    public function phoneKey(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        return strlen($digits) >= 9 ? substr($digits, -9) : null;
    }

    public function find(?string $email, ?string $phone, ?int $ignoreId = null): ?ClientMatch
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $key = $this->phoneKey($phone);

        $byEmail = $normalizedEmail ? $this->first('email_normalized', $normalizedEmail, $ignoreId) : null;
        $byPhone = $key ? $this->first('phone_key', $key, $ignoreId) : null;

        if (!$byEmail && !$byPhone) {
            return null;
        }

        if ($byEmail && $byPhone) {
            return $byEmail->is($byPhone)
                ? new ClientMatch($byEmail, 'email+phone')
                : new ClientMatch($byEmail, 'email', conflict: $byPhone);
        }

        return $byEmail ? new ClientMatch($byEmail, 'email') : new ClientMatch($byPhone, 'phone');
    }

    /**
     * Para formulários do backoffice: se já existe um registo com este email
     * ou telefone (e o utilizador não confirmou), devolve o aviso a mostrar.
     *
     * @return array{message: string, url: string}|null
     */
    public function duplicateWarning(?string $email, ?string $phone, bool $confirmed, ?int $ignoreId = null): ?array
    {
        if ($confirmed || !($match = $this->find($email, $phone, $ignoreId))) {
            return null;
        }

        $client = $match->client;

        return [
            'message' => "Já existe «{$client->name}» com o mesmo {$match->byLabel()}.",
            'url' => route($client->is_lead ? 'admin.v2.leads.show' : 'admin.v2.clients.show', $client->id),
        ];
    }

    /**
     * Preenche só o que falta no cliente existente (nunca substitui) e junta
     * os consentimentos (quem já aceitou continua a ter aceite).
     *
     * @param  array{name?: ?string, email?: ?string, phone?: ?string, newsletter_consent?: ?bool, data_processing_consent?: ?bool}  $data
     */
    public function complete(Client $client, array $data): void
    {
        $fill = [];
        foreach (['name', 'email', 'phone'] as $field) {
            if (blank($client->{$field}) && filled($data[$field] ?? null)) {
                $fill[$field] = $data[$field];
            }
        }
        foreach (['newsletter_consent', 'data_processing_consent'] as $field) {
            if (!empty($data[$field]) && !$client->{$field}) {
                $fill[$field] = true;
            }
        }

        if ($fill) {
            $client->update($fill);
        }
    }

    /**
     * Deixa na timeline que este contacto foi associado a um registo
     * existente — com aviso quando o nome é outro ou o email e o telefone
     * pertencem a registos diferentes.
     */
    public function logMatch(ClientMatch $match, ?string $submittedName, string $source): void
    {
        $client = $match->client;

        if ($match->conflict) {
            LeadActivity::log(
                $client->id,
                "{$source}: email e telefone de registos diferentes",
                "O email corresponde a este registo, mas o telefone corresponde a «{$match->conflict->name}» (#{$match->conflict->id}). Reveja se são a mesma pessoa e, se forem, una os registos.",
                'bi-exclamation-triangle-fill',
                'warning'
            );

            return;
        }

        if (filled($submittedName) && !$this->sameName($client->name, $submittedName)) {
            LeadActivity::log(
                $client->id,
                "{$source}: submetido com outro nome",
                "Contacto associado a este registo pelo {$match->byLabel()}, mas submetido com o nome «{$submittedName}». Pode ser outra pessoa que partilha o contacto.",
                'bi-person-exclamation',
                'warning'
            );
        }
    }

    public function sameName(?string $a, ?string $b): bool
    {
        $a = Str::lower(Str::ascii(trim((string) $a)));
        $b = Str::lower(Str::ascii(trim((string) $b)));

        return $a === '' || $b === '' || $a === $b || str_contains($a, $b) || str_contains($b, $a);
    }

    private function first(string $column, string $value, ?int $ignoreId): ?Client
    {
        return Client::where($column, $value)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->orderBy('id')
            ->first();
    }
}
