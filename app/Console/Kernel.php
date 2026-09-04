<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // Gerar sitemap diariamente
        $schedule->command('sitemap:generate')->daily();
        
        // Enviar lembretes de tarefas todos os dias às 00:00
        $schedule->command('tasks:send-reminders')->dailyAt('00:00');

        // Enviar email de acompanhamento para legalizações novas — todos os dias às 15:00
        $schedule->command('legalizations:send-tracking-emails')->dailyAt('15:00');

        // Sincronizar reviews do Google Business Profile — de hora a hora
        // $schedule->command('google:sync-reviews')->hourly();

        // Enviar lembrete de follow-up no minuto exato em que foi agendado
        $schedule->command('leads:followup-reminder')->everyMinute();

        // Avisar a administração de comissões de angariadores em atraso — todos os dias às 09:00
        $schedule->command('commissions:overdue-alert')->dailyAt('09:00');

        // Relatório mensal — dia 1 de cada mês (reporta o mês anterior, ver
        // SendMonthlyReport::handle() - usa subDay() por causa disto)
        $schedule->command('reports:monthly')->monthlyOn(1, '01:00');

        // Relatório trimestral — dia 1 de abril/julho/outubro (reporta o trimestre
        // anterior, ver SendQuarterlyReport::handle()). Q4 é coberto pelo anual.
        $schedule->command('reports:quarterly')
            ->dailyAt('01:10')
            ->when(fn () => in_array(today()->month, [4, 7, 10]) && today()->day === 1);

        // Relatório anual — 1 de janeiro (reporta o ano anterior, ver
        // SendAnnualReport::handle())
        $schedule->command('reports:annual')->yearlyOn(1, 1, '01:20');

        // Atualizar pesquisas ativas do Radar de Preços — 3x por dia (não de hora a
        // hora: uma pesquisa com a AutoScout24 nos 8 países da Europa já demora
        // minutos, várias seguidas pode demorar bastante mais)
        $schedule->command('radar:refresh-active')->cron('0 6,14,22 * * *');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
