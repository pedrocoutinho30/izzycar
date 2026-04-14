<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * Campos que podem ser preenchidos em massa
     */
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'reminder_date',
        'user_id',
        'user_name',
        'vehicle_id',
        'status',
        'reminder_sent',
    ];

    /**
     * Campos que devem ser tratados como datas
     */
    protected $casts = [
        'due_date' => 'date',
        'reminder_date' => 'date',
        'reminder_sent' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relação: Tarefa pertence a um usuário (opcional)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação: Tarefa pertence a um veículo (opcional)
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Scope: Tarefas pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pendente');
    }

    /**
     * Scope: Tarefas concluídas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'concluida');
    }

    /**
     * Scope: Tarefas que precisam de lembrete hoje
     */
    public function scopeNeedsReminderToday($query)
    {
        return $query->where('reminder_date', now()->toDateString())
                     ->where('reminder_sent', false)
                     ->whereIn('status', ['pendente', 'em_progresso']);
    }

    /**
     * Retorna a cor do badge baseado no status
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pendente' => 'warning',
            'em_progresso' => 'info',
            'concluida' => 'success',
            'cancelada' => 'secondary',
            default => 'primary',
        };
    }

    /**
     * Retorna o label do status
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pendente' => 'Pendente',
            'em_progresso' => 'Em Progresso',
            'concluida' => 'Concluída',
            'cancelada' => 'Cancelada',
            default => 'Desconhecido',
        };
    }
}
