<?php

namespace App\Console\Commands;

use App\Mail\QuarterlyReportMail;
use App\Services\QuarterlyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendQuarterlyReport extends Command
{
    protected $signature = 'reports:quarterly';
    protected $description = 'Gera e envia o relatório trimestral (executado automaticamente no último dia de cada trimestre)';

    public function handle(QuarterlyReportService $service): int
    {
        Carbon::setLocale('pt_PT');

        // subDay(): o cron dispara no dia 1 do mês a seguir ao fim do trimestre
        // (abril/julho/outubro), não no último dia do trimestre em si - "ontem" é
        // que está sempre dentro do trimestre a reportar.
        $date = now()->subDay()->endOfDay();
        $data = $service->generate($date);

        $this->info("A gerar relatório: {$data['label']}...");

        $dir  = storage_path("app/reports/{$data['year']}");
        $file = "relatorio_trimestral_{$data['year']}_Q{$data['quarter']}.pdf";
        $path = "{$dir}/{$file}";

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        Pdf::loadView('pdf.quarterly-report', $data)
            ->setPaper('a4', 'portrait')
            ->save($path);

        $this->info("PDF guardado: {$path}");

        $email = config('mail.admin_address', env('MAIL_FROM_ADDRESS', 'geral@izzycar.com'));
        Mail::to($email)->send(new QuarterlyReportMail($data, $path));

        $this->info("Relatório enviado para {$email}.");

        return Command::SUCCESS;
    }
}
