{{--
    Partial: Vista Mensal do Calendário
    
    Exibe um calendário mensal completo com tarefas
--}}

<div class="calendar-grid">
    {{-- Cabeçalho dos dias da semana --}}
    <div class="calendar-header">Dom</div>
    <div class="calendar-header">Seg</div>
    <div class="calendar-header">Ter</div>
    <div class="calendar-header">Qua</div>
    <div class="calendar-header">Qui</div>
    <div class="calendar-header">Sex</div>
    <div class="calendar-header">Sáb</div>

    @php
        // Calcular primeiro dia do mês e total de dias
        $firstDayOfMonth = $currentDate->copy()->startOfMonth();
        $lastDayOfMonth = $currentDate->copy()->endOfMonth();
        $daysInMonth = $currentDate->daysInMonth;
        
        // Calcular dias vazios no início (antes do primeiro dia do mês)
        $startDayOfWeek = $firstDayOfMonth->dayOfWeek; // 0 = Domingo, 6 = Sábado
        
        // Dias do mês anterior para preencher
        $previousMonth = $currentDate->copy()->subMonth();
        $daysInPreviousMonth = $previousMonth->daysInMonth;
        
        // Array de todos os dias a mostrar
        $calendarDays = [];
        
        // Adicionar dias do mês anterior
        for ($i = $startDayOfWeek - 1; $i >= 0; $i--) {
            $calendarDays[] = [
                'date' => $previousMonth->copy()->day($daysInPreviousMonth - $i),
                'isCurrentMonth' => false
            ];
        }
        
        // Adicionar dias do mês atual
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $calendarDays[] = [
                'date' => $currentDate->copy()->day($day),
                'isCurrentMonth' => true
            ];
        }
        
        // Adicionar dias do próximo mês para completar a grade
        $remainingDays = 42 - count($calendarDays); // 6 semanas * 7 dias
        $nextMonth = $currentDate->copy()->addMonth();
        for ($day = 1; $day <= $remainingDays; $day++) {
            $calendarDays[] = [
                'date' => $nextMonth->copy()->day($day),
                'isCurrentMonth' => false
            ];
        }
    @endphp

    {{-- Renderizar cada dia --}}
    @foreach($calendarDays as $calendarDay)
        @php
            $date = $calendarDay['date'];
            $dateString = $date->format('Y-m-d');
            $isToday = $date->isToday();
            $dayTasks = $tasks[$dateString] ?? collect();
        @endphp

        <div class="calendar-day {{ !$calendarDay['isCurrentMonth'] ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
            {{-- Número do dia --}}
            <div class="day-number">
                {{ $date->day }}
            </div>

            {{-- Tarefas do dia --}}
            @foreach($dayTasks->take(3) as $task)
                <a href="{{ route('admin.tasks.show', $task) }}" 
                   class="task-item status-{{ $task->status }}"
                   title="{{ $task->title }}">
                    <div class="task-title">{{ $task->title }}</div>
                    <div class="task-meta">
                        <i class="bi bi-circle-fill text-{{ $task->status_color }}"></i>
                        {{ $task->status_label }}
                    </div>
                </a>
            @endforeach

            {{-- Indicador de mais tarefas --}}
            @if($dayTasks->count() > 3)
                <div class="text-muted small mt-1">
                    +{{ $dayTasks->count() - 3 }} mais
                </div>
            @endif
        </div>
    @endforeach
</div>
