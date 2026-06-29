<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values'  => 'array',
        'new_values'  => 'array',
        'created_at'  => 'datetime',
    ];

    // Campos que nunca devem aparecer no log (senhas, tokens, etc.)
    public const HIDDEN_FIELDS = ['password', 'remember_token', 'token', 'api_token'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Helper principal — registar qualquer acção ────────────────────────────
    public static function record(
        string  $action,
        string  $description,
        ?Model  $model      = null,
        ?array  $oldValues  = null,
        ?array  $newValues  = null
    ): void {
        // Filtrar campos sensíveis
        $clean = fn(?array $v) => $v ? array_diff_key($v, array_flip(self::HIDDEN_FIELDS)) : null;

        self::create([
            'user_id'        => auth()->id(),
            'action'         => $action,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id'   => $model?->getKey(),
            'description'    => $description,
            'old_values'     => $clean($oldValues),
            'new_values'     => $clean($newValues),
            'ip_address'     => request()->ip(),
            'user_agent'     => substr(request()->userAgent() ?? '', 0, 255),
        ]);
    }

    // ── Helpers específicos para uso nos observers ────────────────────────────
    public static function recordCreated(Model $model, string $description): void
    {
        self::record('created', $description, $model, null, $model->getAttributes());
    }

    public static function recordUpdated(Model $model, string $description, array $old, array $new): void
    {
        // Só regista os campos que realmente mudaram
        $changed = array_keys(array_diff_assoc($new, $old));
        $filteredOld = array_intersect_key($old, array_flip($changed));
        $filteredNew = array_intersect_key($new, array_flip($changed));

        self::record('updated', $description, $model, $filteredOld, $filteredNew);
    }

    public static function recordDeleted(Model $model, string $description): void
    {
        self::record('deleted', $description, $model, $model->getAttributes(), null);
    }
}
