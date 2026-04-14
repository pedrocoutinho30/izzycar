{{--
    Vista: Detalhes da Tarefa
    
    Exibe todas as informações de uma tarefa específica
--}}

@extends('layouts.admin-v2')

@section('title', 'Detalhes da Tarefa')

@section('content')

<!-- PAGE HEADER -->
@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-check2-square', 'label' => 'Tarefas', 'href' => route('admin.tasks.index')],
        ['icon' => '', 'label' => 'Detalhes']
    ],
    'title' => $task->title,
    'subtitle' => 'Informações completas da tarefa',
    'actionHref' => route('admin.tasks.edit', $task),
    'actionLabel' => 'Editar Tarefa'
])

<div class="row g-4">
    {{-- COLUNA PRINCIPAL --}}
    <div class="col-lg-8">
        {{-- INFORMAÇÕES BÁSICAS --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-info-circle"></i>
                    Informações da Tarefa
                </h5>
                <span class="badge bg-{{ $task->status_color }}" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                    {{ $task->status_label }}
                </span>
            </div>

            <div class="modern-card-body">
                {{-- Título --}}
                <div class="mb-4">
                    <h3 class="mb-2">{{ $task->title }}</h3>
                    @if($task->description)
                        <p class="text-muted mb-0">{{ $task->description }}</p>
                    @endif
                </div>

                {{-- Informações em Grid --}}
                <div class="row g-4">
                    {{-- Data Limite --}}
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-danger bg-opacity-10">
                                <i class="bi bi-calendar-event text-danger fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Data Limite</small>
                                <strong class="fs-5">{{ $task->due_date->format('d/m/Y') }}</strong>
                                <br>
                                <small class="text-muted">{{ $task->due_date->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- Data de Criação --}}
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-primary bg-opacity-10">
                                <i class="bi bi-calendar-plus text-primary fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Criada em</small>
                                <strong class="fs-5">{{ $task->created_at->format('d/m/Y') }}</strong>
                                <br>
                                <small class="text-muted">{{ $task->created_at->format('H:i') }}</small>
                            </div>
                        </div>
                    </div>

                    {{-- Lembrete --}}
                    @if($task->reminder_date)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="icon-box bg-warning bg-opacity-10">
                                    <i class="bi bi-bell text-warning fs-4"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Lembrete</small>
                                    <strong class="fs-5">{{ $task->reminder_date->format('d/m/Y') }}</strong>
                                    <br>
                                    @if($task->reminder_sent)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Enviado
                                        </span>
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-clock"></i> Pendente
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Última Atualização --}}
                    <div class="col-md-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-info bg-opacity-10">
                                <i class="bi bi-arrow-clockwise text-info fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Última Atualização</small>
                                <strong class="fs-5">{{ $task->updated_at->format('d/m/Y') }}</strong>
                                <br>
                                <small class="text-muted">{{ $task->updated_at->format('H:i') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ASSOCIAÇÕES --}}
        @if($task->user || $task->user_name || $task->vehicle)
            <div class="modern-card mt-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-link-45deg"></i>
                        Associações
                    </h5>
                </div>

                <div class="modern-card-body">
                    <div class="row g-4">
                        {{-- Responsável --}}
                        @if($task->user || $task->user_name)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-box bg-secondary bg-opacity-10">
                                        <i class="bi bi-person text-secondary fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Responsável</small>
                                        <strong class="fs-5">{{ $task->user?->name ?? $task->user_name }}</strong>
                                        @if($task->user)
                                            <br>
                                            <small class="text-muted">{{ $task->user->email }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Veículo --}}
                        @if($task->vehicle)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-box bg-dark bg-opacity-10">
                                        <i class="bi bi-car-front text-dark fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Veículo</small>
                                        <strong class="fs-5">{{ $task->vehicle->brand }} {{ $task->vehicle->model }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            Ref: {{ $task->vehicle->reference }} | Ano: {{ $task->vehicle->year }}
                                        </small>
                                        <br>
                                        <a href="{{ route('admin.v2.vehicles.show', $task->vehicle) }}" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="bi bi-eye"></i> Ver Veículo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- COLUNA LATERAL --}}
    <div class="col-lg-4">
        {{-- AÇÕES RÁPIDAS --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-lightning"></i>
                    Ações Rápidas
                </h5>
            </div>

            <div class="d-grid gap-2 p-3">
                {{-- Alterar Status --}}
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-circle-fill"></i>
                        Alterar Status
                    </button>
                    <ul class="dropdown-menu w-100">
                        @foreach(['pendente' => 'Pendente', 'em_progresso' => 'Em Progresso', 'concluida' => 'Concluída', 'cancelada' => 'Cancelada'] as $statusValue => $statusLabel)
                            @if($task->status !== $statusValue)
                                <li>
                                    <form action="{{ route('admin.tasks.update-status', $task) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $statusValue }}">
                                        <button type="submit" class="dropdown-item">
                                            {{ $statusLabel }}
                                        </button>
                                    </form>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                {{-- Editar --}}
                <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i>
                    Editar Tarefa
                </a>

                {{-- Voltar --}}
                <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Voltar ao Calendário
                </a>

                <hr>

                {{-- Eliminar --}}
                <button type="button" 
                        class="btn btn-outline-danger" 
                        onclick="if(confirm('Tem certeza que deseja eliminar esta tarefa?')) { document.getElementById('delete-form').submit(); }">
                    <i class="bi bi-trash"></i>
                    Eliminar Tarefa
                </button>
            </div>
        </div>

        {{-- INFORMAÇÕES ADICIONAIS --}}
        <div class="modern-card mt-4">
            <div class="modern-card-header">
                <h5 class="modern-card-title">
                    <i class="bi bi-info-circle"></i>
                    Status da Tarefa
                </h5>
            </div>

            <div class="list-group list-group-flush">
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Estado Atual</span>
                        <span class="badge bg-{{ $task->status_color }}">{{ $task->status_label }}</span>
                    </div>
                </div>

                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Dias até o vencimento</span>
                        <span class="badge bg-{{ $task->due_date->isPast() ? 'danger' : 'info' }}">
                            {{ abs($task->due_date->diffInDays(now())) }} dias
                        </span>
                    </div>
                </div>

                @if($task->due_date->isPast() && $task->status !== 'concluida')
                    <div class="list-group-item">
                        <div class="alert alert-danger mb-0">
                            <i class="bi bi-exclamation-triangle"></i>
                            Tarefa atrasada!
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Formulário de Eliminação --}}
<form id="delete-form" action="{{ route('admin.tasks.destroy', $task->id) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('styles')
<style>
    /* Caixa de ícone */
    .icon-box {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Correção de badges */
    .modern-card-header .badge {
        font-weight: 600;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }
    
    .list-group-item .badge {
        font-size: 0.875rem;
        padding: 0.35rem 0.75rem;
        font-weight: 600;
    }
</style>
@endpush
