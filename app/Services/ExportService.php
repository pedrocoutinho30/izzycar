<?php

namespace App\Services;

use App\Exports\ExportDefinition;
use App\Exports\ExportField;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use InvalidArgumentException;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\DateTimeCell;
use OpenSpout\Common\Entity\Cell\EmptyCell;
use OpenSpout\Common\Entity\Cell\NumericCell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Exportação XLSX comum (clientes, leads e, mais tarde, os outros módulos).
 * Lê por lotes (lazy por lotes: sem N+1 e sem carregar tudo em memória) e
 * escreve em streaming com o openspout.
 *
 * Regras das células:
 *  - texto é sempre StringCell (nunca FormulaCell: "=1+1" fica como texto);
 *  - números e dinheiro são numéricos com formato; datas são células de data;
 *  - booleanos saem "Sim"/"Não"; null é célula vazia (0 continua a ser 0).
 */
class ExportService
{
    public const CONTENT_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    private const CHUNK = 500;

    /** Limite de caracteres de uma célula no Excel. */
    private const MAX_TEXT = 32767;

    private const FORMATS = [
        'int' => '0',
        'decimal' => '#,##0.00',
        'money' => '#,##0.00 "€"',
        'percent' => '0.00%',
        'date' => 'dd/mm/yyyy',
        'datetime' => 'dd/mm/yyyy hh:mm',
    ];

    /** @var array<string, Style> */
    private array $styles = [];

    /**
     * Campos pedidos pela ordem pedida. Chaves desconhecidas são um erro;
     * sem chaves usam-se os campos por defeito. Repetidos contam uma vez.
     *
     * @param  list<string>|null  $keys
     * @return list<ExportField>
     */
    public function resolveFields(ExportDefinition $definition, ?array $keys): array
    {
        $keys = array_values(array_unique(array_filter($keys ?? [], fn ($k) => $k !== null && $k !== '')));
        if ($keys === []) {
            $keys = $definition->defaultKeys();
        }

        return array_map(function ($key) use ($definition) {
            return $definition->field((string) $key)
                ?? throw new InvalidArgumentException("Campo de exportação desconhecido: {$key}");
        }, $keys);
    }

    public function filename(ExportDefinition $definition, ?CarbonInterface $now = null): string
    {
        return $definition->filenamePrefix() . '_' . ($now ?? now())->format('Ymd_His') . '.xlsx';
    }

    /**
     * Resposta de download em streaming. Os campos já têm de estar validados
     * (ver resolveFields).
     *
     * @param  list<ExportField>  $fields
     * @param  (callable(int): void)|null  $onSuccess  chamado com o nº de linhas, só depois de o ficheiro ser escrito sem erro
     */
    public function download(ExportDefinition $definition, array $fields, array $filters, ?callable $onSuccess = null): StreamedResponse
    {
        return response()->streamDownload(
            function () use ($definition, $fields, $filters, $onSuccess) {
                $rows = $this->write($definition, $fields, $filters, 'php://output');
                if ($onSuccess) {
                    $onSuccess($rows);
                }
            },
            $this->filename($definition),
            ['Content-Type' => self::CONTENT_TYPE],
        );
    }

    /**
     * Escreve o ficheiro XLSX em $target (caminho ou php://output).
     *
     * @param  list<ExportField>  $fields
     * @return int nº de linhas de dados escritas (sem o cabeçalho)
     */
    public function write(ExportDefinition $definition, array $fields, array $filters, string $target): int
    {
        $count = 0;
        $options = new Options();
        foreach ($fields as $i => $field) {
            $options->setColumnWidth($this->width($field), $i + 1);
        }

        $writer = new Writer($options);
        $writer->openToFile($target);

        try {
            $sheet = $writer->getCurrentSheet();
            $sheet->setName(mb_substr(preg_replace('/[\\\\\/?*\[\]:]/u', ' ', $definition->sheetName()), 0, 31));
            $sheet->setSheetView((new SheetView())->setFreezeRow(2));

            $writer->addRow(new Row(
                array_map(fn (ExportField $f) => new StringCell($f->label, $this->style('header')), $fields),
            ));

            $query = $definition->query($filters, $fields);
            foreach ($definition->rows($query, self::CHUNK) as $model) {
                $cells = [];
                foreach ($fields as $field) {
                    $cells[] = $this->cell($field, $field->read($model));
                }
                $writer->addRow(new Row($cells));
                $count++;
            }
        } finally {
            $writer->close();
        }

        return $count;
    }

    /** Converte o valor lido do modelo na célula do tipo do campo. */
    public function cell(ExportField $field, mixed $value): Cell
    {
        if ($value === null || $value === '') {
            return new EmptyCell(null, $this->style('text'));
        }

        return match ($field->type) {
            'bool' => $this->text($value ? 'Sim' : 'Não'),
            'int', 'decimal', 'money', 'percent' => $this->number($field->type, $value),
            'date', 'datetime' => $this->date($field->type, $value),
            default => $this->text($value),
        };
    }

    private function text(mixed $value): Cell
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        }
        $value = $value instanceof DateTimeInterface ? $value->format('d/m/Y H:i') : (string) $value;

        return new StringCell(mb_substr($value, 0, self::MAX_TEXT), $this->style('text'));
    }

    private function number(string $type, mixed $value): Cell
    {
        if (is_bool($value)) {
            $value = (int) $value;
        }
        // Valor que não é número (dado sujo): sai como texto em vez de falhar.
        if (!is_numeric($value)) {
            return $this->text($value);
        }

        $number = $type === 'int' ? (int) $value : (float) $value;
        if ($type === 'percent') {
            $number /= 100;
        }

        return new NumericCell($number, $this->style($type));
    }

    private function date(string $type, mixed $value): Cell
    {
        try {
            $date = $value instanceof DateTimeInterface ? Carbon::instance($value) : Carbon::parse((string) $value);
        } catch (Throwable) {
            return $this->text($value);
        }

        if ($type === 'date') {
            $date = $date->copy()->startOfDay();
        }

        return new DateTimeCell($date, $this->style($type));
    }

    private function style(string $name): Style
    {
        return $this->styles[$name] ??= match (true) {
            $name === 'header' => (new Style())->setFontBold(),
            $name === 'text' => new Style(),
            default => (new Style())->setFormat(self::FORMATS[$name]),
        };
    }

    private function width(ExportField $field): float
    {
        if ($field->width !== null) {
            return $field->width;
        }

        $byType = match ($field->type) {
            'date' => 12,
            'datetime' => 17,
            'int', 'bool' => 10,
            'money', 'decimal', 'percent' => 14,
            default => 20,
        };

        return (float) min(max($byType, mb_strlen($field->label) + 3), 40);
    }
}
