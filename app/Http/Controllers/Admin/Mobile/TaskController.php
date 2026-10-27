<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Permissions\PermissionService;
use App\Services\TaskService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Tarefas na versão mobile: lista em cartões com filtros, criar, editar e
 * concluir com um toque. A lógica é a do desktop (TaskService). Calendário,
 * eliminar e ações em lote ficam no desktop.
 *
 * O objeto "tasks" não tem âmbito "próprios": quem pode ver, vê todas e quem
 * pode editar, edita todas (como no desktop). Cada ação verifica-se aqui,
 * para valer também com PERMISSIONS_ENFORCE desligado.
 */
class TaskController extends Controller
{
    /** Caracter de escape do LIKE literal (MySQL e sqlite). */
    private const ESCAPE = '!';

    /** Filtros da lista (chave => rótulo). */
    private const FILTROS = [
        'para_tratar' => 'Para tratar',
        'hoje' => 'Hoje',
        'atraso' => 'Em atraso',
        'pendentes' => 'Pendentes',
        'concluidas' => 'Concluídas',
    ];

    public function __construct(private TaskService $tasks, private PermissionService $permissions)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($this->permissions->allows($user, 'tasks', 'view'), 403);

        $filtro = $request->query('filtro');
        $filtro = is_string($filtro) && array_key_exists($filtro, self::FILTROS) ? $filtro : 'para_tratar';
        // Por omissão só as atribuídas ao utilizador; "minhas=0" mostra todas.
        $minhas = $request->query('minhas') !== '0';
        $search = $request->query('search');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';

        $hoje = now()->toDateString();
        $amanha = now()->addDay()->toDateString();

        $query = Task::with(['user', 'v3Vehicle']);

        // "< amanhã" / ">= hoje": valem com a data guardada com ou sem hora (MySQL e sqlite).
        match ($filtro) {
            'para_tratar' => $query->whereIn('status', TaskService::STATUS_ABERTOS)->where('due_date', '<', $amanha),
            'hoje' => $query->whereIn('status', TaskService::STATUS_ABERTOS)->where('due_date', '>=', $hoje)->where('due_date', '<', $amanha),
            'atraso' => $query->whereIn('status', TaskService::STATUS_ABERTOS)->where('due_date', '<', $hoje),
            'pendentes' => $query->whereIn('status', TaskService::STATUS_ABERTOS),
            'concluidas' => $query->where('status', 'concluida'),
        };

        if ($minhas) {
            $query->where('user_id', $user->id);
        }

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $this->like($q, 'title', $search);
                $this->like($q, 'description', $search, 'or');
            });
        }

        $filtro === 'concluidas'
            ? $query->orderBy('updated_at', 'desc')
            : $query->orderBy('due_date', 'asc');
        $query->orderBy('id', 'asc');

        $params = ['filtro' => $filtro, 'search' => $search !== '' ? $search : null, 'minhas' => $minhas ? null : '0'];

        return view('admin.mobile.tasks.index', [
            'tasks' => $query->paginate(15)->appends(array_filter($params, fn ($v) => $v !== null)),
            'filtros' => self::FILTROS,
            'filtro' => $filtro,
            'minhas' => $minhas,
            'search' => $search,
            'hoje' => $hoje,
            'canCreate' => $this->permissions->allows($user, 'tasks', 'create'),
            'canUpdate' => $this->permissions->allows($user, 'tasks', 'update'),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($this->permissions->allows($request->user(), 'tasks', 'create'), 403);

        // Por omissão: atribuída a quem a cria, para hoje.
        $task = new Task(['status' => 'pendente', 'due_date' => now()->toDateString(), 'user_id' => $request->user()->id]);

        return view('admin.mobile.tasks.form', ['task' => $task, 'users' => $this->users()]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($this->permissions->allows($user, 'tasks', 'create'), 403);

        $data = $this->validated($request);
        $this->tasks->create($data, $user->id);

        return redirect()->route('admin.mobile.tasks.index')->with('success', 'Tarefa criada com sucesso!');
    }

    public function edit(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        abort_unless($this->permissions->allows($request->user(), 'tasks', 'update', $task), 403);

        return view('admin.mobile.tasks.form', ['task' => $task, 'users' => $this->users()]);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        abort_unless($this->permissions->allows($request->user(), 'tasks', 'update', $task), 403);

        $this->tasks->update($task, $this->validated($request));

        return redirect()->route('admin.mobile.tasks.index')->with('success', 'Tarefa atualizada com sucesso!');
    }

    /** Concluir com um toque: o desktop só muda o estado (sem recorrência nem avisos). */
    public function complete(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        abort_unless($this->permissions->allows($request->user(), 'tasks', 'update', $task), 403);

        if ($task->status === 'concluida') {
            return back()->with('info', 'A tarefa já estava concluída.');
        }

        // Só as abertas se concluem: um POST forjado não reabre/converte uma cancelada.
        if (!in_array($task->status, TaskService::STATUS_ABERTOS, true)) {
            return back()->with('info', 'Esta tarefa já não está aberta.');
        }

        $this->tasks->complete($task);

        return back()->with('success', 'Tarefa concluída.');
    }

    /**
     * Só os campos do formulário mobile chegam ao modelo (a viatura e o nome livre
     * ficam como estão); os ids só aceitam inteiros e vão como tal para o service.
     */
    private function validated(Request $request): array
    {
        $rules = collect($this->tasks->rules(true))->only(['title', 'description', 'due_date', 'reminder_date', 'user_id', 'status'])->all();
        $data = $request->validate($rules, $this->tasks->messages());

        // Vazio = sem atribuição; um pedido sem o campo não mexe na atribuição.
        if (isset($data['user_id'])) {
            $data['user_id'] = (int) $data['user_id'];
        }

        return $data;
    }

    /** Utilizadores para o seletor de atribuição (como no desktop). */
    private function users()
    {
        return User::select('id', 'name')->orderBy('name')->get();
    }

    private function like(Builder $q, string $column, string $term, string $boolean = 'and'): void
    {
        $escaped = str_replace([self::ESCAPE, '%', '_'], [self::ESCAPE . self::ESCAPE, self::ESCAPE . '%', self::ESCAPE . '_'], $term);
        $wrapped = $q->getQuery()->getGrammar()->wrap($q->qualifyColumn($column));

        $q->whereRaw("{$wrapped} like ? escape '" . self::ESCAPE . "'", ["%{$escaped}%"], $boolean);
    }
}
