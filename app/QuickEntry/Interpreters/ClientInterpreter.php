<?php

namespace App\QuickEntry\Interpreters;

use App\Models\Client;
use App\Models\User;
use App\Permissions\PermissionService;
use App\QuickEntry\ModuleInterpreter;
use App\QuickEntry\Proposal;
use App\QuickEntry\TextCursor;
use App\QuickEntry\TextNormalizer;
use App\Services\ClientMatcher;
use App\Services\ClientService;

/**
 * "criar cliente João Silva telefone 912 345 678 email joao@x.pt nif 123456789".
 * Só lê: a gravação é do QuickEntryService, após confirmação. Os avisos de
 * duplicados são os do formulário normal (ClientService / ClientMatcher).
 */
class ClientInterpreter implements ModuleInterpreter
{
    public const DEFAULT_LANGUAGE = 'pt';

    public function __construct(
        private ClientService $clients,
        private ClientMatcher $matcher,
        private PermissionService $permissions,
    ) {
    }

    public function command(): string
    {
        return 'cliente.criar';
    }

    public function interpret(Proposal $proposal, TextCursor $cursor, User $user): void
    {
        $this->extractEmail($proposal, $cursor);
        $this->extractVat($proposal, $cursor);
        $this->extractPhone($proposal, $cursor);
        $this->extractName($proposal, $cursor);

        $proposal->set('language', self::DEFAULT_LANGUAGE, 'default', 'Português', 'Valor por defeito do formulário de clientes.');

        $leftover = $this->leftover($cursor);
        if ($leftover !== '') {
            $proposal->leftover = $leftover;
            $proposal->warn('Texto não interpretado: «' . $leftover . '». Foi aproveitado nas observações; edite se preferir.', 'observation');
            $proposal->set('observation', $leftover, 'extracted', null, 'Texto não interpretado da frase.');
        }

        $this->checkDuplicates($proposal, $user);
    }

