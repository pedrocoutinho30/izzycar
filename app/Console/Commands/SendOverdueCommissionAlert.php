<?php

namespace App\Console\Commands;

use App\Mail\OverdueCommissionMail;
use App\Models\ConvertedProposal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendOverdueCommissionAlert extends Command
{
    protected $signature = 'commissions:overdue-alert';
    protected $description = 'Avisa a administração por email sobre comissões de angariadores em atraso (>48h após a entrega, sem pagamento)';

    /** Dias até voltar a avisar da mesma comissão (evita o alerta diário repetido). */
    private const REPETIR_APOS_DIAS = 7;

    public function handle(): int
    {
        $overdue = ConvertedProposal::with(['client', 'owner', 'statusHistories'])
            ->whereNotNull('owner_id')
            ->where('comissao_paga', false)
            ->get()
            ->filter(fn (ConvertedProposal $convertedProposal) => $convertedProposal->isCommissionOverdue())
            // Sem esquema novo: a cache regista o último aviso por comissão.
            ->reject(fn (ConvertedProposal $convertedProposal) => Cache::has(self::chave($convertedProposal)))
            ->values();

        if ($overdue->isEmpty()) {
            return Command::SUCCESS;
        }

        $adminEmail = config('mail.admin_address', env('MAIL_FROM_ADDRESS', 'geral@izzycar.pt'));

        Mail::to($adminEmail)->send(new OverdueCommissionMail($overdue));

        // Só marca como avisadas depois de o email sair sem exceção.
        $overdue->each(fn (ConvertedProposal $convertedProposal) => Cache::put(
            self::chave($convertedProposal),
            now()->toDateTimeString(),
            now()->addDays(self::REPETIR_APOS_DIAS)
        ));

        $this->info("Email enviado para {$adminEmail} com {$overdue->count()} comissão(ões) em atraso.");

        return Command::SUCCESS;
    }

    private static function chave(ConvertedProposal $convertedProposal): string
    {
        return 'commission_overdue_alert:' . $convertedProposal->id;
    }
}
