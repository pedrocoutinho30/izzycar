<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'template',
        'subtitle',
        'text',
        'sent_at',
    ];

    const TEMPLATES = [
        'standard'           => 'Newsletter com Ofertas',
        'custos-importacao'  => 'Guia de Custos de Importação',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function offers()
    {
        return $this->hasMany(NewsletterOffer::class);
    }

    public function sendLogs()
    {
        return $this->hasMany(NewsletterSendLog::class);
    }

    public function sentClients()
    {
        return $this->belongsToMany(Client::class, 'newsletter_send_logs')
                    ->withPivot('sent_at')
                    ->withTimestamps();
    }
}