    /** Frases como "email: a@b.pt" ou "e-mail a@b.pt"; vários emails diferentes são contradição. */
    private function extractEmail(Proposal $p, TextCursor $c): void
    {
        preg_match_all('/(?:\b(?:e-?mail)\s*:?\s*)?[a-z0-9._%+-]+@[a-z0-9-]+(?:\.[a-z0-9-]+)+/', $c->view(), $sets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $found = [];
        foreach ($sets as $set) {
            [$text, $start] = $set[0];
            $c->consume($start, strlen($text));
            $found[strtolower(trim(preg_replace('/^e-?mail\s*:?\s*/', '', $text)))] = true;
        }

        $emails = array_keys($found);
        if (count($emails) > 1) {
            $p->warn('Foram indicados vários emails (' . implode(', ', $emails) . '). Escolha o correto.', 'email', 'error');
            $p->set('email', null, 'extracted');
        } elseif ($emails) {
            $p->set('email', $emails[0], 'extracted', $emails[0]);
            if (! filter_var($emails[0], FILTER_VALIDATE_EMAIL)) {
                $p->warn('O email «' . $emails[0] . '» não parece válido.', 'email', 'error');
            }
        }
    }

    private function extractVat(Proposal $p, TextCursor $c): void
    {
        preg_match_all('/\b(?:nif|contribuinte|vat)\s*(?:n\S{0,2}\s*)?:?\s*(\d{9})(?!\d)/', $c->view(), $sets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $found = [];
        foreach ($sets as $set) {
            $c->consume($set[0][1], strlen($set[0][0]));
            $found[$set[1][0]] = true;
        }

        if (count($found) > 1) {
            $p->warn('Foram indicados vários NIF. Escolha o correto.', 'vat_number', 'error');
            $p->set('vat_number', null, 'extracted');
        } elseif ($found) {
            $nif = array_key_first($found);
            $p->set('vat_number', (string) $nif, 'extracted', (string) $nif);
            if (! $this->validNif((string) $nif)) {
                $p->warn("O NIF {$nif} não passa a validação do dígito de controlo. Confirme.", 'vat_number');
            }
        }
    }

    /** Com palavra-chave (telefone, telemóvel, tlm, contacto) ou, sem ela, um número português de 9 dígitos. */
    private function extractPhone(Proposal $p, TextCursor $c): void
    {
        $number = '\+?\d(?: ?\d){7,13}';
        $found = [];

        foreach (['/\b(?:telefone|telemovel|tlm|tlf|tel|contacto)\s*:?\s*(' . $number . ')(?!\d)/' => 'extracted',
                  '/(?<![\w+.,@-])((?:\+?351 ?)?[29]\d{2} ?\d{3} ?\d{3})(?![\d@.-])/' => 'inferred'] as $pattern => $source) {
            preg_match_all($pattern, $c->view(), $sets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            foreach ($sets as $set) {
                $c->consume($set[0][1], strlen($set[0][0]));
                $found[str_replace(' ', '', $set[1][0])] = $source;
            }
        }

        if (count($found) > 1) {
            $p->warn('Foram indicados vários telefones (' . implode(', ', array_keys($found)) . '). Escolha o correto.', 'phone', 'error');
            $p->set('phone', null, 'extracted');

            return;
        }
        if ($found) {
            $phone = (string) array_key_first($found);
            $note = $found[$phone] === 'inferred' ? 'Inferido: número de 9 dígitos sem a palavra «telefone».' : null;
            $p->set('phone', $phone, $found[$phone], $phone, $note);
        }
    }

    /** O nome é a primeira sequência de palavras livres (depois de «nome», se existir). */
    private function extractName(Proposal $p, TextCursor $c): void
    {
        $words = $c->words();
        if ($words !== [] && $words[0]['text'] === 'nome') {
            $c->consume($words[0]['start'], $words[0]['length']);
            array_shift($words);
            if ($words !== []) {
                $words[0]['break'] = false;
            }
        }

        $tokens = [];
        foreach ($words as $i => $word) {
            if ($i > 0 && $word['break']) {
                break;
            }
            $token = trim($c->originalSlice($word['start'], $word['length']), '.,;:!?()');
            $c->consume($word['start'], $word['length']);
            if ($token !== '') {
                $tokens[] = $token;
            }
        }

        if ($tokens === []) {
            $p->warn('Não encontrei o nome do cliente (ex.: «criar cliente João Silva telefone 912345678»).', 'name', 'error');
            $p->set('name', null, 'extracted');

            return;
        }

        $name = implode(' ', $tokens);
        $note = null;
        if ($name === mb_strtolower($name) || $name === mb_strtoupper($name)) {
            $name = mb_convert_case($name, MB_CASE_TITLE);
            $note = 'Maiúsculas ajustadas (escrito todo em minúsculas ou maiúsculas).';
        }
        $p->set('name', $name, 'extracted', $name, $note);
    }

    private function leftover(TextCursor $c): string
    {
        $skip = ['e', 'de', 'do', 'da', 'com', 'o', 'a', 'um', 'uma'];
        $parts = [];
        foreach ($c->words() as $w) {
            if (! in_array($w['text'], $skip, true) && preg_match('/^[^a-z0-9\x01]+$/', $w['text']) !== 1) {
                $parts[] = $c->originalSlice($w['start'], $w['length']);
            }
        }

        return trim(implode(' ', $parts));
    }

    /** Mesmos avisos do formulário normal; mensagens neutras a quem só vê os seus clientes. */
    private function checkDuplicates(Proposal $p, User $user): void
    {
        $neutral = $this->permissions->scope($user, 'clients', 'view') !== 'all';

        if ($email = $p->value('email')) {
            if ($owner = $this->clients->emailOwner($email)) {
                $p->warn($this->clients->emailTakenMessage($owner, $neutral) . ' Não é possível criar outro com o mesmo email.', 'email', 'error');
            }
        }

        if ($phone = $p->value('phone')) {
            if ($warning = $this->clients->duplicateWarning(['phone' => $phone], false)) {
                $p->warn($this->clients->phoneWarningMessage($warning, $neutral) . ' Tem de confirmar para criar mesmo assim.', 'confirm_duplicate');
            }
        }

        if (($name = $p->value('name')) && ! $neutral) {
            $same = Client::select('id', 'name')->get()->filter(fn ($c) => TextNormalizer::compare($c->name) === TextNormalizer::compare($name))->count();
            if ($same > 0) {
                $p->warn("Já existe {$same} cliente(s) com este nome. Confirme que não é um duplicado.", 'name', 'info');
            }
        }
    }

    /** Dígito de controlo do NIF português. */
    private function validNif(string $nif): bool
    {
        if (! preg_match('/^\d{9}$/', $nif)) {
            return false;
        }
        $sum = 0;
        for ($i = 0; $i < 8; $i++) {
            $sum += (int) $nif[$i] * (9 - $i);
        }
        $check = 11 - ($sum % 11);

        return (int) $nif[8] === ($check >= 10 ? 0 : $check);
    }
}
