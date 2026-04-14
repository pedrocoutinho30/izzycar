{{--
    Vista: Formulário de Tarefas
    
    Permite criar e editar tarefas
    Suporta associação com usuários e veículos
--}}

@extends('layouts.admin-v2')

@section('title', isset($task) ? 'Editar Tarefa' : 'Nova Tarefa')

@section('content')

<!-- PAGE HEADER -->
@php
    $action = isset($task) ? 'Editar' : 'Criar';
@endphp

@include('components.admin.page-header', [
    'breadcrumbs' => [
        ['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
        ['icon' => 'bi bi-check2-square', 'label' => 'Tarefas', 'href' => route('admin.tasks.index')],
        ['icon' => '', 'label' => $action]
    ],
    'title' => $action . ' Tarefa',
    'subtitle' => isset($task) ? 'Editar informações da tarefa' : 'Criar nova tarefa',
    'actionHref' => '',
    'actionLabel' => ''
])

<!-- FORMULÁRIO -->
<form action="{{ isset($task) ? route('admin.tasks.update', $task->id) : route('admin.tasks.store') }}" method="POST">
    @csrf
    @if(isset($task))
        @method('PUT')
    @endif

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
                </div>

                <div class="row g-3">
                    {{-- Título --}}
                    <div class="col-12">
                        <label class="form-label required">Título</label>
                        <input type="text" 
                               name="title" 
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $task->title ?? '') }}" 
                               placeholder="Ex: Preparar documentação para venda"
                               required
                               maxlength="255">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Título curto e descritivo da tarefa</small>
                    </div>

                    {{-- Descrição --}}
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" 
                                  class="form-control @error('description') is-invalid @enderror"
                                  rows="4"
                                  placeholder="Descrição detalhada da tarefa...">{{ old('description', $task->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Data Limite --}}
                    <div class="col-md-6">
                        <label class="form-label required">Data Limite</label>
                        <input type="date" 
                               name="due_date" 
                               class="form-control @error('due_date') is-invalid @enderror"
                               value="{{ old('due_date', isset($task) ? $task->due_date->format('Y-m-d') : '') }}" 
                               required>
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Data em que a tarefa deve ser concluída</small>
                    </div>

                    {{-- Data do Lembrete --}}
                    <div class="col-md-6">
                        <label class="form-label">Data do Lembrete</label>
                        <input type="date" 
                               name="reminder_date" 
                               class="form-control @error('reminder_date') is-invalid @enderror"
                               value="{{ old('reminder_date', isset($task) && $task->reminder_date ? $task->reminder_date->format('Y-m-d') : '') }}">
                        @error('reminder_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Receberá um email nesta data (deve ser anterior ou igual à data limite)</small>
                    </div>

                    {{-- Estado --}}
                    <div class="col-md-6">
                        <label class="form-label required">Estado</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="pendente" {{ old('status', $task->status ?? 'pendente') == 'pendente' ? 'selected' : '' }}>
                                Pendente
                            </option>
                            <option value="em_progresso" {{ old('status', $task->status ?? '') == 'em_progresso' ? 'selected' : '' }}>
                                Em Progresso
                            </option>
                            <option value="concluida" {{ old('status', $task->status ?? '') == 'concluida' ? 'selected' : '' }}>
                                Concluída
                            </option>
                            <option value="cancelada" {{ old('status', $task->status ?? '') == 'cancelada' ? 'selected' : '' }}>
                                Cancelada
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ASSOCIAÇÕES --}}
            <div class="modern-card mt-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-link-45deg"></i>
                        Associações (Opcional)
                    </h5>
                </div>

                <div class="row g-3">
                    {{-- Usuário do Sistema --}}
                    <div class="col-md-6">
                        <label class="form-label">Usuário Responsável</label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                            <option value="">Selecione um usuário...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id', $task->user_id ?? '') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Selecione um usuário do sistema</small>
                    </div>

                    {{-- Nome do Usuário (alternativo) --}}
                    <div class="col-md-6">
                        <label class="form-label">Ou Nome de Responsável</label>
                        <input type="text" 
                               name="user_name" 
                               class="form-control @error('user_name') is-invalid @enderror"
                               value="{{ old('user_name', $task->user_name ?? '') }}" 
                               placeholder="Ex: João Silva"
                               maxlength="255">
                        @error('user_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Caso não seja um usuário do sistema</small>
                    </div>

                    {{-- Veículo --}}
                    <div class="col-12">
                        <label class="form-label">Veículo Relacionado</label>
                        <select name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror">
                            <option value="">Nenhum veículo...</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $task->vehicle_id ?? '') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->reference }} - {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->year }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Associar tarefa a um veículo específico</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUNA LATERAL --}}
        <div class="col-lg-4">
            {{-- RESUMO --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-clipboard-check"></i>
                        Resumo
                    </h5>
                </div>

                <div class="list-group list-group-flush">
                    @if(isset($task))
                        {{-- Data de Criação --}}
                        <div class="list-group-item">
                            <small class="text-muted d-block">Data de Criação</small>
                            <strong>{{ $task->created_at->format('d/m/Y H:i') }}</strong>
                        </div>

                        {{-- Última Atualização --}}
                        <div class="list-group-item">
                            <small class="text-muted d-block">Última Atualização</small>
                            <strong>{{ $task->updated_at->format('d/m/Y H:i') }}</strong>
                        </div>

                        {{-- Status do Lembrete --}}
                        @if($task->reminder_date)
                            <div class="list-group-item">
                                <small class="text-muted d-block">Status do Lembrete</small>
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
                        @endif
                    @endif

                    {{-- Dicas --}}
                    <div class="list-group-item">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            <strong>Dica:</strong> Use o lembrete para receber um email antes da data limite.
                        </small>
                    </div>
                </div>
            </div>

            {{-- AÇÕES --}}
            <div class="modern-card mt-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">
                        <i class="bi bi-gear"></i>
                        Ações
                    </h5>
                </div>

                <div class="d-grid gap-2 p-3">
                    {{-- Botão Salvar --}}
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i>
                        {{ isset($task) ? 'Atualizar Tarefa' : 'Criar Tarefa' }}
                    </button>

                    {{-- Botão Cancelar --}}
                    <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </a>

                    {{-- Botão Eliminar (apenas edição) --}}
                    @if(isset($task))
                        <hr>
                        <button type="button" 
                                class="btn btn-outline-danger" 
                                onclick="if(confirm('Tem certeza que deseja eliminar esta tarefa?')) { document.getElementById('delete-form').submit(); }">
                            <i class="bi bi-trash"></i>
                            Eliminar Tarefa
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Formulário de Eliminação (apenas edição) --}}
@if(isset($task))
    <form id="delete-form" action="{{ route('admin.tasks.destroy', $task->id) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endif

@endsection

@push('styles')
<style>
    /* Estilos adicionais para o formulário */
    .required::after {
        content: ' *';
        color: #dc3545;
    }

    .list-group-item {
        border-left: none;
        border-right: none;
    }

    .list-group-item:first-child {
        border-top: none;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }
</style>
@endpush
