<?php

namespace App\QuickEntry\Interpreters;

use App\Models\Expense;
use App\Models\Legalization;
use App\Models\User;
use App\QuickEntry\ModuleInterpreter;
use App\QuickEntry\Proposal;
use App\QuickEntry\ReferenceResolver;
use App\QuickEntry\TextCursor;
use App\QuickEntry\TextNormalizer;
use Carbon\CarbonImmutable;

/**
 * "criar movimento ...": extrai campo a campo, do mais específico para o mais
 * genérico (datas e valores antes de nomes, expressões negativas antes das
 * palavras isoladas). Cada extração consome o seu trecho do texto. Só lê da
 * base de dados; a gravação é do QuickEntryService, após confirmação.
 */
class MovementInterpreter implements ModuleInterpreter
{
    /** Valores por defeito do formulário de movimentos (MovementV2Controller::create / form.blade). */
    public const DEFAULT_STATUS = 'paid';
    public const DEFAULT_VAT_RATE = 23;

    private const KEYWORDS = ['categoria', 'cliente', 'legalizacao'];

    private const FILLERS = ['de', 'do', 'da', 'dos', 'das', 'o', 'a', 'os', 'as', 'para', 'com', 'ao', 'pelo', 'pela', 'e', 'um', 'uma'];

    private const CUR = '\x01';

    private bool $receivedWord = false;

    public function __construct(private ReferenceResolver $resolver)
    {
    }

    public function command(): string
    {
        return 'movimento.criar';
    }

    public function interpret(Proposal $proposal, TextCursor $cursor, User $user): void
    {
        $now = CarbonImmutable::now('Europe/Lisbon');
        $this->receivedWord = false;

        $this->extractDate($proposal, $cursor, $now);
        $this->extractAmount($proposal, $cursor);
        $this->extractStatus($proposal, $cursor);
        $this->extractMethod($proposal, $cursor);
        $this->extractType($proposal, $cursor);
        $explicitCategory = $this->extractKeywordFields($proposal, $cursor, $user);
        $this->applyTransportRule($proposal, $cursor, $explicitCategory);
        $this->reconcileClientAndLegalization($proposal, $user);
        $this->applyDefaults($proposal, $now);
        $this->composeTexts($proposal, $cursor);
    }

    // ── Data ────────────────────────────────────────────────────────────────

