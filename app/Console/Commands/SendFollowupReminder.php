<?php

namespace App\Console\Commands;

use App\Mail\FollowupReminderMail;
use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendFollowupReminder extends Command
{
    protected $signature = 'leads:followup-reminder';
    protected $description = 'Envia email diário com os follow-ups agendados para amanhã';

    public function handle(): int
    {
        $amanha = today()->addDay();

        $followups = Client::where('is_lead', true)
            ->whereNotNull('next_followup_at')
            ->whereDate('next_followup_at', $amanha)
            ->orderBy('next_followup_at')
            ->get();

        $adminEmail = config('mail.admin_address', env('MAIL_FROM_ADDRESS', 'geral@izzycar.com'));

        Mail::to($adminEmail)->send(new FollowupReminderMail(
            $followups,
            $amanha->format('d/m/Y')
        ));

        $this->info("Email enviado para {$adminEmail} com {$followups->count()} follow-up(s) para {$amanha->format('d/m/Y')}.");

        return Command::SUCCESS;
    }
}
