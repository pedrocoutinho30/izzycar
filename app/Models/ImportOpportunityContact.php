<?php

namespace App\Models;

use App\Enums\ContactMethod;
use App\Enums\ContactType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registo do histórico de contactos com o vendedor de uma Oportunidade.
 */
class ImportOpportunityContact extends Model
{
    protected $fillable = [
        'import_opportunity_id',
        'contacted_at',
        'method',
        'type',
        'message',
        'user_id',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'method' => ContactMethod::class,
        'type' => ContactType::class,
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(ImportOpportunity::class, 'import_opportunity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