    private function extractDate(Proposal $p, TextCursor $c, CarbonImmutable $now): void
    {
        $pattern = '/\b(hoje|ontem)\b|(?<![\d\/.,-])(\d{1,2})\/(\d{1,2})\/(\d{4})(?![\d\/])/';
        preg_match_all($pattern, $c->view(), $sets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $dates = [];
        $invalid = false;
        foreach ($sets as $set) {
            [$text, $start] = $set[0];
            $c->consume($start, strlen($text));

            if ($set[1][0] !== '') {
                $date = $set[1][0] === 'hoje' ? $now->startOfDay() : $now->startOfDay()->subDay();
                $dates[$date->toDateString()] = ['label' => $date->format('d/m/Y'), 'note' => 'Resolvido com a data de Lisboa (' . $set[1][0] . ').'];

                continue;
            }

            [$d, $m, $y] = [(int) $set[2][0], (int) $set[3][0], (int) $set[4][0]];
            if (! checkdate($m, $d, $y)) {
                $invalid = true;
                $p->warn('Data inválida: «' . $c->originalSlice($start, strlen($text)) . '».', 'expense_date', 'error');

                continue;
            }
            $iso = sprintf('%04d-%02d-%02d', $y, $m, $d);
            $dates[$iso] = ['label' => sprintf('%02d/%02d/%04d', $d, $m, $y), 'note' => null];
        }

        if (count($dates) > 1) {
            $p->warn('Foram indicadas várias datas (' . implode(', ', array_column($dates, 'label')) . '). Escolha a correta.', 'expense_date');
            $p->set('expense_date', null, 'extracted');
            $p->needsChoice('expense_date');
        } elseif (count($dates) === 1) {
            $iso = array_key_first($dates);
            $p->set('expense_date', $iso, 'extracted', $dates[$iso]['label'], $dates[$iso]['note']);
        } elseif ($invalid) {
            $p->set('expense_date', null, 'extracted');
            $p->needsChoice('expense_date');
        }
    }

    // ── Valor ───────────────────────────────────────────────────────────────

    private function extractAmount(Proposal $p, TextCursor $c): void
    {
        $number = '(-)?(\d{1,3}(?:\.\d{3})+|\d+)(?:,(\d{1,2})|\.(\d{1,2}))?';
        $patterns = [
            '/(?<![\w.,\/-])' . $number . '\s*(?:' . self::CUR . '|euros?\b|eur\b)/',
            '/(?<![\w.,\/-])' . self::CUR . '\s*' . $number . '(?![\d.,]*\d)/',
        ];

        $amounts = [];
        $negative = false;
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $c->view(), $sets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            foreach ($sets as $set) {
                [$text, $start] = $set[0];
                $c->consume($start, strlen($text));
                $original = trim($c->originalSlice($start, strlen($text)));

                if (($set[1][0] ?? '') === '-') {
                    $negative = true;
                    $p->warn("Valor negativo não aceite: «{$original}». Indique o valor sem sinal e escolha o tipo.", 'amount', 'error');

                    continue;
                }

                $integer = str_replace('.', '', $set[2][0]);
                if (strlen($integer) > 9) {
                    $p->warn("Valor demasiado grande: «{$original}».", 'amount', 'error');

                    continue;
                }
                $decimals = ($set[3][0] ?? '') !== '' ? $set[3][0] : ($set[4][0] ?? '');
                if (($set[4][0] ?? '') !== '') {
                    $p->warn("«{$original}» tem ponto decimal; foi lido como " . $this->money(((int) $integer) * 100 + (int) str_pad($decimals, 2, '0')) . '. Confirme o valor.', 'amount', 'info');
                }

                $cents = ((int) $integer) * 100 + (int) str_pad($decimals, 2, '0');
                $amounts[$cents] = true;
            }
        }

        // Algo que parece um valor mas não cumpre o formato (ex. "10,555€").
        preg_match_all('/\d[\d.,]*\s*(?:' . self::CUR . '|euros?\b|eur\b)/', $c->view(), $loose, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $invalid = $negative;
        foreach ($loose as $set) {
            [$text, $start] = $set[0];
            $invalid = true;
            $p->warn('Formato de valor inválido: «' . trim($c->originalSlice($start, strlen($text))) . '». Use por exemplo 10€, 922,50 € ou 1.250,50 €.', 'amount', 'error');
            $c->consume($start, strlen($text));
        }

        $cents = array_keys($amounts);
        if (count($cents) > 1) {
            $p->warn('Foram indicados vários valores (' . implode(', ', array_map(fn ($v) => $this->money($v), $cents)) . '). Escolha o correto.', 'amount');
            $p->set('amount', null, 'extracted');
            $p->options['amount'] = array_map(fn ($v) => ['id' => $this->decimal($v), 'label' => $this->money($v)], $cents);
            $p->needsChoice('amount');

            return;
        }

        if (count($cents) === 1) {
            if ($cents[0] <= 0) {
                $p->warn('O valor indicado é nulo (0 €). Indique um valor superior a zero.', 'amount', 'error');
                $p->set('amount', null, 'extracted');

                return;
            }
            $p->set('amount', $this->decimal($cents[0]), 'extracted', $this->money($cents[0]));

            return;
        }

        $p->set('amount', null, 'extracted');
        if (! $invalid) {
            $hint = preg_match('/(?<![\w\/.,-])\d+(?:[.,]\d+)?(?![\w\/])/', $c->view()) === 1
                ? ' Há um número sem moeda no texto; escreva-o com € ou «euros» para ser lido como valor.'
                : '';
            $p->warn('Não encontrei o valor (por exemplo 10€, 922,50 € ou 1.250,50 €).' . $hint, 'amount');
        }
    }

    // ── Estado ──────────────────────────────────────────────────────────────

    private function extractStatus(Proposal $p, TextCursor $c): void
    {
        $found = [];
        // As expressões negativas primeiro: "por pagar" nunca chega a ser lido como "pagar".
        foreach ([
            'pending' => '/\b(?:nao\s+pag[oa]|por\s+pagar|nao\s+recebid[oa]|por\s+receber|pendente)\b/',
            'paid' => '/\b(?:pag[oa]|recebid[oa])\b/',
        ] as $status => $pattern) {
            preg_match_all($pattern, $c->view(), $sets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            foreach ($sets as $set) {
                [$text, $start] = $set[0];
                $found[$status][] = $c->originalSlice($start, strlen($text));
                if ($status === 'paid' && str_starts_with($text, 'recebid')) {
                    $this->receivedWord = true;
                }
                $c->consume($start, strlen($text));
            }
        }

        if (count($found) > 1) {
            $p->warn('Estados contraditórios («' . implode('», «', array_merge(...array_values($found))) . '»). Escolha o estado.', 'status', 'error');
            $p->set('status', null, 'extracted');
            $p->needsChoice('status');
        } elseif (isset($found['pending'])) {
            $p->set('status', 'pending', 'extracted', Expense::statuses()['pending'], 'Lido de «' . $found['pending'][0] . '».');
        } elseif (isset($found['paid'])) {
            $p->set('status', 'paid', 'extracted', Expense::statuses()['paid'], 'Lido de «' . $found['paid'][0] . '». Nas receitas, «Pago» é o estado de recebido.');
        }
    }

    // ── Método de pagamento ─────────────────────────────────────────────────

    private function extractMethod(Proposal $p, TextCursor $c): void
    {
        $found = [];
        foreach ([
            'bank_transfer' => '/\b(?:transferencia\s+bancaria|por\s+transferencia|transferencia)\b/',
            'cash' => '/\b(?:em\s+dinheiro|dinheiro|numerario)\b/',
        ] as $method => $pattern) {
            preg_match_all($pattern, $c->view(), $sets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            foreach ($sets as $set) {
                [$text, $start] = $set[0];
                $found[$method][] = $c->originalSlice($start, strlen($text));
                $c->consume($start, strlen($text));
            }
        }

        if (count($found) > 1) {
            $p->warn('Métodos de pagamento incompatíveis («' . implode('», «', array_merge(...array_values($found))) . '»). Escolha o método.', 'payment_method', 'error');
            $p->set('payment_method', null, 'extracted');
            $p->needsChoice('payment_method');
        } elseif ($found !== []) {
            $key = array_key_first($found);
            $p->set('payment_method', $key, 'extracted', Expense::paymentMethods()[$key], 'Lido de «' . $found[$key][0] . '».');
        }
    }

    // ── Tipo ────────────────────────────────────────────────────────────────

    private function extractType(Proposal $p, TextCursor $c): void
    {
        $found = [];
        foreach ([
            'expense' => '/\b(?:despesa|saida)\b/',
            'income' => '/\b(?:receita|entrada)\b/',
        ] as $type => $pattern) {
            preg_match_all($pattern, $c->view(), $sets, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            foreach ($sets as $set) {
                [$text, $start] = $set[0];
                $found[$type][] = $c->originalSlice($start, strlen($text));
                $c->consume($start, strlen($text));
            }
        }

        if (count($found) > 1) {
            $p->warn('Tipos incompatíveis («' . implode('», «', array_merge(...array_values($found))) . '»). Escolha entre despesa e receita.', 'movement_type', 'error');
            $p->set('movement_type', null, 'extracted');
            $p->needsChoice('movement_type');
        } elseif ($found !== []) {
            $key = array_key_first($found);
            $p->set('movement_type', $key, 'extracted', Expense::movementTypes()[$key], 'Lido de «' . $found[$key][0] . '».');
        }
    }

    // ── Categoria, cliente e legalização (campos com nome) ──────────────────

    /** @return bool true se a frase indicou "categoria ..." (mesmo que não se tenha resolvido) */
    private function extractKeywordFields(Proposal $p, TextCursor $c, User $user): bool
    {
        $occurrences = $this->collectKeywordRuns($c);

        $this->resolveCategory($p, $c, $occurrences['categoria'] ?? []);
        $this->resolveClient($p, $c, $user, $occurrences['cliente'] ?? []);
        $this->resolveLegalization($p, $c, $user, $occurrences['legalizacao'] ?? []);

        return isset($occurrences['categoria']);
    }

    /**
     * Cada "categoria|cliente|legalização" abre um campo cujo nome vai até ao
     * próximo campo reconhecido (já consumido), outra palavra-chave ou ao fim.
     *
     * @return array<string, list<array{words: list<array>, run: list<array>}>>
     */
    private function collectKeywordRuns(TextCursor $c): array
    {
        $words = $c->words();
        $n = count($words);
        $occurrences = [];

        for ($i = 0; $i < $n; $i++) {
            $keyword = $words[$i]['text'];
            if (! in_array($keyword, self::KEYWORDS, true)) {
                continue;
            }

            $consumed = [$words[$i]];
            $j = $i + 1;
            while ($j < $n && ! $words[$j]['break'] && in_array($words[$j]['text'], self::FILLERS, true)) {
                $consumed[] = $words[$j++];
            }
            // "legalização do cliente Fábio": o "cliente" faz parte da referência, não abre um campo.
            if ($keyword === 'legalizacao' && $j < $n && ! $words[$j]['break'] && $words[$j]['text'] === 'cliente') {
                $consumed[] = $words[$j++];
            }

            $run = [];
            while ($j < $n && ! $words[$j]['break'] && ! in_array($words[$j]['text'], self::KEYWORDS, true)) {
                $run[] = $words[$j++];
            }

            $occurrences[$keyword][] = ['words' => $consumed, 'run' => $run];
            $i = $j - 1;
        }

        return $occurrences;
    }

    /** @param list<array{text: string, start: int, length: int}> $words */
    private function consumeWords(TextCursor $c, array $words): void
    {
        foreach ($words as $word) {
            $c->consume($word['start'], $word['length']);
        }
    }

    /** @return list<string> palavras do nome, sem pontuação nas pontas */
    private function tokens(TextCursor $c, array $run): array
    {
        $tokens = [];
        foreach ($run as $word) {
            $token = trim($c->originalSlice($word['start'], $word['length']), '.,;:!?()');
            if ($token !== '') {
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    /** Uma só ocorrência utilizável; várias iguais contam como uma; várias diferentes são contradição. */
    private function single(Proposal $p, TextCursor $c, array $occurrences, string $field, string $what): ?array
    {
        foreach ($occurrences as $occurrence) {
            $this->consumeWords($c, $occurrence['words']);
        }
        if ($occurrences === []) {
            return null;
        }

        $names = [];
        foreach ($occurrences as $occurrence) {
            $this->consumeWords($c, $occurrence['run']);
            $names[TextNormalizer::compare(implode(' ', $this->tokens($c, $occurrence['run'])))] = $occurrence;
        }

        if (count($names) > 1) {
            $p->warn("{$what} indicado mais de uma vez com valores diferentes. Escolha o correto.", $field, 'error');
            $p->set($field, null, 'extracted');
            $p->needsChoice($field);

            return null;
        }

        return array_values($names)[0];
    }

    private function resolveCategory(Proposal $p, TextCursor $c, array $occurrences): void
    {
        if ($occurrences === []) {
            return;
        }

        // A categoria consome só as palavras que reconhece; o resto fica como texto não interpretado.
        if (count($occurrences) === 1) {
            $occurrence = $occurrences[0];
            $tokens = $this->tokens($c, $occurrence['run']);
            $this->consumeWords($c, $occurrence['words']);

            if ($tokens === []) {
                $p->warn('Indicou «categoria» sem dizer qual.', 'category');
                $p->set('category', null, 'extracted');
                $p->needsChoice('category');

                return;
            }

            $match = $this->resolver->matchCategory($tokens);
            if ($match === null) {
                $this->consumeWords($c, $occurrence['run']);
                $p->warn('Não encontrei a categoria «' . implode(' ', $tokens) . '». Escolha uma das existentes (não são criadas categorias).', 'category');
                $p->set('category', null, 'extracted');
                $p->needsChoice('category');

                return;
            }

            $this->consumeWords($c, array_slice($occurrence['run'], 0, $match['used']));
            $p->set('category', $match['key'], 'extracted', $match['label'], 'Categoria indicada na frase.');

            return;
        }

        $this->single($p, $c, $occurrences, 'category', 'A categoria');
    }

    private function resolveClient(Proposal $p, TextCursor $c, User $user, array $occurrences): void
    {
        $occurrence = $this->single($p, $c, $occurrences, 'client_id', 'O cliente');
        if ($occurrence === null) {
            return;
        }

        $name = implode(' ', $this->tokens($c, $occurrence['run']));
        if ($name === '') {
            $p->warn('Indicou «cliente» sem dizer o nome.', 'client_id');
            $p->set('client_id', null, 'extracted');
            $p->needsChoice('client_id');

            return;
        }
        if (! $this->resolver->canSearchClients($user)) {
            $p->warn('Não tem acesso à lista de clientes; não foi possível procurar «' . $name . '».', 'client_id');
            $p->set('client_id', null, 'extracted');
            $p->needsChoice('client_id');

            return;
        }

        $match = $this->resolver->matchClients($user, $name);
        $p->set('client_id', null, 'extracted');

        if ($match['exact']->count() === 1) {
            $client = $match['exact']->first();
            $p->set('client_id', $client->id, 'extracted', $client->name, 'Cliente «' . $name . '» encontrado.');

            return;
        }

        $p->needsChoice('client_id');
        if ($match['exact']->count() > 1) {
            $p->warn('Há ' . $match['exact']->count() . ' clientes com o nome «' . $name . '». Escolha o correto.', 'client_id');
            $p->options['client_id'] = $match['exact']->map(fn ($cl) => ['id' => $cl->id, 'label' => $this->resolver->clientLabel($cl)])->all();
        } elseif ($match['candidates']->isNotEmpty()) {
            $p->warn('Não há nenhum cliente chamado exatamente «' . $name . '». Escolha entre os nomes semelhantes (não escolho por aproximação).', 'client_id');
            $p->options['client_id'] = $match['candidates']->map(fn ($cl) => ['id' => $cl->id, 'label' => $this->resolver->clientLabel($cl)])->all();
        } else {
            $p->warn('Não encontrei nenhum cliente «' . $name . '». Selecione-o manualmente (não é criado nenhum cliente).', 'client_id');
        }
    }

    private function resolveLegalization(Proposal $p, TextCursor $c, User $user, array $occurrences): void
    {
        $occurrence = $this->single($p, $c, $occurrences, 'legalization_id', 'A legalização');
        if ($occurrence === null) {
            return;
        }

        $text = implode(' ', $this->tokens($c, $occurrence['run']));
        if ($text === '') {
            $p->warn('Indicou «legalização» sem dizer o cliente ou a referência.', 'legalization_id');
            $p->set('legalization_id', null, 'extracted');
            $p->needsChoice('legalization_id');

            return;
        }
        if (! $this->resolver->canSearchLegalizations($user)) {
            $p->warn('Não tem acesso às legalizações; não foi possível procurar «' . $text . '».', 'legalization_id');
            $p->set('legalization_id', null, 'extracted');
            $p->needsChoice('legalization_id');

            return;
        }

        $match = $this->resolver->matchLegalizations($user, $text);
        $p->set('legalization_id', null, 'extracted');

        if ($match['exact']->count() === 1) {
            /** @var Legalization $legalization */
            $legalization = $match['exact']->first();
            $p->set('legalization_id', $legalization->id, 'extracted', $this->resolver->legalizationLabel($legalization), 'Única legalização que corresponde a «' . $text . '».');

            return;
        }

        $p->needsChoice('legalization_id');
        $options = fn ($set) => $set->map(fn (Legalization $l) => ['id' => $l->id, 'label' => $this->resolver->legalizationLabel($l)])->all();

        if ($match['exact']->count() > 1) {
            $p->warn('Há ' . $match['exact']->count() . ' legalizações para «' . $text . '». Escolha o processo correto.', 'legalization_id');
            $p->options['legalization_id'] = $options($match['exact']);
        } elseif ($match['candidates']->isNotEmpty()) {
            $p->warn('Não há legalização com correspondência exata a «' . $text . '». Escolha entre as semelhantes (não escolho por aproximação).', 'legalization_id');
            $p->options['legalization_id'] = $options($match['candidates']);
        } else {
            $p->warn('Não encontrei nenhuma legalização para «' . $text . '». Selecione-a manualmente.', 'legalization_id');
        }
    }

    // ── Regra do "transporte" ───────────────────────────────────────────────

    private function applyTransportRule(Proposal $p, TextCursor $c, bool $explicitCategory): void
    {
        $inText = preg_match('/\btransportes?\b/', $c->view(), $m, PREG_OFFSET_CAPTURE) === 1;
        $explicitTransport = $p->value('category') === 'transport';

        if (! $inText && ! $explicitTransport) {
            return;
        }

        // Categoria explícita prevalece; só sem ela se sugere "Transporte".
        if ($inText && ! $explicitCategory) {
            $c->consume($m[0][1], strlen($m[0][0]));
            $p->set('category', 'transport', 'inferred', Expense::categories()['transport'], 'Inferido da palavra «transporte».');
        } elseif ($inText) {
            $p->warn('A palavra «transporte» ficou de fora: a categoria indicada na frase prevalece.', 'category', 'info');
        }

        if ($p->value('movement_type') === null && ! in_array('movement_type', $p->unresolved, true)) {
            $p->set('movement_type', 'expense', 'inferred', Expense::movementTypes()['expense'], 'Inferido de «transporte» (sem tipo explícito).');
        }
    }

    // ── Cliente ↔ legalização ───────────────────────────────────────────────

    private function reconcileClientAndLegalization(Proposal $p, User $user): void
    {
        $legalizationId = $p->value('legalization_id');
        $clientId = $p->value('client_id');

        if ($legalizationId) {
            $legalization = Legalization::with('client:id,name')->find($legalizationId);
            $owner = $legalization?->client;

            if ($owner && $clientId && (int) $clientId !== (int) $owner->id) {
                $p->warn('Contradição: a legalização indicada pertence a «' . $owner->name . '», mas o cliente indicado é outro. Corrija antes de confirmar.', 'client_id', 'error');
                $p->needsChoice('client_id');
                $p->needsChoice('legalization_id');
            } elseif ($owner && ! $clientId) {
                // Relação existente: o cliente da legalização preenche o movimento.
                $p->set('client_id', $owner->id, 'relation', $owner->name, 'Cliente da legalização escolhida.');
                $p->unresolved = array_values(array_diff($p->unresolved, ['client_id']));
            }

            return;
        }

        if ($clientId && ! in_array('legalization_id', $p->unresolved, true)) {
            $count = Legalization::where('client_id', $clientId)->count();
            if ($count > 0) {
                $p->warn("Este cliente tem {$count} legalização(ões); não foi associada nenhuma automaticamente. Escolha uma se quiser ligar o movimento.", 'legalization_id', 'info');
            }
        }
    }

    // ── Valores por defeito e textos ────────────────────────────────────────

    private function applyDefaults(Proposal $p, CarbonImmutable $now): void
    {
        if ($p->value('movement_type') === null) {
            $p->needsChoice('movement_type');
            $p->warn('Não foi indicado o tipo (despesa ou receita) e não há regra para o inferir. Escolha o tipo.', 'movement_type');
        }

        if (! array_key_exists('expense_date', $p->fields)) {
            $p->set('expense_date', $now->toDateString(), 'default', $now->format('d/m/Y'), 'Data de hoje (Lisboa): valor por defeito do formulário.');
            $p->warn('Data não indicada: aplicada a de hoje, como no formulário.', 'expense_date', 'info');
        }

        if (! array_key_exists('status', $p->fields)) {
            $p->set('status', self::DEFAULT_STATUS, 'default', Expense::statuses()[self::DEFAULT_STATUS], 'Valor por defeito do formulário; não foi indicado na frase.');
            $p->warn('Estado não indicado: aplicado «' . Expense::statuses()[self::DEFAULT_STATUS] . '», o valor por defeito do formulário. Altere se ainda não foi pago/recebido.', 'status');
        }

        $p->set('vat_rate', self::DEFAULT_VAT_RATE, 'default', self::DEFAULT_VAT_RATE . '%', 'Valor por defeito do formulário; a frase não indica IVA.');

        if ($this->receivedWord && $p->value('movement_type') === 'expense') {
            $p->warn('«Recebido» costuma aplicar-se a receitas, mas o movimento é uma despesa.', 'status');
        }
    }

    private function composeTexts(Proposal $p, TextCursor $c): void
    {
        $tokens = array_values(array_filter(
            $c->words(),
            fn ($w) => ! in_array($w['text'], self::FILLERS, true) && preg_match('/^[^a-z0-9\x01]+$/', $w['text']) !== 1
        ));
        $leftover = trim(implode(' ', array_map(fn ($w) => $c->originalSlice($w['start'], $w['length']), $tokens)));

        if ($leftover !== '') {
            $p->leftover = $leftover;
            $p->warn('Texto não interpretado: «' . $leftover . '». Foi aproveitado como título; edite se preferir.', 'title');
            $p->set('title', $leftover, 'extracted', null, 'Texto não interpretado da frase.');
        } else {
            $p->set('title', $this->suggestedTitle($p), 'inferred', null, 'Título sugerido a partir dos campos reconhecidos; edite se quiser.');
        }

        $p->set('observations', $p->originalText, 'default', null, 'Texto original da frase, guardado nas observações (pode apagar).');
    }

    private function suggestedTitle(Proposal $p): string
    {
        $base = $p->fields['category']['label']
            ?? ($p->fields['movement_type']['label'] ?? null)
            ?? 'Movimento';

        $reference = $p->fields['client_id']['label'] ?? null;
        if ($p->value('legalization_id')) {
            $reference = 'Legalização' . ($reference ? ' ' . $reference : '');
        }

        return $reference ? $base . ' – ' . $reference : $base . ' (inserção rápida)';
    }

    // ── Formatação de valores (em cêntimos inteiros, sem floats) ────────────

    private function decimal(int $cents): string
    {
        return intdiv($cents, 100) . '.' . sprintf('%02d', $cents % 100);
    }

    private function money(int $cents): string
    {
        return number_format(intdiv($cents, 100), 0, ',', '.') . ',' . sprintf('%02d', $cents % 100) . ' €';
    }
}
