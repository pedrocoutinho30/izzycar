{{-- 
    Vista: Index de Tarefas (Calendário)
    
    Exibe tarefas em formato de calendário
    Suporta 3 visualizações: mensal, semanal e diária
    Permite criar, editar e visualizar tarefas
--}}

@extends('layouts.admin-v2')

@section('title', 'Tarefas')

@section('content')

<!-- PAGE HEADER -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-check2-square', 'label' => 'Tarefas', 'href' => ''],
    ],
    'title' => 'Tarefas',
    'subtitle' => 'Gestão de tarefas e lembretes',
    'actionHref' => route('admin.tasks.create'),
    'actionLabel' => 'Nova Tarefa'
])

<!-- STATS CARDS -->
@include('components.admin.stats-cards', [
    'stats' => [
        [
            'icon' => 'list-check',
            'title' => 'Total de Tarefas',
            'value' => $stats['total'],
            'color' => 'primary'
        ],
        [
            'icon' => 'clock-history',
            'title' => 'Pendentes',
            'value' => $stats['pendentes'],
            'color' => 'warning'
        ],
        [
            'icon' => 'hourglass-split',
            'title' => 'Em Progresso',
            'value' => $stats['em_progresso'],
            'color' => 'info'
        ],
        [
            'icon' => 'check-circle',
            'title' => 'Concluídas',
            'value' => $stats['concluidas'],
            'color' => 'success'
        ]
    ]
])

