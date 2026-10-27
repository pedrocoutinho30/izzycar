<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Models\V3Vehicle;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * TaskController
 * 
 * Controlador para gestão de tarefas
 * Permite criar, visualizar, editar e eliminar tarefas
 * Tarefas podem ser associadas a usuários e veículos
 * Inclui calendário para visualização mensal, semanal e diária
 */
class TaskController extends Controller
{
    public function __construct(private TaskService $tasks)
    {
    }

    /**
     * Exibe o calendário de tarefas
     * Suporta visualização mensal, semanal e diária
     */
    public function index(Request $request)
    {
        // Obter tipo de visualização (mensal, semanal ou diária). Em mobile o
        // padrão é semanal (o mensal não cabe bem no ecrã) — sabemos que é
        // mobile pelo cookie "viewport" (ver admin-v2.blade.php), que reflete
        // a largura do ecrã na última página visitada.
        $defaultView = $request->cookie('viewport') === 'mobile' ? 'week' : 'month';
        $view = $request->get('view', $defaultView);
        $date = $request->get('date', now()->toDateString());
        $currentDate = Carbon::parse($date);

        // Definir intervalo de datas baseado na visualização
        switch ($view) {
            case 'week':
                // Visualização semanal (domingo a sábado)
                $startDate = $currentDate->copy()->startOfWeek();
                $endDate = $currentDate->copy()->endOfWeek();
                break;
            case 'day':
                // Visualização diária
                $startDate = $currentDate->copy()->startOfDay();
                $endDate = $currentDate->copy()->endOfDay();
                break;
            default:
                // Visualização mensal
                $startDate = $currentDate->copy()->startOfMonth();
                $endDate = $currentDate->copy()->endOfMonth();
                break;
        }

        // Buscar tarefas do período
        $tasks = Task::with(['user', 'v3Vehicle'])
            ->whereBetween('due_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(function($task) {
                return $task->due_date->format('Y-m-d');
            });

        // Estatísticas gerais
        $stats = [
            'total' => Task::count(),
            'pendentes' => Task::where('status', 'pendente')->count(),
            'em_progresso' => Task::where('status', 'em_progresso')->count(),
            'concluidas' => Task::where('status', 'concluida')->count(),
        ];

        return view('admin.v2.tasks.index', compact(
            'tasks',
            'view',
            'currentDate',
            'startDate',
            'endDate',
            'stats'
        ));
    }

    /**
     * Exibe formulário de criação de nova tarefa
     */
    public function create()
    {
        $users    = User::orderBy('name')->get();
        $vehicles = V3Vehicle::select('id', 'reference', 'brand', 'model', 'year')
            ->orderBy('reference')
            ->get();

        return view('admin.v2.tasks.form', compact('users', 'vehicles'));
    }

    /**
     * Armazena uma nova tarefa no banco de dados
     */
    public function store(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate($this->tasks->rules(), $this->tasks->messages());

        // Criar a tarefa (notifica quem foi atribuído, não a si próprio)
        $this->tasks->create($validated, auth()->id());

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Tarefa criada com sucesso!');
    }

    /**
     * Exibe os detalhes de uma tarefa específica
     */
    public function show(Task $task)
    {
        // Carregar relacionamentos
        $task->load(['user', 'v3Vehicle']);

        return view('admin.v2.tasks.show', compact('task'));
    }

    /**
     * Exibe formulário de edição de tarefa
     */
    public function edit(Task $task)
    {
        $users    = User::orderBy('name')->get();
        $vehicles = V3Vehicle::select('id', 'reference', 'brand', 'model', 'year')
            ->orderBy('reference')
            ->get();

        return view('admin.v2.tasks.form', compact('task', 'users', 'vehicles'));
    }

    /**
     * Atualiza uma tarefa existente
     */
    public function update(Request $request, Task $task)
    {
        // Validação dos dados
        $validated = $request->validate($this->tasks->rules(), $this->tasks->messages());

        // Atualizar a tarefa
        $this->tasks->update($task, $validated);

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Tarefa atualizada com sucesso!');
    }

    /**
     * Remove uma tarefa
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Tarefa eliminada com sucesso!');
    }

    /**
     * Atualiza apenas o status de uma tarefa (via AJAX)
     */
    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:pendente,em_progresso,concluida,cancelada',
        ]);

        $this->tasks->setStatus($task, $validated['status']);

        return response()->json([
            'success' => true,
            'message' => 'Estado atualizado com sucesso!',
            'task' => $task
        ]);
    }

    /**
     * Retorna tarefas em formato JSON para o calendário (via AJAX)
     */
    public function getTasksJson(Request $request)
    {
        $startDate = $request->get('start');
        $endDate = $request->get('end');

        $tasks = Task::with(['user', 'vehicle'])
            ->whereBetween('due_date', [$startDate, $endDate])
            ->get()
            ->map(function($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'start' => $task->due_date->format('Y-m-d'),
                    'description' => $task->description,
                    'status' => $task->status,
                    'statusLabel' => $task->status_label,
                    'statusColor' => $task->status_color,
                    'user' => $task->user?->name ?? $task->user_name,
                    'vehicle' => $task->vehicle ? "{$task->vehicle->brand} {$task->vehicle->model}" : null,
                    'url' => route('admin.tasks.show', $task),
                ];
            });

        return response()->json($tasks);
    }
}
