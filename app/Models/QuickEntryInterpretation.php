<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Histórico da inserção rápida (ver a migration). Contém texto livre e nomes
 * de clientes: aceder só com a permissão "quick-entry-history.view".
 */
class QuickEntryInterpretation extends Model
{
    public const INTERPRETED = 'interpretada';
    public const CONFIRMED = 'confirmada';
    public const CANCELLED = 'cancelada';

    protected $fillable = [
        'user_id', 'original_text', 'module', 'action', 'interpreter_version', 'status',
        'proposal', 'final_data', 'record_type', 'record_id', 'confirmed_at', 'cancelled_at',
    ];

    protected $casts = [
        'proposal' => 'array',
        'final_data' => 'array',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