<!-- NAVEGAÇÃO DO CALENDÁRIO -->
<div class="modern-card mb-4">
    <div class="modern-card-body">
        <div class="row align-items-center">
            {{-- Botões de navegação --}}
            <div class="col-md-4">
                <div class="btn-group" role="group">
                    {{-- Anterior --}}
                    <a href="{{ route('admin.tasks.index', ['view' => $view, 'date' => $currentDate->copy()->subMonth()->toDateString()]) }}" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-chevron-left"></i> Anterior
                    </a>
                    
                    {{-- Hoje --}}
                    <a href="{{ route('admin.tasks.index', ['view' => $view]) }}" 
                       class="btn btn-outline-secondary">
                        Hoje
                    </a>
                    
                    {{-- Próximo --}}
                    <a href="{{ route('admin.tasks.index', ['view' => $view, 'date' => $currentDate->copy()->addMonth()->toDateString()]) }}" 
                       class="btn btn-outline-secondary">
                        Próximo <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>

            {{-- Título do período --}}
            <div class="col-md-4 text-center">
                <h4 class="mb-0">
                    @if($view === 'month')
                        {{ $currentDate->format('F Y') }}
                    @elseif($view === 'week')
                        {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
                    @else
                        {{ $currentDate->format('d/m/Y') }}
                    @endif
                </h4>
            </div>

            {{-- Botões de visualização --}}
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.tasks.index', ['view' => 'month', 'date' => $currentDate->toDateString()]) }}" 
                       class="btn btn-{{ $view === 'month' ? 'primary' : 'outline-primary' }}">
                        Mensal
                    </a>
                    <a href="{{ route('admin.tasks.index', ['view' => 'week', 'date' => $currentDate->toDateString()]) }}" 
                       class="btn btn-{{ $view === 'week' ? 'primary' : 'outline-primary' }}">
                        Semanal
                    </a>
                    <a href="{{ route('admin.tasks.index', ['view' => 'day', 'date' => $currentDate->toDateString()]) }}" 
                       class="btn btn-{{ $view === 'day' ? 'primary' : 'outline-primary' }}">
                        Diária
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CALENDÁRIO -->
<div class="modern-card">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-calendar3"></i>
            Calendário de Tarefas
        </h5>
    </div>
    <div class="modern-card-body p-0">
        @if($view === 'month')
            {{-- Vista Mensal --}}
            @include('admin.v2.tasks.partials.calendar-month')
        @elseif($view === 'week')
            {{-- Vista Semanal --}}
            @include('admin.v2.tasks.partials.calendar-week')
        @else
            {{-- Vista Diária --}}
            @include('admin.v2.tasks.partials.calendar-day')
        @endif
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Estilos do calendário */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background-color: #dee2e6;
        border: 1px solid #dee2e6;
    }

    .calendar-header {
        background-color: #990000;
        color: white;
        padding: 12px 8px;
        text-align: center;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .calendar-day {
        background-color: white;
        min-height: 120px;
        padding: 8px;
        position: relative;
        transition: background-color 0.2s;
    }

    .calendar-day:hover {
        background-color: #f8f9fa;
    }

    .calendar-day.other-month {
        background-color: #f8f9fa;
        opacity: 0.6;
    }

    .calendar-day.today {
        background-color: #fff3cd;
    }

    .day-number {
        font-weight: 600;
        font-size: 0.875rem;
        margin-bottom: 8px;
        color: #495057;
    }

    .calendar-day.today .day-number {
        background-color: #990000;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .task-item {
        display: block;
        background-color: #e3f2fd;
        border-left: 3px solid #2196f3;
        padding: 6px 8px;
        margin-bottom: 4px;
        border-radius: 3px;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        color: inherit;
        overflow: hidden;
    }

    .task-item:hover {
        transform: translateX(2px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-decoration: none;
    }

    .task-item.status-pendente {
        background-color: #fff3cd;
        border-left-color: #ffc107;
    }

    .task-item.status-em_progresso {
        background-color: #d1ecf1;
        border-left-color: #17a2b8;
    }

    .task-item.status-concluida {
        background-color: #d4edda;
        border-left-color: #28a745;
    }

    .task-item.status-cancelada {
        background-color: #e2e3e5;
        border-left-color: #6c757d;
    }

    .task-title {
        font-weight: 500;
        margin-bottom: 3px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1.3;
        color: #2c3e50;
    }

    .task-meta {
        font-size: 0.7rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .task-meta i {
        font-size: 0.5rem;
        flex-shrink: 0;
    }

    /* Vista semanal e diária */
    .timeline-container {
        overflow-y: auto;
        max-height: 600px;
    }

    .timeline-day {
        border-bottom: 1px solid #dee2e6;
        padding: 16px;
    }

    .timeline-day-header {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 12px;
        color: #495057;
    }

    .timeline-task {
        display: block;
        background-color: white;
        border: 1px solid #dee2e6;
        border-left: 4px solid #2196f3;
        padding: 12px;
        margin-bottom: 8px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        color: inherit;
    }

    .timeline-task:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
        text-decoration: none;
    }
    
    .timeline-task.status-pendente {
        border-left-color: #ffc107;
    }
    
    .timeline-task.status-em_progresso {
        border-left-color: #17a2b8;
    }
    
    .timeline-task.status-concluida {
        border-left-color: #28a745;
    }
    
    .timeline-task.status-cancelada {
        border-left-color: #6c757d;
    }

    /* Vista Semanal - Design Moderno */
    .week-timeline {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        padding: 10px 0;
    }

    .week-day-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .week-day-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }

    .week-day-card.is-today {
        border-color: #990000;
        box-shadow: 0 4px 16px rgba(153, 0, 0, 0.15);
    }

    .week-day-card.is-today .week-day-header {
        background: linear-gradient(135deg, #990000 0%, #cc0000 100%);
        color: white;
    }

    .week-day-card.is-past {
        opacity: 0.85;
    }

    .week-day-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 16px 20px;
        border-bottom: 2px solid #dee2e6;
    }

    .day-name {
        font-weight: 700;
        font-size: 1.1rem;
        color: inherit;
        margin-bottom: 4px;
    }

    .day-date {
        font-size: 0.85rem;
        opacity: 0.9;
        font-weight: 500;
    }

    .day-info {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .week-day-tasks {
        padding: 16px;
        min-height: 100px;
        max-height: 400px;
        overflow-y: auto;
    }

    .week-task-item {
        display: block;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 10px;
        border-left: 4px solid #dee2e6;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        position: relative;
    }

    .week-task-item:hover {
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateX(4px);
        text-decoration: none;
    }

    .week-task-item.status-pendente {
        border-left-color: #ffc107;
        background: #fffbf0;
    }

    .week-task-item.status-pendente:hover {
        background: #fff9e6;
    }

    .week-task-item.status-em_progresso {
        border-left-color: #17a2b8;
        background: #f0f9fb;
    }

    .week-task-item.status-em_progresso:hover {
        background: #e6f7f9;
    }

    .week-task-item.status-concluida {
        border-left-color: #28a745;
        background: #f0f9f2;
        opacity: 0.75;
    }

    .week-task-item.status-concluida:hover {
        background: #e6f7ea;
        opacity: 1;
    }

    .week-task-item.status-cancelada {
        border-left-color: #6c757d;
        background: #f5f5f5;
        opacity: 0.6;
    }

    .task-status-indicator {
        width: 4px;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        border-radius: 8px 0 0 8px;
    }

    .task-title-week {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 6px;
        color: #2c3e50;
        line-height: 1.4;
    }

    .task-description-week {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 8px;
        line-height: 1.5;
    }

    .task-meta-week {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 0.75rem;
        color: #6c757d;
    }

    .meta-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .meta-item i {
        font-size: 0.9em;
    }

    .task-arrow {
        color: #adb5bd;
        margin-left: 8px;
        transition: all 0.2s;
    }

    .week-task-item:hover .task-arrow {
        color: #990000;
        transform: translateX(4px);
    }

    .no-tasks-day {
        text-align: center;
        padding: 32px 16px;
        color: #adb5bd;
        font-size: 0.9rem;
    }

    .no-tasks-day i {
        display: block;
        font-size: 2rem;
        margin-bottom: 8px;
        opacity: 0.5;
    }

    /* Responsivo */
    @media (max-width: 768px) {
        .week-timeline {
            grid-template-columns: 1fr;
        }
        
        .week-day-tasks {
            max-height: 300px;
        }
    }

    .no-tasks {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
</style>
@endpush
