<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'type',
        'title',
        'body',
        'icon',
        'color',
    ];

    // ── Tipos disponíveis ─────────────────────────────────────────────────────
    // system  → evento automático gerado pelo sistema (não editável)
    // note    → nota interna livre do comercial
    // call    → registo de chamada telefónica
    // email   → registo de email enviado/recebido
    // meeting → registo de reunião ou visita presencial
    public const TYPES = [
        'system'    => ['label' => 'Sistema',   'icon' => 'bi-gear-fill',          'color' => 'secondary'],
        'note'      => ['label' => 'Nota',      'icon' => 'bi-sticky-fill',        'color' => 'warning'],
        'call'      => ['label' => 'Chamada',   'icon' => 'bi-telephone-fill',     'color' => 'info'],
        'email'     => ['label' => 'Email',     'icon' => 'bi-envelope-fill',      'color' => 'primary'],
        'whatsapp'  => ['label' => 'WhatsApp',  'icon' => 'bi-whatsapp',           'color' => 'success'],
        'facebook'  => ['label' => 'Facebook',  'icon' => 'bi-facebook',           'color' => 'info'],
        'meeting'   => ['label' => 'Reunião',   'icon' => 'bi-people-fill',        'color' => 'success'],
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Regista evento automático do sistema (sem utilizador associado)
    public static function log(int $clientId, string $title, string $body = '', string $icon = 'bi-circle-fill', string $color = 'secondary'): self
    {
        return self::create([
            'client_id' => $clientId,
            'user_id'   => null,
            'type'      => 'system',
            'title'     => $title,
            'body'      => $body,
            'icon'      => $icon,
            'color'     => $color,
        ]);
    }

    // Regista actividade manual com o utilizador autenticado
    public static function logManual(int $clientId, string $type, string $title, string $body = ''): self
    {
        $config = self::TYPES[$type] ?? self::TYPES['note'];

        return self::create([
            'client_id' => $clientId,
            'user_id'   => auth()->id(),
            'type'      => $type,
            'title'     => $title,
            'body'      => $body,
            'icon'      => $config['icon'],
            'color'     => $config['color'],
        ]);
    }
}
