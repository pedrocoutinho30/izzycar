<?php

namespace App\Console\Commands;

use App\Mail\FollowupReminderMail;
use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendFollowupReminder extends Command
{
    protected $signature = 'leads:followup-reminder';
    protected $description = 'Envia email de lembrete no minuto exato em que cada follow-up está agendado';

    /** Tempo (min) que a chave de deduplicação fica na cache; bem acima da janela de 1 min. */
    private const DEDUP_TTL_MINUTES = 180;

    public function handle(): int
    {
        // Janela semiaberta [início do minuto, início do minuto seguinte): um
        // follow-up às HH:MM:00 só cai numa execução (o whereBetween era
        // inclusivo e duplicava) e o lembrete sai no minuto exato, não 60 min antes.
        $inicio = now()->startOfMinute();
        $fim = $inicio->copy()->addMinute();

        $candidatos = Client::whereNotNull('next_followup_at')
            ->where('next_followup_at', '>=', $inicio)
            ->where('next_followup_at', '<', $fim)
            ->orderBy('next_followup_at')
            ->with('owner')
            ->get();

        // Deduplicação sem esquema novo: chave de cache por cliente + instante do
        // follow-up (Cache::add é atómico). Se o follow-up for reagendado, a
        // chave muda e volta a avisar. Alternativa mais robusta: coluna
        // followup_reminded_at (exige migration).
        $followups = $candidatos->filter(fn (Client $lead) => Cache::add(
            $this->chaveDeduplicacao($lead),
            true,
            now()->addMinutes(self::DEDUP_TTL_MINUTES)
        ))->values();

        if ($followups->isEmpty()) {
            return Command::SUCCESS;
        }

        $dataFormatada = $inicio->format('d/m/Y H:i');
        $adminEmail = config('mail.admin_address', env('MAIL_FROM_ADDRESS', 'geral@izzycar.com'));

        try {
            Mail::to($adminEmail)->send(new FollowupReminderMail($followups, $dataFormatada));
            $this->info("Email enviado para {$adminEmail} com {$followups->count()} follow-up(s) para as {$inicio->format('H:i')}.");
        } catch (\Throwable $e) {
            // Não impedir o email ao angariador por falha no email do admin.
            report($e);
            $this->error("Falha a enviar o email para {$adminEmail}: {$e->getMessage()}");
        }

        // Notificar também o angariador responsável por cada lead, com apenas
        // os follow-ups que lhe pertencem (uma lead sem angariador não gera
        // este segundo email).
        $followups
            ->filter(fn (Client $lead) => $lead->owner && $lead->owner->email)
            ->groupBy('owner_id')
            ->each(function ($ownerFollowups) use ($dataFormatada) {
                $owner = $ownerFollowups->first()->owner;

                try {
                    Mail::to($owner->email)->send(new FollowupReminderMail(
                        $ownerFollowups,
                        $dataFormatada,
                        forAngariador: true
                    ));

                    $this->info("Email enviado para o angariador {$owner->email} com {$ownerFollowups->count()} follow-up(s).");
                } catch (\Throwable $e) {
                    report($e);
                    $this->error("Falha a enviar o email para o angariador {$owner->email}: {$e->getMessage()}");
                }
            });

        return Command::SUCCESS;
    }

    private function chaveDeduplicacao(Client $lead): string
    {
        return 'followup_reminder:' . $lead->id . ':' . $lead->next_followup_at->timestamp;
    }
}
