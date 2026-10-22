<?php

namespace Tests\Feature;

use App\Exports\ClientExport;
use App\Exports\LeadExport;
use App\Models\Client;
use App\Services\ExportService;
use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;
use OpenSpout\Reader\XLSX\Reader;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ExportXlsxServiceTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    /** Escreve o export num ficheiro temporário e devolve as linhas (cada uma: lista de células). */
    private function export($definition, ?array $keys, array $filters = []): array
    {
        $service = new ExportService();
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $service->write($definition, $service->resolveFields($definition, $keys), $filters, $path);

        $reader = new Reader();
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->getCells();
            }
        }
        $reader->close();
        unlink($path);

        return $rows;
    }

    private function values(array $row): array
    {
        return array_map(fn ($c) => $c->getValue(), $row);
    }

    public function test_columns_follow_the_requested_order_and_header_is_labels(): void
    {
        Client::create(['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '912345678', 'is_lead' => false]);

        $rows = $this->export(new ClientExport(), ['email', 'name', 'phone']);

        $this->assertSame(['Email', 'Nome', 'Telefone'], $this->values($rows[0]));
        $this->assertSame(['ana@example.com', 'Ana', '912345678'], $this->values($rows[1]));
    }

    public function test_empty_selection_uses_default_fields(): void
    {
        $service = new ExportService();
        $fields = $service->resolveFields(new LeadExport(), []);

        $this->assertSame((new LeadExport())->defaultKeys(), array_map(fn ($f) => $f->key, $fields));
    }

    public function test_unknown_field_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ExportService())->resolveFields(new ClientExport(), ['name', 'password']);
    }

    public function test_user_text_is_never_a_formula_and_identifiers_stay_text(): void
    {
        foreach (['=1+1', '+SUM(A1)', '@x', '-1'] as $i => $text) {
            Client::create(['name' => $text, 'vat_number' => '0123456789' . $i, 'postal_code' => '1000-001', 'identification_number' => '00012' . $i, 'phone' => '+351912345678', 'is_lead' => false]);
        }

        $rows = $this->export(new ClientExport(), ['name', 'vat_number', 'postal_code', 'identification_number', 'phone']);
        array_shift($rows); // cabeçalho

        $names = array_map(fn ($r) => $r[0]->getValue(), $rows);
        sort($names);
        $this->assertSame(['+SUM(A1)', '-1', '=1+1', '@x'], $names);

        foreach ($rows as $row) {
            foreach ($row as $cell) {
                $this->assertIsString($cell->getValue());
            }
        }

        // O leitor do openspout converte "=..." em fórmula ao ler; o que conta é o XML escrito:
        // texto em linha (inlineStr) e nenhuma fórmula (<f>).
        $service = new ExportService();
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $service->write(new ClientExport(), $service->resolveFields(new ClientExport(), ['name']), [], $path);
        $zip = new \ZipArchive();
        $zip->open($path);
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        unlink($path);
        $this->assertStringNotContainsString('<f>', $xml);
        $this->assertStringContainsString('t="inlineStr"', $xml);
        $this->assertStringContainsString('=1+1', $xml);

        // zeros à esquerda e prefixo "+" preservados
        $this->assertSame('01234567890', collect($rows)->firstWhere(fn ($r) => $r[0]->getValue() === '=1+1')[1]->getValue());
        $this->assertSame('+351912345678', $rows[0][4]->getValue());
    }

    public function test_empty_is_distinct_from_zero_and_booleans_are_sim_nao(): void
    {
        Client::create(['name' => 'Sem dono', 'is_lead' => false, 'data_processing_consent' => true, 'newsletter_consent' => false]);

        $rows = $this->export(new ClientExport(), ['owner', 'proposals_count', 'data_processing_consent', 'newsletter_consent', 'vat_number']);

        $this->assertSame(['Angariador', 'Nº de cotações', 'Consentimento tratamento de dados', 'Consentimento newsletter', 'NIF'], $this->values($rows[0]));
        $row = $rows[1];
        $this->assertInstanceOf(\OpenSpout\Common\Entity\Cell\EmptyCell::class, $row[0]);
        $this->assertSame(0, $row[1]->getValue());
        $this->assertSame('Sim', $row[2]->getValue());
        $this->assertSame('Não', $row[3]->getValue());
        $this->assertInstanceOf(\OpenSpout\Common\Entity\Cell\EmptyCell::class, $row[4]);
    }

    public function test_dates_are_native_date_cells(): void
    {
        Client::create([
            'name' => 'Datas', 'is_lead' => false,
            'birth_date' => '1990-03-15', 'converted_at' => '2026-05-02 10:30:00',
        ]);

        $rows = $this->export(new ClientExport(), ['birth_date', 'converted_at', 'created_at']);

        $birth = $rows[1][0]->getValue();
        $this->assertInstanceOf(DateTimeInterface::class, $birth);
        $this->assertSame('1990-03-15 00:00', $birth->format('Y-m-d H:i'));
        $this->assertSame('2026-05-02 00:00', $rows[1][1]->getValue()->format('Y-m-d H:i'));
        $this->assertInstanceOf(DateTimeInterface::class, $rows[1][2]->getValue());

        // datetime mantém a hora
        $lead = Client::create(['name' => 'Lead', 'is_lead' => true, 'next_followup_at' => '2026-11-03 14:45:00']);
        $leadRows = $this->export(new LeadExport(), ['next_followup_at']);
        $this->assertSame('2026-11-03 14:45', $leadRows[1][0]->getValue()->format('Y-m-d H:i'));
    }

    public function test_client_type_gender_and_labels_are_normalized(): void
    {
        Client::create(['name' => 'A', 'is_lead' => false, 'client_type' => 'EMPRESA', 'gender' => 'masculino', 'lead_source' => 'newsletter_footer']);
        Client::create(['name' => 'B', 'is_lead' => false, 'client_type' => 'particular', 'gender' => 'Mulher', 'lead_source' => 'valor_novo']);

        $rows = $this->export(new ClientExport(), ['name', 'client_type', 'gender', 'lead_source']);
        $byName = collect(array_slice($rows, 1))->mapWithKeys(fn ($r) => [$r[0]->getValue() => $this->values($r)]);

        $this->assertSame(['A', 'Empresa', 'Masculino', 'Newsletter'], $byName['A']);
        $this->assertSame(['B', 'Particular', 'Feminino', 'valor_novo'], $byName['B']);
    }

    public function test_missing_lead_status_counts_as_nova(): void
    {
        $this->assertSame('Nova', \App\Enums\LeadStatus::labelOf(null));
        $this->assertSame('Fria', \App\Enums\LeadStatus::labelOf('fria'));
        $this->assertSame('x', \App\Enums\LeadStatus::labelOf('x'));
    }

    public function test_lead_labels_and_only_leads(): void
    {
        $owner = $this->backofficeUser('angariador', ['name' => 'Rita Angariadora']);
        Client::create(['name' => 'Lead sem estado', 'is_lead' => true, 'lead_source' => 'whatsapp', 'owner_id' => $owner->id]);
        Client::create(['name' => 'Lead contacto', 'is_lead' => true, 'lead_status' => 'em_contacto', 'lead_source' => 'estranha']);
        Client::create(['name' => 'Cliente', 'is_lead' => false]);

        $rows = $this->export(new LeadExport(), ['name', 'lead_status', 'lead_source', 'owner', 'form_proposals_count']);
        $byName = collect(array_slice($rows, 1))->mapWithKeys(fn ($r) => [$r[0]->getValue() => $r]);

        $this->assertCount(2, $byName);
        $this->assertSame(['Nova', 'WhatsApp', 'Rita Angariadora', 0], array_map(fn ($c) => $c->getValue(), array_slice($byName['Lead sem estado'], 1)));
        $this->assertSame('Em contacto', $byName['Lead contacto'][1]->getValue());
        $this->assertSame('estranha', $byName['Lead contacto'][2]->getValue());
    }

    public function test_filters_apply_and_large_sets_are_fully_exported(): void
    {
        foreach (range(1, 1100) as $i) {
            Client::create(['name' => "Cliente {$i}", 'is_lead' => false, 'client_type' => $i % 2 ? 'Particular' : 'Empresa']);
        }

        $all = $this->export(new ClientExport(), ['name']);
        $this->assertCount(1101, $all);

        $empresas = $this->export(new ClientExport(), ['name'], ['client_type' => 'EMPRESA']);
        $this->assertCount(551, $empresas);
    }

    public function test_excel_text_limit_is_respected(): void
    {
        Client::create(['name' => 'Longo', 'is_lead' => false, 'observation' => str_repeat('x', 40000)]);

        $rows = $this->export(new ClientExport(), ['observation']);
        $this->assertSame(32767, mb_strlen($rows[1][0]->getValue()));
    }
}
