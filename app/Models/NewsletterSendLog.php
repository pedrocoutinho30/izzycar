<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSendLog extends Model
{
    protected $fillable = [
        'newsletter_id',
        'client_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
