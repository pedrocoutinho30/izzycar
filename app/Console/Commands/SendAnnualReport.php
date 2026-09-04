<?php

namespace App\Console\Commands;

use App\Mail\AnnualReportMail;
use App\Services\AnnualReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAnnualReport extends Command
{
    protected $signature = 'reports:annual';
    protected $description = 'Gera e envia o relatório anual (executado automaticamente a 31 de dezembro)';

    public function handle(AnnualReportService $service): int
    {
        Carbon::setLocale('pt_PT');

        // subDay(): o cron dispara a 1 de janeiro (não a 31 de dezembro), por isso
        // "ontem" é que está sempre dentro do ano a reportar.
        $year = now()->subDay()->year;
        $data = $service->generate($year);

        $this->info("A gerar relatório anual {$year}...");

        $dir  = storage_path("app/reports/{$year}");
        $path = "{$dir}/relatorio_anual_{$year}.pdf";

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        Pdf::loadView('pdf.annual-report', $data)
            ->setPaper('a4', 'portrait')
            ->save($path);

        $this->info("PDF guardado: {$path}");

        $email = config('mail.admin_address', env('MAIL_FROM_ADDRESS', 'geral@izzycar.com'));
        Mail::to($email)->send(new AnnualReportMail($data, $path));

        $this->info("Relatório anual enviado para {$email}.");

        return Command::SUCCESS;
    }
}
