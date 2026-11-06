<?php

namespace App\QuickEntry;

/**
 * Resultado de interpretar uma frase. Nunca é um registo de negócio: é só
 * a proposta que o utilizador reve, edita e confirma.
 *
 * fields[nome] = ['value' => mixed, 'label' => ?string, 'source' => extracted|inferred|default|relation, 'note' => ?string]
 * warnings[]   = ['level' => info|warning|error, 'field' => ?string, 'message' => string]
 * unresolved   = campos que o utilizador tem de escolher explicitamente (ambiguidade, não encontrado, contradição)
 * options      = candidatos por campo, [{id, label}]
 */
final class Proposal
{
    public const READY = 'ready';
    public const NOT_IMPLEMENTED = 'not_implemented';
    public const UNRECOGNIZED = 'unrecognized';

    /** @var array<string, array{value: mixed, label: ?string, source: string, note: ?string}> */
    public array $fields = [];

    /** @var list<array{level: string, field: ?string, message: string}> */
    public array $warnings = [];

    /** @var list<string> */
    public array $unresolved = [];

    /** @var array<string, list<array{id: int|string, label: string}>> */
    public array $options = [];

    public ?string $leftover = null;

    public function __construct(
        public string $originalText,
        public string $status,
        public ?string $module = null,
        public ?string $action = null,
        public ?string $commandLabel = null,
        public ?string $confirmLabel = null,
        public ?string $message = null,
    ) {
    }

    public function set(string $field, mixed $value, string $source, ?string $label = null, ?string $note = null): void
    {
        $this->fields[$field] = ['value' => $value, 'label' => $label, 'source' => $source, 'note' => $note];
    }

    public function value(string $field): mixed
    {
        return $this->fields[$field]['value'] ?? null;
    }

    public function warn(string $message, ?string $field = null, string $level = 'warning'): void
    {
        $this->warnings[] = ['level' => $level, 'field' => $field, 'message' => $message];
    }

    public function needsChoice(string $field): void
    {
        if (! in_array($field, $this->unresolved, true)) {
            $this->unresolved[] = $field;
        }
    }

    public function isReady(): bool
    {
        return $this->status === self::READY;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'module' => $this->module,
            'action' => $this->action,
            'command_label' => $this->commandLabel,
            'confirm_label' => $this->confirmLabel,
            'message' => $this->message,
            'original_text' => $this->originalText,
            'fields' => $this->fields,
            'warnings' => $this->warnings,
            'unresolved' => $this->unresolved,
            'options' => $this->options,
            'leftover' => $this->leftover,
        ];
    }
}
