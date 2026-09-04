<?php

namespace App\Console\Commands;

use App\Mail\FollowupReminderMail;
use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendFollowupReminder extends Command
{
    protected $signature = 'leads:followup-reminder';
    protected $description = 'Envia email de lembrete no minuto exato em que cada follow-up está agendado';

    public function handle(): int
    {
        Mail::raw('SendFollowupReminder executado às '.now()->format('d/m/Y H:i:s').'.', function ($message) {
            $message->to('geral@izzycar.pt')->subject('SendFollowupReminder executado');
        });

        $agora = now();
        $agora->addMinutes(60); // Ignorar segundos para não perder follow-ups agendados para o minuto exato
        $limite = $agora->copy()->addMinute();

        $followups = Client::whereNotNull('next_followup_at')
            ->whereBetween('next_followup_at', [$agora, $limite])
            ->orderBy('next_followup_at')
            ->with('owner')
            ->get();
        if ($followups->isEmpty()) {
            return Command::SUCCESS;
        }

        $dataFormatada = $agora->format('d/m/Y H:i');
        $adminEmail = config('mail.admin_address', env('MAIL_FROM_ADDRESS', 'geral@izzycar.com'));

        Mail::to($adminEmail)->send(new FollowupReminderMail($followups, $dataFormatada));

        $this->info("Email enviado para {$adminEmail} com {$followups->count()} follow-up(s) para as {$agora->format('H:i')}.");

        // Notificar também o angariador responsável por cada lead, com apenas
        // os follow-ups que lhe pertencem (uma lead sem angariador não gera
        // este segundo email).
        $followups
            ->filter(fn (Client $lead) => $lead->owner && $lead->owner->email)
            ->groupBy('owner_id')
            ->each(function ($ownerFollowups, $ownerId) use ($dataFormatada) {
                $owner = $ownerFollowups->first()->owner;

                Mail::to($owner->email)->send(new FollowupReminderMail(
                    $ownerFollowups,
                    $dataFormatada,
                    forAngariador: true
                ));

                $this->info("Email enviado para o angariador {$owner->email} com {$ownerFollowups->count()} follow-up(s).");
            });

        return Command::SUCCESS;
    }
}
