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

        // Relatório mensal — último dia de cada mês às 23:30
        $schedule->command('reports:monthly')
            ->dailyAt('23:30')
            ->when(fn () => today()->day === today()->daysInMonth);

        // Relatório trimestral — último dia de Q1 (Mar), Q2 (Jun), Q3 (Set) às 23:40
        // Q4 é coberto pelo relatório anual
        $schedule->command('reports:quarterly')
            ->dailyAt('23:40')
            ->when(fn () => in_array(today()->month, [3, 6, 9]) && today()->day === today()->daysInMonth);

        // Relatório anual — 31 de dezembro às 23:50
        $schedule->command('reports:annual')
            ->dailyAt('23:50')
            ->when(fn () => today()->month === 12 && today()->day === 31);
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
