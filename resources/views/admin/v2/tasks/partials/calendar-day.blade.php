{{--
    Partial: Vista Diária do Calendário
    
    Exibe todas as tarefas de um dia específico
--}}

<div class="timeline-container">
    @php
        $dateString = $currentDate->format('Y-m-d');
        $dayTasks = $tasks[$dateString] ?? collect();
        
        // Nomes dos dias da semana em português
        $diasSemana = [
            'Monday' => 'Segunda-feira',
            'Tuesday' => 'Terça-feira',
            'Wednesday' => 'Quarta-feira',
            'Thursday' => 'Quinta-feira',
            'Friday' => 'Sexta-feira',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        
        // Nomes dos meses em português
        $meses = [
            'January' => 'Janeiro',
            'February' => 'Fevereiro',
            'March' => 'Março',
            'April' => 'Abril',
            'May' => 'Maio',
            'June' => 'Junho',
            'July' => 'Julho',
            'August' => 'Agosto',
            'September' => 'Setembro',
            'October' => 'Outubro',
            'November' => 'Novembro',
            'December' => 'Dezembro'
        ];
        
        $diaSemana = $diasSemana[$currentDate->format('l')];
        $mes = $meses[$currentDate->format('F')];
        $diaNumero = $currentDate->format('d');
        $ano = $currentDate->format('Y');
    @endphp

    <div class="timeline-day">
        {{-- Cabeçalho do dia --}}
        <div class="timeline-day-header mb-4">
            {{ $diaSemana }}, {{ $diaNumero }} de {{ $mes }} de {{ $ano }}
            <span class="badge bg-primary ms-2">{{ $dayTasks->count() }} tarefa(s)</span>
        </div>

        {{-- Tarefas do dia agrupadas por estado --}}
        @php
            $tasksByStatus = $dayTasks->groupBy('status');
            $statusOrder = ['em_progresso', 'pendente', 'concluida', 'cancelada'];
        @endphp

        @foreach($statusOrder as $status)
            @if(isset($tasksByStatus[$status]) && $tasksByStatus[$status]->count() > 0)
                <h6 class="text-uppercase text-muted mb-3 mt-4">
                    @switch($status)
                        @case('pendente')
                            <i class="bi bi-clock-history text-warning"></i> Pendentes
                            @break
                        @case('em_progresso')
                            <i class="bi bi-hourglass-split text-info"></i> Em Progresso
                            @break
                        @case('concluida')
                            <i class="bi bi-check-circle text-success"></i> Concluídas
                            @break
                        @case('cancelada')
                            <i class="bi bi-x-circle text-secondary"></i> Canceladas
                            @break
                    @endswitch
                </h6>

                @foreach($tasksByStatus[$status] as $task)
                    <a href="{{ route('admin.tasks.show', $task) }}" 
                       class="timeline-task status-{{ $task->status }}"
                       style="text-decoration: none; color: inherit;">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <h5 class="mb-0">{{ $task->title }}</h5>
                                    <span class="badge bg-{{ $task->status_color }}">{{ $task->status_label }}</span>
                                </div>
                                
                                @if($task->description)
                                    <p class="text-muted mb-3">{{ $task->description }}</p>
                                @endif
                                
                                <div class="d-flex flex-wrap gap-3 small">
                                    {{-- Data de criação --}}
                                    <span class="text-muted">
                                        <i class="bi bi-calendar-plus"></i>
                                        Criada em: {{ $task->created_at->format('d/m/Y H:i') }}
                                    </span>
                                    
                                    {{-- Usuário --}}
                                    @if($task->user || $task->user_name)
                                        <span class="text-muted">
                                            <i class="bi bi-person"></i>
                                            Responsável: {{ $task->user?->name ?? $task->user_name }}
                                        </span>
                                    @endif
                                    
                                    {{-- Veículo --}}
                                    @if($task->vehicle)
                                        <span class="text-muted">
                                            <i class="bi bi-car-front"></i>
                                            Veículo: {{ $task->vehicle->reference }} - {{ $task->vehicle->brand }} {{ $task->vehicle->model }}
                                        </span>
                                    @endif
                                    
                                    {{-- Lembrete --}}
                                    @if($task->reminder_date)
                                        <span class="text-muted">
                                            <i class="bi bi-bell"></i>
                                            Lembrete: {{ $task->reminder_date->format('d/m/Y') }}
                                            @if($task->reminder_sent)
                                                <i class="bi bi-check-circle-fill text-success" title="Lembrete enviado"></i>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="ms-3">
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </div>
                    </a>
                @endforeach
            @endif
        @endforeach

        {{-- Sem tarefas --}}
        @if($dayTasks->isEmpty())
            <div class="no-tasks">
                <i class="bi bi-inbox" style="font-size: 48px;"></i>
                <p class="mt-3">Sem tarefas para este dia</p>
                <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Criar Nova Tarefa
                </a>
            </div>
        @endif
    </div>
</div>
