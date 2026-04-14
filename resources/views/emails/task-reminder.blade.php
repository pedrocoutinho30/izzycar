{{--
    Email: Lembrete de Tarefa
    
    Enviado automaticamente quando a data de lembrete de uma tarefa chega
--}}

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembrete de Tarefa</title>
    <style>
        /* Estilos inline para compatibilidade com clientes de email */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .email-header {
            background-color: #990000;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .email-body {
            padding: 30px 20px;
            color: #333333;
        }
        .task-card {
            background-color: #f8f9fa;
            border-left: 4px solid #990000;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .task-title {
            font-size: 20px;
            font-weight: 600;
            color: #990000;
            margin-bottom: 10px;
        }
        .task-description {
            color: #666666;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        .task-meta {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        .task-meta-item {
            display: table-row;
            margin-bottom: 8px;
        }
        .task-meta-label {
            display: table-cell;
            font-weight: 600;
            color: #666666;
            padding: 5px 15px 5px 0;
            width: 40%;
        }
        .task-meta-value {
            display: table-cell;
            color: #333333;
            padding: 5px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pendente {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-em_progresso {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .alert-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .alert-box.danger {
            background-color: #f8d7da;
            border-left-color: #dc3545;
        }
        .btn-primary {
            display: inline-block;
            background-color: #990000;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 4px;
            font-weight: 600;
            margin-top: 20px;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        {{-- Cabeçalho --}}
        <div class="email-header">
            <div class="icon">🔔</div>
            <h1>Lembrete de Tarefa</h1>
        </div>

        {{-- Corpo do Email --}}
        <div class="email-body">
            <p>Olá{{ $task->user?->name ?? ($task->user_name ? ' ' . explode(' ', $task->user_name)[0] : '') }},</p>
            
            <p>Este é um lembrete sobre a seguinte tarefa:</p>

            {{-- Card da Tarefa --}}
            <div class="task-card">
                <div class="task-title">{{ $task->title }}</div>
                
                @if($task->description)
                    <div class="task-description">{{ $task->description }}</div>
                @endif

                <div class="task-meta">
                    {{-- Estado --}}
                    <div class="task-meta-item">
                        <div class="task-meta-label">Estado:</div>
                        <div class="task-meta-value">
                            <span class="status-badge status-{{ $task->status }}">
                                {{ $task->status_label }}
                            </span>
                        </div>
                    </div>

                    {{-- Data Limite --}}
                    <div class="task-meta-item">
                        <div class="task-meta-label">Data Limite:</div>
                        <div class="task-meta-value">
                            <strong>{{ $task->due_date->format('d/m/Y') }}</strong>
                            ({{ $task->due_date->diffForHumans() }})
                        </div>
                    </div>

                    {{-- Veículo --}}
                    @if($task->vehicle)
                        <div class="task-meta-item">
                            <div class="task-meta-label">Veículo:</div>
                            <div class="task-meta-value">
                                {{ $task->vehicle->reference }} - {{ $task->vehicle->brand }} {{ $task->vehicle->model }}
                            </div>
                        </div>
                    @endif

                    {{-- Data de Criação --}}
                    <div class="task-meta-item">
                        <div class="task-meta-label">Criada em:</div>
                        <div class="task-meta-value">{{ $task->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- Alerta se estiver atrasada --}}
            @if($task->due_date->isPast())
                <div class="alert-box danger">
                    <strong>⚠️ Atenção!</strong> Esta tarefa está atrasada!
                </div>
            @elseif($task->due_date->isToday())
                <div class="alert-box">
                    <strong>⏰ Hoje!</strong> Esta tarefa vence hoje!
                </div>
            @endif

            <p>Não se esqueça de concluir esta tarefa dentro do prazo.</p>

            {{-- Botão de Ação --}}
            <center>
                <a href="{{ route('admin.tasks.show', $task->id) }}" class="btn-primary">
                    Ver Detalhes da Tarefa
                </a>
            </center>
        </div>

        {{-- Rodapé --}}
        <div class="email-footer">
            <p>Este é um email automático. Por favor, não responda.</p>
            <p>&copy; {{ date('Y') }} IzzyCar. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
