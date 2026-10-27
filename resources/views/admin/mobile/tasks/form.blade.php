@extends('layouts.admin-mobile')

@php
    $editar = $task->exists;
    $valor = fn (string $campo) => old($campo, $task->{$campo});
    $prazo = old('due_date', $task->due_date?->format('Y-m-d'));
    $lembrete = old('reminder_date', $task->reminder_date?->format('Y-m-d'));
    $estados = ['pendente' => 'Pendente', 'em_progresso' => 'Em progresso', 'concluida' => 'Concluída', 'cancelada' => 'Cancelada'];
    $voltar = route('admin.mobile.tasks.index');
@endphp

@section('title', $editar ? 'Editar tarefa' : 'Nova tarefa')

@section('header_actions')
    <a href="{{ $voltar }}" class="order-first m-topbar-back" aria-label="Voltar"><i class="bi bi-chevron-left fs-4"></i></a>
@endsection

@section('content')
    <form action="{{ $editar ? route('admin.mobile.tasks.update', $task->id) : route('admin.mobile.tasks.store') }}" method="POST" class="d-grid gap-3" style="font-size:16px">
        @csrf
        @if($editar) @method('PUT') @endif

        <div class="m-card d-grid gap-3 mb-0">
            <div>
                <label for="task-title" class="form-label fw-semibold">Título <span class="text-danger">*</span></label>
                <input type="text" id="task-title" name="title" value="{{ $valor('title') }}" class="form-control @error('title') is-invalid @enderror"
                       required maxlength="255" enterkeyhint="next" style="font-size:16px">
            </div>

            <div>
                <label for="task-description" class="form-label fw-semibold">Descrição</label>
                <textarea id="task-description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror" style="font-size:16px">{{ $valor('description') }}</textarea>
            </div>

            <div>
                <label for="task-due" class="form-label fw-semibold">Data limite <span class="text-danger">*</span></label>
                <input type="date" id="task-due" name="due_date" value="{{ $prazo }}" class="form-control @error('due_date') is-invalid @enderror" required style="font-size:16px; min-height:44px">
            </div>

            <div>
                <label for="task-reminder" class="form-label fw-semibold">Lembrete</label>
                <input type="date" id="task-reminder" name="reminder_date" value="{{ $lembrete }}" class="form-control @error('reminder_date') is-invalid @enderror" style="font-size:16px; min-height:44px">
                <div class="form-text">Opcional: igual ou anterior à data limite.</div>
            </div>

            <div>
                <label for="task-user" class="form-label fw-semibold">Atribuída a</label>
                <select id="task-user" name="user_id" class="form-select @error('user_id') is-invalid @enderror" style="font-size:16px; min-height:44px">
                    <option value="">Sem atribuição</option>
                    @foreach($users as $utilizador)
                        <option value="{{ $utilizador->id }}" @selected((string) $valor('user_id') === (string) $utilizador->id)>{{ $utilizador->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="task-status" class="form-label fw-semibold">Estado <span class="text-danger">*</span></label>
                <select id="task-status" name="status" class="form-select @error('status') is-invalid @enderror" required style="font-size:16px; min-height:44px">
                    @foreach($estados as $chave => $rotulo)
                        <option value="{{ $chave }}" @selected($valor('status') === $chave)>{{ $rotulo }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100" style="min-height:48px"><i class="bi bi-check-lg me-1"></i> {{ $editar ? 'Guardar alterações' : 'Criar tarefa' }}</button>
        <a href="{{ $voltar }}" class="btn btn-outline-secondary w-100" style="min-height:48px; display:flex; align-items:center; justify-content:center">Cancelar</a>
    </form>
@endsection
