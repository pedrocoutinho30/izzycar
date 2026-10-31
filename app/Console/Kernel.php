<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

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
        $this->protegido($schedule->command('sitemap:generate')->daily(), 30);
        
        // Enviar lembretes de tarefas todos os dias às 00:00
        $this->protegido($schedule->command('tasks:send-reminders')->dailyAt('00:00'), 60);

        // Enviar email de acompanhamento para legalizações novas — todos os dias às 15:00
        $this->protegido($schedule->command('legalizations:send-tracking-emails')->dailyAt('15:00'), 60);

        // Sincronizar reviews do Google Business Profile — de hora a hora
        // $schedule->command('google:sync-reviews')->hourly();

        // Enviar lembrete de follow-up no minuto exato em que foi agendado
        $this->protegido($schedule->command('leads:followup-reminder')->everyMinute(), 10);

        // Avisar a administração de comissões de angariadores em atraso — todos os dias às 09:00
        $this->protegido($schedule->command('commissions:overdue-alert')->dailyAt('09:00'), 60);

        // Relatório mensal — dia 1 de cada mês (reporta o mês anterior, ver
        // SendMonthlyReport::handle() - usa subDay() por causa disto)
        $this->protegido($schedule->command('reports:monthly')->monthlyOn(1, '01:00'), 60);

        // Relatório trimestral — dia 1 de abril/julho/outubro (reporta o trimestre
        // anterior, ver SendQuarterlyReport::handle()). Q4 é coberto pelo anual.
        $this->protegido(
            $schedule->command('reports:quarterly')
                ->dailyAt('01:10')
                ->when(fn () => in_array(today()->month, [4, 7, 10]) && today()->day === 1),
            60
        );

        // Relatório anual — 1 de janeiro (reporta o ano anterior, ver
        // SendAnnualReport::handle())
        $this->protegido($schedule->command('reports:annual')->yearlyOn(1, 1, '01:20'), 60);

        // Atualizar pesquisas ativas do Radar de Preços — 3x por dia (não de hora a
        // hora: uma pesquisa com a AutoScout24 nos 8 países da Europa já demora
        // minutos, várias seguidas pode demorar bastante mais)
        $this->protegido($schedule->command('radar:refresh-active')->cron('0 6,14,22 * * *'), 60);

        // Classificar (via IA) o multiplicador de preço de trim dos anúncios
        // novos que o refresh acima acabou de trazer - 30min depois, para dar
        // tempo ao scrape de terminar.
        $this->protegido($schedule->command('radar:classify-trims')->cron('30 6,14,22 * * *'), 60);
    }

    /**
     * Protege uma tarefa agendada: não sobrepõe execuções (a máscara expira ao
     * fim de $expiraMinutos, para um processo morto não bloquear para sempre) e
     * regista no log quando falha, para não passar despercebido.
     *
     * Não usa onOneServer(): exige cache partilhada (redis/database/memcached)
     * e o driver por omissão é "file". Acrescentar se houver mais de um nó.
     */
    private function protegido(Event $evento, int $expiraMinutos): Event
    {
        return $evento
            ->withoutOverlapping($expiraMinutos)
            ->onFailure(function () use ($evento) {
                Log::error('Tarefa agendada falhou: ' . ($evento->command ?? $evento->description ?? 'desconhecida'));
            });
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
