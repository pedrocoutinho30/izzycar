<?php

namespace App\Console\Commands;

use App\Mail\LegalizationTrackingMail;
use App\Models\Legalization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * SendLegalizationTrackingEmails
 *
 * Comando para enviar o email de acompanhamento das legalizações novas
 * Executa diariamente às 15:00
 * Envia email para legalizações com email_enviado = false
 */
class SendLegalizationTrackingEmails extends Command
{
    protected $signature = 'legalizations:send-tracking-emails';

    protected $description = 'Envia o email de acompanhamento para legalizações novas ainda sem email enviado';

    public function handle()
    {
        $this->info('A verificar legalizações novas...');

        $legalizations = Legalization::where('email_enviado', false)
            ->with('client')
            ->get();

        if ($legalizations->isEmpty()) {
            $this->info('Nenhuma legalização nova por notificar.');
            return 0;
        }

        $this->info("Encontradas {$legalizations->count()} legalização(ões) por notificar.");

        $sentCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($legalizations as $legalization) {
            $client = $legalization->client;

            if (!$client || !$client->email) {
                $this->warn("- Legalização #{$legalization->id} sem cliente/email associado. Ignorada por agora.");
                $skippedCount++;
                continue;
            }

            try {
                Mail::to($client->email)->send(new LegalizationTrackingMail(
                    $legalization,
                    $client->name,
                    $legalization->trackingUrl()
                ));

                $legalization->forceFill(['email_enviado' => true])->save();

                $this->info("✓ Email enviado para {$client->name} ({$client->email}) — legalização #{$legalization->id}");
                $sentCount++;
            } catch (\Throwable $e) {
                $this->error("✗ Erro ao enviar email da legalização #{$legalization->id}: {$e->getMessage()}");
                $errorCount++;
            }
        }

        $this->info("\n" . str_repeat('=', 50));
        $this->info('Resumo do envio de emails de acompanhamento:');
        $this->info("Enviados com sucesso: {$sentCount}");
        $this->info("Ignorados (sem cliente/email): {$skippedCount}");
        $this->info("Erros: {$errorCount}");
        $this->info(str_repeat('=', 50));

        return 0;
    }
}
