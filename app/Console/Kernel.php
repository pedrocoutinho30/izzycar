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

        // Sincronizar reviews do Google Business Profile — de hora a hora
        // $schedule->command('google:sync-reviews')->hourly();

        // Enviar lembrete de follow-up no minuto exato em que foi agendado
        $schedule->command('leads:followup-reminder')->everyMinute();

        // Relatório mensal — último dia do mês às 08:00
        $schedule->command('reports:monthly')
            ->dailyAt('23:30')
            ->when(fn () => today()->day === today()->daysInMonth);
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
