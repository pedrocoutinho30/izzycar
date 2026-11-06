<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Expense;
use App\Models\QuickEntryInterpretation;
use App\Models\User;
use App\Permissions\PermissionService;
use App\QuickEntry\Proposal;
use App\QuickEntry\QuickEntryDispatcher;
use App\QuickEntry\ReferenceResolver;
use App\Support\DecimalInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Inserção rápida: interpretar não cria nem altera registos de negócio (só
 * o histórico); gravar só acontece em confirm(), que revalida tudo no servidor
 * e usa as regras e o serviço dos movimentos normais.
 */
class QuickEntryService
{
    /** Valor do select para "deixar este campo em branco" numa referência ambígua. */
    public const NONE = '__none__';

    /** Campos de uma proposta de movimento que se podem deixar em branco explicitamente. */
    private const OPTIONAL_CHOICES = ['category', 'client_id', 'legalization_id', 'payment_method'];

    public function __construct(
        private QuickEntryDispatcher $dispatcher,
        private MovementService $movements,
        private ReferenceResolver $resolver,
        private ClientService $clients,
        private PermissionService $permissions,
    ) {
    }

    public function interpret(User $user, string $text): QuickEntryInterpretation
    {
        $proposal = $this->dispatcher->interpret($text, $user);

        return QuickEntryInterpretation::create([
            'user_id' => $user->getKey(),
            'original_text' => $text,
            'module' => $proposal->module,
            'action' => $proposal->action,
            'interpreter_version' => QuickEntryDispatcher::VERSION,
            'status' => QuickEntryInterpretation::INTERPRETED,
            'proposal' => $proposal->toArray(),
        ]);
    }

    /** Listas dos selects da proposta, limitadas ao que o utilizador pode ver. */
    public function formOptions(User $user): array
    {
        return [
            'clients' => $this->resolver->clients($user),
            'legalizations' => $this->resolver->legalizations($user),
            'resolver' => $this->resolver,
        ];
    }

    public function cancel(QuickEntryInterpretation $interpretation): void
    {
        if ($interpretation->status === QuickEntryInterpretation::INTERPRETED) {
            $interpretation->update(['status' => QuickEntryInterpretation::CANCELLED, 'cancelled_at' => now()]);
        }
    }

    /**
     * Valida e grava o movimento da proposta, uma única vez.
     *
     * @param  array<string, mixed>  $input  campos editados pelo utilizador
     * @return array{movement: ?Expense, client: ?Client, record: ?\Illuminate\Database\Eloquent\Model, duplicate: bool}
     *
     * @throws ValidationException
     */
    public function confirm(User $user, QuickEntryInterpretation $interpretation, array $input): array
    {
        return DB::transaction(function () use ($user, $interpretation, $input) {
            // Duplo clique ou pedido repetido: o segundo espera pela linha e vê "confirmada".
            $locked = QuickEntryInterpretation::whereKey($interpretation->getKey())->lockForUpdate()->firstOrFail();

            $isClient = ($locked->proposal['module'] ?? null) === 'cliente';
            $recordClass = $isClient ? Client::class : Expense::class;

            if ($locked->status === QuickEntryInterpretation::CONFIRMED) {
                $record = $recordClass::find($locked->record_id);

                return ['movement' => $isClient ? null : $record, 'client' => $isClient ? $record : null, 'record' => $record, 'duplicate' => true];
            }
            if ($locked->status === QuickEntryInterpretation::CANCELLED) {
                throw ValidationException::withMessages(['interpretation' => 'Esta interpretação foi cancelada. Interprete a frase de novo.']);
            }
            $module = $locked->proposal['module'] ?? null;
            if (($locked->proposal['status'] ?? null) !== Proposal::READY || ! in_array($module, ['movimento', 'cliente'], true)) {
                throw ValidationException::withMessages(['interpretation' => 'Este comando ainda não pode ser confirmado.']);
            }

            if ($isClient) {
                $data = $this->validateClient($user, $input);
                $record = $this->clients->create($data);
            } else {
                $data = $this->validateMovement($user, $locked, $input);
                $record = $this->movements->create($data);
            }

            $locked->update([
                'status' => QuickEntryInterpretation::CONFIRMED,
                'final_data' => $data,
                'record_type' => $recordClass,
                'record_id' => $record->getKey(),
                'confirmed_at' => now(),
            ]);

            return ['movement' => $isClient ? null : $record, 'client' => $isClient ? $record : null, 'record' => $record, 'duplicate' => false];
        });
    }

