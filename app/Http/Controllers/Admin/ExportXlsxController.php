<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportXlsxRequest;
use App\Models\AuditLog;
use App\Services\ExportService;

/**
 * Exportação XLSX (ver App\Exports\ExportRegistry). Cada módulo tem a sua
 * rota (para o config/permissions.php) mas a lógica é comum. O CSV antigo
 * continua no ExportController.
 */
class ExportXlsxController extends Controller
{
    public function __construct(private ExportService $exports)
    {
    }

    public function download(ExportXlsxRequest $request, string $tipo)
    {
        $definition = $request->definition();
        $fields = $this->exports->resolveFields($definition, $request->fieldKeys());

        $filters = $request->filters();
        $keys = array_map(fn ($f) => $f->key, $fields);

        // Auditoria só depois de o ficheiro ser gerado. Nunca se grava o conteúdo
        // exportado: só o módulo, as chaves dos campos, os filtros e a contagem.
        return $this->exports->download($definition, $fields, $filters, function (int $rows) use ($tipo, $keys, $filters) {
            AuditLog::record(
                'exported',
                "Exportação XLSX de {$tipo} ({$rows} linhas)",
                null,
                null,
                [
                    'module' => $tipo,
                    'format' => 'xlsx',
                    'fields' => $keys,
                    'filters' => $this->auditFilters($filters),
                    'rows' => $rows,
                ],
            );
        });
    }

    /**
     * A pesquisa livre pode conter um NIF, email ou telefone: regista-se que foi
     * usada, mas não o texto. Os restantes filtros são valores de listas fechadas.
     */
    private function auditFilters(array $filters): array
    {
        if (array_key_exists('search', $filters) && $filters['search'] !== null && $filters['search'] !== '') {
            $filters['search'] = '[omitido]';
        }

        return $filters;
    }
}
