<?php

namespace App\Console\Commands;

use App\Mail\MonthlyReportMail;
use App\Services\MonthlyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendMonthlyReport extends Command
{
    protected $signature = 'reports:monthly
                            {--month= : Mês a reportar no formato YYYY-MM (default: mês atual)}
                            {--email= : Email de destino (default: admin configurado)}';

    protected $description = 'Gera e envia o relatório mensal em PDF por email';

    public function handle(MonthlyReportService $service): int
    {
        Carbon::setLocale('pt_PT');

        $monthArg = $this->option('month');
        $period   = $monthArg
            ? Carbon::createFromFormat('Y-m', $monthArg)->startOfMonth()
            : now()->startOfMonth();

        $this->info("A gerar relatório para {$period->locale('pt_PT')->translatedFormat('F Y')}...");

        $data = $service->generate($period);

        // Guardar PDF em storage/app/reports/{year}/
        $dir  = storage_path('app/reports/' . $period->year);
        $file = "relatorio_{$period->format('Y_m')}.pdf";
        $path = "{$dir}/{$file}";

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        Pdf::loadView('pdf.monthly-report', array_merge(['period' => $period], $data))
            ->setPaper('a4', 'portrait')
            ->save($path);

        $this->info("PDF guardado: {$path}");

        $email = $this->option('email')
            ?? config('mail.admin_address', env('MAIL_FROM_ADDRESS', 'geral@izzycar.com'));

        Mail::to($email)->send(new MonthlyReportMail($period, $data, $path));

        $this->info("Relatório enviado para {$email}.");

        return Command::SUCCESS;
    }
}
