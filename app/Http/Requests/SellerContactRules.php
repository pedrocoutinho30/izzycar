<?php

namespace App\Http\Requests;

/**
 * Regras dos campos de um contacto de vendedor (enviados como contact[...]),
 * partilhadas entre o formulário do vendedor e o de contactos.
 */
trait SellerContactRules
{
    protected function contactRules(string $nameRule = 'required'): array
    {
        return [
            'contact' => 'nullable|array',
            'contact.name' => "{$nameRule}|nullable|string|max:255",
            'contact.role' => 'nullable|string|max:255',
            'contact.email' => 'nullable|email|max:255',
            'contact.phone' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+()\s.\-\/]+$/'],
            'contact.whatsapp' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+()\s.\-\/]+$/'],
            'contact.notes' => 'nullable|string|max:5000',
            'contact.is_primary' => 'nullable|boolean',
            'confirm_matches' => 'nullable|boolean',
        ];
    }

    protected function contactMessages(): array
    {
        return [
            'contact.name.required' => 'O nome do contacto é obrigatório.',
            'contact.name.required_with' => 'Indique o nome do contacto.',
            'contact.email.email' => 'O email tem de ser válido.',
            'contact.phone.regex' => 'O telefone só pode ter números, espaços, +, ( ) e -.',
            'contact.whatsapp.regex' => 'O WhatsApp só pode ter números, espaços, +, ( ) e -.',
        ];
    }

    /** Dados do contacto para o model (sem campos vazios de um bloco opcional). */
    public function contactData(): ?array
    {
        $contact = $this->validated('contact') ?? [];

        if (blank($contact['name'] ?? null)) {
            return null;
        }

        return [
            'name' => $contact['name'],
            'role' => $contact['role'] ?? null,
            'email' => $contact['email'] ?? null,
            'phone' => $contact['phone'] ?? null,
            'whatsapp' => $contact['whatsapp'] ?? null,
            'notes' => $contact['notes'] ?? null,
            'is_primary' => (bool) ($contact['is_primary'] ?? false),
        ];
    }

    public function matchesConfirmed(): bool
    {
        return $this->boolean('confirm_matches');
    }
}
