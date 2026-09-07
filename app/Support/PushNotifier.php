<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * Ponto único para disparar notificações push para a equipa do BO (leads,
 * formulários, simulações, tarefas, ...). Falha em silêncio se o Web Push
 * não estiver configurado ou o envio falhar — nunca deve interromper o
 * pedido que a despoletou (ex.: submissão de um formulário público).
 */
class PushNotifier
{
    /**
     * Notifica todos os utilizadores com acesso de gestão (admin, gestor,
     * cms) — mesmo público que já vê os badges/toasts de "nova lead" no BO.
     */
    public static function notifyStaff(Notification $notification): void
    {
        try {
            User::role(['admin', 'gestor', 'cms'])->get()->each->notify($notification);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Notifica um utilizador específico (ex.: a quem uma tarefa foi
     * atribuída). Sem utilizador definido, cai para a equipa toda.
     */
    public static function notifyUserOrStaff(?User $user, Notification $notification): void
    {
        if (!$user) {
            static::notifyStaff($notification);
            return;
        }

        try {
            $user->notify($notification);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
