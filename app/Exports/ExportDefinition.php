<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;

/**
 * O que um módulo precisa para ser exportado: query base, filtros e campos.
 * Os módulos novos (cotações, viaturas…) acrescentam uma subclasse e uma
 * linha no ExportRegistry.
 */
abstract class ExportDefinition
{
    /** @var list<ExportField>|null */
    private ?array $fields = null;

    /** Chave usada no registo, nas rotas e nos testes (ex. "leads"). */
    abstract public function type(): string;

    /** Prefixo do nome do ficheiro (ex. "leads" → leads_20261020_101500.xlsx). */
    abstract public function filenamePrefix(): string;

    abstract public function title(): string;

    /** Nome da folha do Excel. */
    public function sheetName(): string
    {
        return $this->title();
    }

    /** Chaves de filtro aceites (as mesmas da listagem). */
    abstract public function filterKeys(): array;

    /**
     * Ajusta os filtros validados antes de os usar e de os auditar (ex.: valores
     * por omissão que a listagem também aplica). Por omissão não muda nada.
     */
    public function normalizeFilters(array $filters): array
    {
        return $filters;
    }

    /**
     * Linhas a exportar, por lotes. Por omissão do id mais recente para o mais
     * antigo; os módulos com outra ordem (ex. movimentos) redefinem.
     *
     * @return iterable<\Illuminate\Database\Eloquent\Model>
     */
    public function rows(Builder $query, int $chunk): iterable
    {
        return $query->lazyByIdDesc($chunk);
    }

    /** Query base (sem relações/contagens: vêm dos campos escolhidos). */
    abstract protected function baseQuery(array $filters): Builder;

    /** @return list<ExportField> */
    abstract protected function defineFields(): array;

    /** @return list<ExportField> */
    final public function fields(): array
    {
        return $this->fields ??= $this->defineFields();
    }

    public function field(string $key): ?ExportField
    {
        foreach ($this->fields() as $field) {
            if ($field->key === $key) {
                return $field;
            }
        }

        return null;
    }

    /** @return list<string> */
    public function keys(): array
    {
        return array_map(fn (ExportField $f) => $f->key, $this->fields());
    }

    /** @return list<string> */
    public function defaultKeys(): array
    {
        return array_values(array_map(
            fn (ExportField $f) => $f->key,
            array_filter($this->fields(), fn (ExportField $f) => $f->default),
        ));
    }

    /** @return array<string, list<ExportField>> campos por grupo, pela ordem do catálogo */
    public function groups(): array
    {
        $groups = [];
        foreach ($this->fields() as $field) {
            $groups[$field->group][] = $field;
        }

        return $groups;
    }

    /**
     * Query com os filtros aplicados e só as relações/contagens que os campos
     * escolhidos precisam (evita N+1 sem carregar o que não se exporta).
     *
     * @param  list<ExportField>  $fields
     */
    public function query(array $filters, array $fields): Builder
    {
        $query = $this->baseQuery($filters);

        $with = array_values(array_unique(array_merge(...array_map(fn (ExportField $f) => $f->with, $fields) ?: [[]])));
        $counts = array_values(array_unique(array_merge(...array_map(fn (ExportField $f) => $f->counts, $fields) ?: [[]])));

        if ($with) {
            $query->with($with);
        }
        if ($counts) {
            $query->withCount($counts);
        }

        return $query;
    }
}
