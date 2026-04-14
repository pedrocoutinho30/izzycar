{{--
    Partial: Vista Semanal do Calendário
    
    Exibe tarefas de uma semana completa em formato timeline
--}}

<div class="week-timeline">
    @php
        // Gerar array com todos os dias da semana
        $weekDays = [];
        $currentDay = $startDate->copy();
        
        while ($currentDay->lte($endDate)) {
            $weekDays[] = $currentDay->copy();
            $currentDay->addDay();
        }
        
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
    @endphp

    @foreach($weekDays as $day)
        @php
            $dateString = $day->format('Y-m-d');
            $dayTasks = $tasks[$dateString] ?? collect();
            $isToday = $day->isToday();
            $isPast = $day->isPast() && !$isToday;
            
            $diaSemana = $diasSemana[$day->format('l')];
            $mes = $meses[$day->format('F')];
            $diaNumero = $day->format('d');
        @endphp

        <div class="week-day-card {{ $isToday ? 'is-today' : '' }} {{ $isPast ? 'is-past' : '' }}">
            {{-- Cabeçalho do dia --}}
            <div class="week-day-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="day-name">{{ $diaSemana }}</div>
                        <div class="day-date">{{ $diaNumero }} de {{ $mes }}</div>
                    </div>
                    <div class="day-info">
                        @if($isToday)
                            <span class="badge bg-primary">Hoje</span>
                        @endif
                        @if($dayTasks->count() > 0)
                            <span class="badge bg-light text-dark">{{ $dayTasks->count() }} {{ $dayTasks->count() == 1 ? 'tarefa' : 'tarefas' }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Lista de tarefas do dia --}}
            <div class="week-day-tasks">
                @forelse($dayTasks as $task)
                    <a href="{{ route('admin.tasks.show', $task) }}" class="week-task-item status-{{ $task->status }}">
                        <div class="d-flex align-items-start">
                            <div class="task-status-indicator bg-{{ $task->status_color }}"></div>
                            <div class="flex-grow-1">
                                <div class="task-title-week">
                                    <i class="bi bi-circle-fill text-{{ $task->status_color }} me-1" style="font-size: 0.5rem;"></i>
                                    {{ $task->title }}
                                </div>
                                
                                @if($task->description)
                                    <div class="task-description-week">
                                        {{ Str::limit($task->description, 80) }}
                                    </div>
                                @endif
                                
                                <div class="task-meta-week">
                                    {{-- Hora/Prazo --}}
                                    <span class="meta-item">
                                        <i class="bi bi-clock"></i>
                                        {{ $task->due_date->format('H:i') != '00:00' ? $task->due_date->format('H:i') : 'Sem hora' }}
                                    </span>
                                    
                                    {{-- Usuário --}}
                                    @if($task->user || $task->user_name)
                                        <span class="meta-item">
                                            <i class="bi bi-person"></i>
                                            {{ $task->user?->name ?? $task->user_name }}
                                        </span>
                                    @endif
                                    
                                    {{-- Veículo --}}
                                    @if($task->vehicle)
                                        <span class="meta-item">
                                            <i class="bi bi-car-front"></i>
                                            {{ $task->vehicle->brand }} {{ $task->vehicle->model }}
                                        </span>
                                    @endif
                                    
                                    {{-- Lembrete --}}
                                    @if($task->reminder_date && !$task->reminder_sent)
                                        <span class="meta-item text-warning">
                                            <i class="bi bi-bell-fill"></i>
                                            Lembrete ativo
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="task-arrow">
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="no-tasks-day">
                        <i class="bi bi-calendar-check text-muted"></i>
                        <span>Nenhuma tarefa</span>
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
