<?php

namespace App\Console\Commands;

use App\Mail\OverdueCommissionMail;
use App\Models\ConvertedProposal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOverdueCommissionAlert extends Command
{
    protected $signature = 'commissions:overdue-alert';
    protected $description = 'Avisa a administração por email sobre comissões de angariadores em atraso (>48h após a entrega, sem pagamento)';

    public function handle(): int
    {
        $overdue = ConvertedProposal::with(['client', 'owner', 'statusHistories'])
            ->whereNotNull('owner_id')
            ->where('comissao_paga', false)
            ->get()
            ->filter(fn (ConvertedProposal $convertedProposal) => $convertedProposal->isCommissionOverdue())
            ->values();

        if ($overdue->isEmpty()) {
            return Command::SUCCESS;
        }

        $adminEmail = config('mail.admin_address', env('MAIL_FROM_ADDRESS', 'geral@izzycar.pt'));

        Mail::to($adminEmail)->send(new OverdueCommissionMail($overdue));

        $this->info("Email enviado para {$adminEmail} com {$overdue->count()} comissão(ões) em atraso.");

        return Command::SUCCESS;
    }
}
