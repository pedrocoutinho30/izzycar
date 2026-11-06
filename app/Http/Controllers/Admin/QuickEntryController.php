<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuickEntryInterpretation;
use App\Permissions\PermissionService;
use App\QuickEntry\CommandRecognizer;
use App\Services\QuickEntryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Inserção rápida (desktop e mobile): escrever ou ditar uma frase, rever a
 * proposta e confirmar. Interpretar nunca grava movimentos; só confirm() o faz,
 * pelo QuickEntryService. Mesmas permissões dos formulários normais
 * (criar movimentos / criar clientes), verificadas aqui porque PERMISSIONS_ENFORCE pode estar desligado.
 */
class QuickEntryController extends Controller
{
    public const EXAMPLES = [
        'Criar movimento despesa categoria outros 10€ legalização fabio oliveira pago em dinheiro',
        'Criar movimento receita cliente Fabio oliveira 2000€ pago transferencia bancaria',
        'Criar movimento transporte 922,5€ legalização fabio oliveira pago por transferencia',
        'Criar movimento saída 1.250,50 € categoria reparação ontem por pagar',
        'Criar cliente João Silva telefone 912 345 678 email joao@exemplo.pt nif 123456789',
    ];

    public function __construct(private QuickEntryService $quickEntry, private PermissionService $permissions)
    {
    }

    public function index(Request $request)
    {
        $this->authorizeFor($request, null);

        return $this->render($request, null);
    }

    public function interpret(Request $request): RedirectResponse
    {
        $this->authorizeFor($request, null);

        $data = $request->validate(['text' => 'required|string|max:1000'], [
            'text.required' => 'Escreva ou dite uma frase para interpretar.',
            'text.max' => 'A frase pode ter no máximo 1000 caracteres.',
        ]);

        // Cada comando exige a permissão de criar do seu módulo (movimentos ou clientes).
        $this->authorizeFor($request, app(CommandRecognizer::class)->recognize($data['text'])['module'] ?? null);

        $interpretation = $this->quickEntry->interpret($request->user(), trim($data['text']));

        return redirect()->route($this->prefix($request) . 'show', $interpretation->id);
    }

    public function show(Request $request, int $id)
    {
        $interpretation = $this->own($request, $id);
        $this->authorizeFor($request, $interpretation->module);

        return $this->render($request, $interpretation);
    }

    public function confirm(Request $request, int $id): RedirectResponse
    {
        $interpretation = $this->own($request, $id);
        $this->authorizeFor($request, $interpretation->module);

        $result = $this->quickEntry->confirm($request->user(), $interpretation, $request->all());

        $mobile = $this->isMobile($request);
        if ($result['client']) {
            $canOpen = $this->permissions->allows($request->user(), 'clients', 'view', $result['client']);
            $target = $mobile
                ? ($canOpen ? route('admin.mobile.clients.show', $result['client']->id) : route('admin.mobile.clients.index'))
                : route('admin.v2.clients.index');

            return redirect($target)->with('success', $result['duplicate']
                ? 'Este cliente já tinha sido criado; não foi criado um segundo.'
                : 'Cliente criado com sucesso!');
        }

        $target = $mobile && $result['movement']
            ? route('admin.mobile.movements.show', $result['movement']->id)
            : route($mobile ? 'admin.mobile.movements.index' : 'admin.v2.movements.index');

        return redirect($target)->with('success', $result['duplicate']
            ? 'Este movimento já tinha sido criado; não foi criado um segundo.'
            : 'Movimento criado com sucesso!');
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $interpretation = $this->own($request, $id);
        $this->authorizeFor($request, $interpretation->module);
        $this->quickEntry->cancel($interpretation);

        return redirect()->route($this->prefix($request) . 'index')->with('success', 'Proposta cancelada. Nada foi gravado.');
    }

    private function render(Request $request, ?QuickEntryInterpretation $interpretation)
    {
        $mobile = $this->isMobile($request);

        return view('admin.quick-entry.index', [
            'mobile' => $mobile,
            'layout' => $mobile ? 'layouts.admin-mobile' : 'layouts.admin-v2',
            'prefix' => $this->prefix($request),
            'interpretation' => $interpretation,
            'proposal' => $interpretation?->proposal,
            'examples' => self::EXAMPLES,
        ] + ($interpretation ? $this->quickEntry->formOptions($request->user()) : []));
    }

    /** Só o próprio utilizador abre e confirma as suas interpretações. */
    private function own(Request $request, int $id): QuickEntryInterpretation
    {
        return QuickEntryInterpretation::where('user_id', $request->user()->getKey())->findOrFail($id);
    }

    /** Sem módulo: basta poder criar movimentos ou clientes. Com módulo: a permissão desse módulo. */
    private function authorizeFor(Request $request, ?string $module): void
    {
        $user = $request->user();
        $movements = $this->permissions->allows($user, 'movements', 'create');
        $clients = $this->permissions->allows($user, 'clients', 'create');

        abort_unless(match ($module) {
            'movimento' => $movements,
            'cliente' => $clients,
            default => $movements || $clients,
        }, 403);
    }

    private function isMobile(Request $request): bool
    {
        return $request->routeIs('admin.mobile.*');
    }

    private function prefix(Request $request): string
    {
        return $this->isMobile($request) ? 'admin.mobile.quick-entry.' : 'admin.v2.quick-entry.';
    }
}
