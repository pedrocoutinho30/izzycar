<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;

/**
 * TaskSeeder
 * 
 * Popula a tabela de tarefas com dados de exemplo
 * Útil para testes e demonstração do sistema
 */
class TaskSeeder extends Seeder
{
    /**
     * Executa o seeder
     */
    public function run(): void
    {
        // Obter primeiro usuário e veículo para associações
        $user = User::first();
        $vehicle = Vehicle::first();

        // Tarefas de exemplo
        $tasks = [
            [
                'title' => 'Preparar documentação para venda',
                'description' => 'Reunir todos os documentos necessários para processar a venda do veículo, incluindo certificado de matrícula, livrete e documentos de inspeção.',
                'due_date' => Carbon::now()->addDays(3),
                'reminder_date' => Carbon::now()->addDays(2),
                'user_id' => $user?->id,
                'vehicle_id' => $vehicle?->id,
                'status' => 'pendente',
            ],
            [
                'title' => 'Agendar inspeção técnica',
                'description' => 'Marcar inspeção técnica periódica para o veículo antes da data de vencimento.',
                'due_date' => Carbon::now()->addDays(7),
                'reminder_date' => Carbon::now()->addDays(5),
                'user_id' => $user?->id,
                'vehicle_id' => $vehicle?->id,
                'status' => 'pendente',
            ],
            [
                'title' => 'Contactar cliente para follow-up',
                'description' => 'Ligar ao cliente João Silva para acompanhamento da proposta enviada na semana passada.',
                'due_date' => Carbon::now()->addDays(1),
                'reminder_date' => Carbon::now(),
                'user_id' => $user?->id,
                'status' => 'em_progresso',
            ],
            [
                'title' => 'Atualizar anúncios online',
                'description' => 'Atualizar fotos e descrições dos veículos nos portais online (Stand Virtual, Autoscout24, etc).',
                'due_date' => Carbon::now()->addDays(5),
                'reminder_date' => Carbon::now()->addDays(4),
                'user_name' => 'Maria Santos',
                'status' => 'pendente',
            ],
            [
                'title' => 'Processar pagamento de fornecedor',
                'description' => 'Efetuar pagamento ao fornecedor de peças pela fatura #2024-123.',
                'due_date' => Carbon::now()->addDays(2),
                'reminder_date' => Carbon::now()->addDay(),
                'user_id' => $user?->id,
                'status' => 'pendente',
            ],
            [
                'title' => 'Revisar contratos de consignação',
                'description' => 'Analisar e renovar contratos de veículos em consignação que vencem este mês.',
                'due_date' => Carbon::now()->addDays(10),
                'reminder_date' => Carbon::now()->addDays(8),
                'user_name' => 'Pedro Costa',
                'status' => 'pendente',
            ],
            [
                'title' => 'Limpar e detalhar veículo para exposição',
                'description' => 'Preparar veículo para exposição no stand: limpeza completa, polimento e verificação de todos os sistemas.',
                'due_date' => Carbon::now()->addDays(4),
                'reminder_date' => Carbon::now()->addDays(3),
                'vehicle_id' => $vehicle?->id,
                'user_id' => $user?->id,
                'status' => 'em_progresso',
            ],
            [
                'title' => 'Enviar relatório mensal de vendas',
                'description' => 'Compilar e enviar relatório mensal de vendas para a direção.',
                'due_date' => Carbon::now()->endOfMonth(),
                'reminder_date' => Carbon::now()->endOfMonth()->subDays(2),
                'user_id' => $user?->id,
                'status' => 'pendente',
            ],
            [
                'title' => 'Tarefa concluída - Teste de histórico',
                'description' => 'Esta é uma tarefa de teste que já foi concluída.',
                'due_date' => Carbon::now()->subDays(5),
                'reminder_date' => Carbon::now()->subDays(7),
                'user_id' => $user?->id,
                'status' => 'concluida',
            ],
            [
                'title' => 'Verificar documentação de importação',
                'description' => 'Confirmar que todos os documentos de importação estão em ordem para o veículo recém-chegado.',
                'due_date' => Carbon::now()->addWeek(),
                'reminder_date' => Carbon::now()->addDays(6),
                'user_name' => 'Ana Rodrigues',
                'vehicle_id' => $vehicle?->id,
                'status' => 'pendente',
            ],
        ];

        // Criar tarefas
        foreach ($tasks as $taskData) {
            Task::create($taskData);
        }

        $this->command->info('✓ Criadas ' . count($tasks) . ' tarefas de exemplo!');
    }
}