    /** @return array<string, mixed> dados prontos para o ClientService::create */
    private function validateClient(User $user, array $input): array
    {
        $data = [];
        foreach (['name', 'phone', 'email', 'vat_number', 'language', 'observation'] as $field) {
            $value = $input[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $data[$field] = $value === '' ? null : $value;
        }
        if ($data['phone']) {
            $data['phone'] = preg_replace('/\s+/', '', $data['phone']);
        }

        $rules = array_intersect_key($this->clients->rules(), array_flip(array_keys($data)));
        $rules['phone'] = ['nullable', 'regex:/^\+?\d{9,15}$/'];
        $rules['vat_number'] = ['nullable', 'regex:/^\d{9}$/'];
        $validator = Validator::make($data, $rules, [
            'phone.regex' => 'O telefone tem de ter entre 9 e 15 dígitos (pode começar por +).',
            'vat_number.regex' => 'O NIF tem de ter 9 dígitos.',
        ], ['name' => 'nome', 'phone' => 'telefone', 'vat_number' => 'NIF']);

        $neutral = $this->permissions->scope($user, 'clients', 'view') !== 'all';
        $validator->after(function ($validator) use ($data, $input, $neutral) {
            // Revalida os duplicados no momento da confirmação; o email repetido nunca se força (é único na BD).
            if ($owner = $this->clients->emailOwner($data['email'])) {
                $validator->errors()->add('email', $this->clients->emailTakenMessage($owner, $neutral));
            }
            $confirmed = filter_var($input['confirm_duplicate'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if ($data['phone'] && ($warning = $this->clients->duplicateWarning(['phone' => $data['phone']], $confirmed))) {
                $validator->errors()->add('confirm_duplicate', $this->clients->phoneWarningMessage($warning, $neutral) . ' Marque a confirmação para criar mesmo assim.');
            }
        });

        return $validator->validate();
    }

    /** @return array<string, mixed> dados prontos para o MovementService::create */
    private function validateMovement(User $user, QuickEntryInterpretation $interpretation, array $input): array
    {
        $fields = ['movement_type', 'category', 'amount', 'client_id', 'legalization_id', 'status', 'payment_method', 'expense_date', 'title', 'observations', 'vat_rate'];

        $raw = [];
        $data = [];
        foreach ($fields as $field) {
            $value = $input[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $raw[$field] = $value;
            $data[$field] = $value === '' || $value === self::NONE ? null : $value;
        }
        $data['amount'] = DecimalInput::normalize(is_scalar($data['amount']) ? (string) $data['amount'] : null);
        // O IVA vem de uma lista fechada (como no formulário), nunca de texto livre.
        $data['vat_rate'] = is_scalar($data['vat_rate']) ? str_replace(',', '.', (string) $data['vat_rate']) : null;

        $rules = $this->movements->rules(false);
        $rules['amount'] = ['required', 'regex:/^\d{1,9}(\.\d{1,2})?$/', 'gt:0'];
        $rules['movement_type'] = 'required|in:' . implode(',', array_keys(Expense::movementTypes()));
        $rules['category'] = 'nullable|in:' . implode(',', array_keys(Expense::categories()));
        $rules['payment_method'] = 'nullable|in:' . implode(',', array_keys(Expense::paymentMethods()));
        $rules['status'] = 'required|in:' . implode(',', array_keys(Expense::statuses()));
        $rules['vat_rate'] = 'required|in:0,6,13,19,23,25';
        $rules['expense_date'] = 'required|date_format:Y-m-d';
        unset($rules['v3_vehicle_id'], $rules['partner_id']);

        $validator = Validator::make($data, $rules, [
            'amount.regex' => 'O valor tem de ter no máximo 2 casas decimais (ex.: 922,50).',
            'amount.gt' => 'O valor tem de ser superior a zero.',
            'expense_date.date_format' => 'A data é inválida.',
        ], [
            'movement_type' => 'tipo', 'amount' => 'valor', 'title' => 'título', 'expense_date' => 'data',
            'status' => 'estado', 'payment_method' => 'método de pagamento', 'category' => 'categoria',
            'client_id' => 'cliente', 'legalization_id' => 'legalização', 'vat_rate' => 'IVA',
        ]);

        $validator->after(function ($validator) use ($user, $interpretation, $data, $raw) {
            // Campos que a interpretação deixou por decidir: têm de ser escolhidos (ou deixados em branco de propósito).
            $labels = ['category' => 'a categoria', 'client_id' => 'o cliente', 'legalization_id' => 'a legalização', 'payment_method' => 'o método de pagamento', 'status' => 'o estado', 'movement_type' => 'o tipo'];
            foreach ($interpretation->proposal['unresolved'] ?? [] as $field) {
                $empty = ($raw[$field] ?? '') === '' || (($raw[$field] ?? null) === self::NONE && ! in_array($field, self::OPTIONAL_CHOICES, true));
                if ($empty && isset($labels[$field])) {
                    $validator->errors()->add($field, 'Escolha ' . $labels[$field] . ' (ou deixe em branco de propósito).');
                }
            }

            if ($data['client_id'] && ! $this->resolver->clients($user)->contains('id', (int) $data['client_id'])) {
                $validator->errors()->add('client_id', 'Cliente inválido ou sem acesso.');
            }

            $legalization = null;
            if ($data['legalization_id']) {
                $legalization = $this->resolver->legalizations($user)->firstWhere('id', (int) $data['legalization_id']);
                if (! $legalization) {
                    $validator->errors()->add('legalization_id', 'Legalização inválida ou sem acesso.');
                }
            }

            if ($legalization && $legalization->client_id && $data['client_id'] && (int) $legalization->client_id !== (int) $data['client_id']) {
                $validator->errors()->add('legalization_id', 'O cliente escolhido não é o da legalização («' . ($legalization->client?->name ?? '?') . '»). Corrija um dos dois.');
            }
        });

        return $validator->validate();
    }
}
