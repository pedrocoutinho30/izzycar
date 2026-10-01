<?php

namespace Tests\Feature;

use App\Models\ConvertedProposal;
use App\Services\AnnualReportService;
use App\Services\QuarterlyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ReportImportsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        // 3.º trimestre: duas ativas e uma cancelada; 2.º trimestre: uma.
        $this->converted('2026-09-11', 'Transporte', 32979, 615, 1097.50);
        $this->converted('2026-09-30', 'Iniciada', 22750, 615, 997.50);
        $this->converted('2026-09-08', 'Cancelado', 20990, 615, 1097.50);
        $this->converted('2026-05-20', 'Entrega', 18000, 500, 900);
    }

    private function converted(string $date, string $status, float $car, float $commission, float $tranche): void
    {
        $proposal = ConvertedProposal::create([
            'status' => $status,
            'valor_carro' => $car,
            'valor_comissao' => $commission,
            'valor_primeira_tranche' => $tranche,
            'valor_segunda_tranche' => $tranche,
        ]);
        $proposal->forceFill(['created_at' => Carbon::parse($date . ' 12:00')])->saveQuietly();
    }

    public function test_quarterly_report_counts_converted_proposals_without_cancelled(): void
    {
        $data = app(QuarterlyReportService::class)->generate(Carbon::parse('2026-09-30'));
        $q3 = $data['current'];

        $this->assertSame(2, $q3['imports_count']);
        $this->assertSame(55729.0, $q3['imports_car_value']);
        $this->assertSame(1230.0, $q3['imports_commission']);
        $this->assertSame(4190.0, $q3['imports_billed']);
        $this->assertSame(2, $q3['deals_total']);
        $this->assertSame(1230.0, $q3['total_margin']);

        $this->assertSame(1, $data['prev_quarter']['imports_count']);
        $this->assertSame(500.0, $data['prev_quarter']['imports_commission']);
        $this->assertSame(0, $data['same_last_year']['imports_count']);
    }

    public function test_annual_report_includes_imports_per_month(): void
    {
        $data = app(AnnualReportService::class)->generate(2026);

        $this->assertSame(3, $data['current']['imports_count']);
        $this->assertSame(1730.0, $data['current']['imports_commission']);
        $this->assertSame(2, $data['monthly_breakdown'][9]['imports_count']);
        $this->assertSame(1, $data['monthly_breakdown'][5]['imports_count']);
    }

    public function test_reports_render_the_imports_section(): void
    {
        $quarterly = app(QuarterlyReportService::class)->generate(Carbon::parse('2026-09-30'));
        $annual = app(AnnualReportService::class)->generate(2026);

        $html = view('pdf.quarterly-report', $quarterly)->render();
        $this->assertStringContainsString('Cotações convertidas', $html);
        $this->assertStringContainsString('1.230 €', $html);
        $this->assertStringContainsString('Negócios fechados (vendas + importações)', $html);
        $this->assertStringContainsString('55.729 €', $html);

        $html = view('pdf.annual-report', $annual)->render();
        $this->assertStringContainsString('Comissões de importação', $html);
        $this->assertStringContainsString('1.730 €', $html);

        $this->assertStringContainsString('Importações', view('emails.quarterly-report', ['data' => $quarterly])->render());
        $this->assertStringContainsString('Importações', view('emails.annual-report', ['data' => $annual])->render());

        // O DomPDF consegue gerar os dois PDFs.
        $this->assertStringStartsWith('%PDF', Pdf::loadView('pdf.quarterly-report', $quarterly)->setPaper('a4', 'portrait')->output());
        $this->assertStringStartsWith('%PDF', Pdf::loadView('pdf.annual-report', $annual)->setPaper('a4', 'portrait')->output());
    }
}
