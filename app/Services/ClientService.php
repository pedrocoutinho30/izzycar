<?php

namespace App\Services;

use App\Models\Client;

/**
 * Regras de negócio dos clientes, partilhadas pelo backoffice desktop
 * (ClientV2Controller) e pelo mobile. O follow-up e as atividades são os das
 * leads (LeadService): lead e cliente são o mesmo registo.
 */
class ClientService
{
    public function __construct(private ClientMatcher $matcher)
    {
    }

    /** Validação da criação e da edição (os campos do formulário do desktop). */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'language' => 'nullable|string|in:pt,en',
            'vat_number' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable',
            'identification_number' => 'nullable|string|max:50',
            'validate_identification_number' => 'nullable|date',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'client_type' => 'nullable',
            'origin' => 'nullable|string|max:100',
            'observation' => 'nullable|string',
            'data_processing_consent' => 'nullable|boolean',
            'newsletter_consent' => 'nullable|boolean',
        ];
    }

    /**
     * Aviso de duplicados do desktop (mesmo email OU telefone, ver
     * ClientMatcher), que o utilizador pode confirmar.
     *
     * @return array{message: string, url: string}|null
     */
    public function duplicateWarning(array $data, bool $confirmed): ?array
    {
        return $this->matcher->duplicateWarning($data['email'] ?? null, $data['phone'] ?? null, $confirmed);
    }

    /**
     * Registo que já usa este email. clients.email é único na BD: um email
     * repetido nunca se pode forçar, senão o insert/update rebenta com 500.
     */
    public function emailOwner(?string $email, ?int $ignoreId = null): ?Client
    {
        if (!$email = $this->matcher->normalizeEmail($email)) {
            return null;
        }

        return Client::where(fn ($q) => $q->where('email_normalized', $email)->orWhereRaw('LOWER(email) = ?', [$email]))
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->orderBy('id')
            ->first();
    }

    /** Cria o cliente com os dados já validados (o aviso de duplicados fica no controller). */
    public function create(array $data): Client
    {
        $data['data_processing_consent'] = $data['data_processing_consent'] ?? true;
        $data['newsletter_consent']       = $data['newsletter_consent'] ?? false;

        return Client::create($data);
    }

    /** Atualiza com os dados já validados. Não verifica duplicados (como sempre no desktop). */
    public function update(Client $client, array $data): void
    {
        $client->update($data);
    }
}
