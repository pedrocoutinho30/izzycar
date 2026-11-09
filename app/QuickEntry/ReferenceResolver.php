<?php

namespace App\QuickEntry;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Legalization;
use App\Models\User;
use App\Permissions\PermissionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Procura clientes, legalizações e categorias na base de dados, só entre o
 * que o utilizador pode ver. Nunca cria nada e nunca escolhe por
 * aproximação: "exact" são correspondências iguais ao texto (sem distinguir
 * acentos nem maiúsculas) e "candidates" são só sugestões para o utilizador.
 */
class ReferenceResolver
{
    private const MAX_CANDIDATES = 12;

    public function __construct(private PermissionService $permissions)
    {
    }

    // ── Âmbito de acesso ────────────────────────────────────────────────────

    public function canSearchClients(User $user): bool
    {
        return $this->permissions->scope($user, 'clients', 'view') !== null;
    }

    public function canSearchLegalizations(User $user): bool
    {
        return $this->permissions->allows($user, 'legalizations', 'view');
    }

    /** @return Collection<int, Client> clientes visíveis (id, name), por nome */
    public function clients(User $user): Collection
    {
        if (! $this->canSearchClients($user)) {
            return collect();
        }

        $query = $this->permissions->applyScope(Client::query(), $user, 'clients', 'view');

        return $query->select('clients.id', 'clients.name')->orderBy('clients.name')->get();
    }

    /** @return Collection<int, Legalization> legalizações visíveis, com o cliente */
    public function legalizations(User $user): Collection
    {
        if (! $this->canSearchLegalizations($user)) {
            return collect();
        }

        return Legalization::with('client:id,name')
            ->select('id', 'client_id', 'marca', 'modelo', 'matricula', 'num_processo_imt', 'created_at')
            ->orderByDesc('id')
            ->get();
    }

    // ── Clientes ────────────────────────────────────────────────────────────

    /** @return array{exact: Collection<int, Client>, candidates: Collection<int, Client>} */
    public function matchClients(User $user, string $name): array
    {
        $needle = TextNormalizer::compare($name);
        $tokens = array_values(array_filter(explode(' ', $needle)));
        $clients = $this->clients($user);

        $exact = $clients->filter(fn (Client $c) => TextNormalizer::compare($c->name) === $needle)->values();

        $candidates = $exact->isNotEmpty() ? collect() : $clients->filter(function (Client $c) use ($tokens) {
            $haystack = ' ' . TextNormalizer::compare($c->name) . ' ';
            foreach ($tokens as $token) {
                if (! str_contains($haystack, ' ' . $token . ' ')) {
                    return false;
                }
            }

            return $tokens !== [];
        })->values()->take(self::MAX_CANDIDATES);

        return ['exact' => $exact, 'candidates' => $candidates];
    }

    // ── Legalizações ────────────────────────────────────────────────────────

    /**
     * Por referência (matrícula ou nº de processo IMT) ou pelo nome do cliente.
     *
     * @return array{exact: Collection<int, Legalization>, candidates: Collection<int, Legalization>, by: string}
     */
    public function matchLegalizations(User $user, string $text): array
    {
        $legalizations = $this->legalizations($user);
        $squash = fn (?string $v) => preg_replace('/[^a-z0-9]/', '', TextNormalizer::compare((string) $v)) ?? '';
        $wanted = $squash($text);

        if ($wanted !== '') {
            $byReference = $legalizations->filter(
                fn (Legalization $l) => $squash($l->matricula) === $wanted || $squash($l->num_processo_imt) === $wanted
            )->values();

            if ($byReference->isNotEmpty()) {
                return ['exact' => $byReference, 'candidates' => collect(), 'by' => 'reference'];
            }
        }

        $clients = $this->matchClients($user, $text);
        $ofClients = fn (Collection $set) => $legalizations->filter(fn (Legalization $l) => $l->client_id && $set->contains('id', $l->client_id))->values();

        return [
            'exact' => $ofClients($clients['exact']),
            'candidates' => $ofClients($clients['candidates'])->take(self::MAX_CANDIDATES),
            'by' => 'client',
        ];
    }

    public function legalizationLabel(Legalization $l): string
    {
        $parts = ['#' . $l->id];
        $vehicle = trim(($l->marca ?? '') . ' ' . ($l->modelo ?? ''));
        if ($vehicle !== '') {
            $parts[] = $vehicle;
        }
        if ($l->matricula) {
            $parts[] = $l->matricula;
        }
        $parts[] = $l->client?->name ?? 'sem cliente';
        if ($l->num_processo_imt) {
            $parts[] = 'processo IMT ' . $l->num_processo_imt;
        }
        if ($l->created_at) {
            $parts[] = 'criada em ' . $l->created_at->format('d/m/Y');
        }

        return implode(' · ', $parts);
    }

    public function clientLabel(Client $c): string
    {
        return $c->name . ' (#' . $c->id . ')';
    }

    // ── Categorias (lista fechada do modelo Expense) ────────────────────────

    /**
     * Procura a categoria pelo maior prefixo das palavras, tolerando o plural
     * ("outros" → Outro). Devolve a chave, o nome e quantas palavras usou.
     *
     * @param  list<string>  $tokens
     * @return array{key: string, label: string, used: int}|null
     */
    public function matchCategory(array $tokens): ?array
    {
        $stem = fn (string $w) => strlen($w) > 3 && str_ends_with($w, 's') ? substr($w, 0, -1) : $w;
        $phrase = fn (string $text) => implode(' ', array_map($stem, array_filter(explode(' ', TextNormalizer::compare(str_replace('_', ' ', $text))))));

        $index = [];
        foreach (Expense::selectableCategories() as $key => $label) {
            $index[$phrase($label)] = [$key, $label];
            $index[$phrase($key)] ??= [$key, $label];
        }
        // Nomes antigos de categorias que mudaram de nome continuam a ser entendidos.
        foreach (['Compra de Veículo' => 'vehicle_purchase', 'Compra de Viatura' => 'vehicle_purchase', 'Venda de Veículo' => 'vehicle_sale',
                  'Reparação' => 'repair', 'Serviço de Importação' => 'import_service'] as $antigo => $key) {
            $label = Expense::selectableCategories()[$key] ?? null;
            if ($label !== null) {
                $index[$phrase($antigo)] ??= [$key, $label];
            }
        }

        for ($used = count($tokens); $used >= 1; $used--) {
            $wanted = $phrase(implode(' ', array_slice($tokens, 0, $used)));
            if (isset($index[$wanted])) {
                return ['key' => $index[$wanted][0], 'label' => $index[$wanted][1], 'used' => $used];
            }
        }

        return null;
    }
}
