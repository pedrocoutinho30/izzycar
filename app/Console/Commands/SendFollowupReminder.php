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
        $agora = now();
        $limite = $agora->copy()->addMinute();

        $followups = Client::whereNotNull('next_followup_at')
            ->whereBetween('next_followup_at', [$agora, $limite])
            ->orderBy('next_followup_at')
            ->get();

        if ($followups->isEmpty()) {
            return Command::SUCCESS;
        }

        $adminEmail = config('mail.admin_address', env('MAIL_FROM_ADDRESS', 'geral@izzycar.com'));

        Mail::to($adminEmail)->send(new FollowupReminderMail(
            $followups,
            $agora->format('d/m/Y H:i')
        ));

        $this->info("Email enviado para {$adminEmail} com {$followups->count()} follow-up(s) para as {$agora->format('H:i')}.");

        return Command::SUCCESS;
    }
}
