<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\AngariadorContractService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class AngariadorContractTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['name' => 'Izzycar', 'vat_number' => '999999999', 'phone' => '928459346', 'email' => 'geral@izzycar.pt'] as $label => $value) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }
    }

    public function test_admin_downloads_the_angariador_contract_as_pdf(): void
    {
        $angariador = $this->backofficeUser('angariador', [
            'name' => 'Rita', 'nif' => '123456789', 'iban' => 'PT50000201231234567890154',
            'location' => 'Porto', 'commission_fixed_value' => 100,
        ]);

        $response = $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v2.angariadores.contract', $angariador->id))
            ->assertOk();

        $this->assertStringStartsWith('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_user_without_the_angariador_role_gets_404(): void
    {
        $outro = $this->backofficeUser('admin');

        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v2.angariadores.contract', $outro->id))
            ->assertNotFound();
    }

    public function test_contract_is_generated_even_without_nif_iban_or_commission(): void
    {
        $angariador = $this->backofficeUser('angariador', ['nif' => null, 'iban' => null, 'location' => null, 'commission_fixed_value' => null]);

        $this->assertSame(
            ['nif' => 'NIF', 'iban' => 'IBAN', 'location' => 'morada', 'commission_fixed_value' => 'comissão'],
            AngariadorContractService::camposEmFalta($angariador)
        );

        $this->assertStringStartsWith('%PDF', AngariadorContractService::generatePdf($angariador));
    }

    public function test_missing_fields_are_empty_when_the_profile_is_complete(): void
    {
        $angariador = $this->backofficeUser('angariador', [
            'nif' => '123456789', 'iban' => 'PT50', 'location' => 'Porto', 'commission_fixed_value' => 100,
        ]);

        $this->assertSame([], AngariadorContractService::camposEmFalta($angariador));
    }

    public function test_the_angariador_page_shows_the_contract_button(): void
    {
        $angariador = $this->backofficeUser('angariador');

        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v2.angariadores.show', $angariador->id))
            ->assertOk()
            ->assertSee(route('admin.v2.angariadores.contract', $angariador->id), false)
            ->assertSee('Descarregar contrato');
    }

    public function test_admin_uploads_the_signed_contract_and_the_angariador_sees_it(): void
    {
        Storage::fake('local');
        $angariador = $this->backofficeUser('angariador');

        $this->actingAs($this->backofficeUser('admin'))
            ->post(route('admin.v2.angariadores.contract.signed.store', $angariador->id), [
                'contrato_assinado' => UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $angariador->refresh();
        $this->assertNotNull($angariador->contrato_assinado_path);
        $this->assertNotNull($angariador->contrato_assinado_em);
        Storage::disk('local')->assertExists($angariador->contrato_assinado_path);

        $this->actingAs($angariador)->get(route('admin.angariador.dashboard'))
            ->assertOk()->assertSee('Ver o meu contrato');
        $this->actingAs($angariador)->get(route('admin.angariador.contrato'))->assertOk();
        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v2.angariadores.contract.signed', $angariador->id))->assertOk();
    }

    public function test_angariador_without_a_signed_contract_gets_404_and_a_notice(): void
    {
        $angariador = $this->backofficeUser('angariador');

        $this->actingAs($angariador)->get(route('admin.angariador.contrato'))->assertNotFound();
        $this->actingAs($angariador)->get(route('admin.angariador.dashboard'))
            ->assertOk()->assertSee('ainda não foi carregado');
    }

    public function test_only_pdf_or_images_are_accepted_as_signed_contract(): void
    {
        Storage::fake('local');
        $angariador = $this->backofficeUser('angariador');

        $this->actingAs($this->backofficeUser('admin'))
            ->post(route('admin.v2.angariadores.contract.signed.store', $angariador->id), [
                'contrato_assinado' => UploadedFile::fake()->create('contrato.exe', 10, 'application/octet-stream'),
            ])
            ->assertSessionHasErrors('contrato_assinado');

        $this->assertNull($angariador->refresh()->contrato_assinado_path);
    }

    public function test_replacing_and_removing_the_signed_contract_deletes_the_old_file(): void
    {
        Storage::fake('local');
        $angariador = $this->backofficeUser('angariador');
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->post(route('admin.v2.angariadores.contract.signed.store', $angariador->id), [
            'contrato_assinado' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
        ]);
        $primeiro = $angariador->refresh()->contrato_assinado_path;

        $this->actingAs($admin)->post(route('admin.v2.angariadores.contract.signed.store', $angariador->id), [
            'contrato_assinado' => UploadedFile::fake()->create('b.pdf', 10, 'application/pdf'),
        ]);
        $segundo = $angariador->refresh()->contrato_assinado_path;

        Storage::disk('local')->assertMissing($primeiro);
        Storage::disk('local')->assertExists($segundo);

        $this->actingAs($admin)->delete(route('admin.v2.angariadores.contract.signed.destroy', $angariador->id))->assertRedirect();

        Storage::disk('local')->assertMissing($segundo);
        $this->assertNull($angariador->refresh()->contrato_assinado_path);
    }

    public function test_signed_contract_routes_give_404_for_users_without_the_angariador_role(): void
    {
        $outro = $this->backofficeUser('admin');
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->get(route('admin.v2.angariadores.contract.signed', $outro->id))->assertNotFound();
        $this->actingAs($admin)->post(route('admin.v2.angariadores.contract.signed.store', $outro->id), [
            'contrato_assinado' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'),
        ])->assertNotFound();
    }

    public function test_the_contract_uses_the_same_provider_signature_as_the_quote_contract(): void
    {
        Storage::fake('public');
        $angariador = $this->backofficeUser('angariador');

        $semAssinatura = strlen(AngariadorContractService::generatePdf($angariador));

        // Imagem real: um PNG 1x1 grande o bastante para pesar no PDF.
        $img = imagecreatetruecolor(600, 200);
        ob_start();
        imagepng($img);
        Storage::disk('public')->put('settings/assinatura.png', ob_get_clean());
        Setting::create(['title' => 'assinatura_prestador', 'label' => 'assinatura_prestador', 'type' => 'image', 'value' => 'settings/assinatura.png']);

        $comAssinatura = strlen(AngariadorContractService::generatePdf($angariador));

        $this->assertGreaterThan($semAssinatura, $comAssinatura);
    }

    public function test_contract_includes_the_brand_prospecting_liability_and_expenses_rules(): void
    {
        $angariador = $this->backofficeUser('angariador', ['commission_fixed_value' => 100]);

        $html = view('pdf.contract_angariador', AngariadorContractService::viewData($angariador))->render();
        $texto = preg_replace('/\s+/', ' ', strip_tags($html));

        // As secções 1 a 9 mantêm a numeração (a adenda dos contratos antigos remete para as secções 3, 6 e 9).
        foreach ([
            '3. Natureza da Relação', '6. Comissão', '9. Duração e Cessação',
            '10. Uso do Nome, Marca e Domínio Izzycar', '11. Prospeção e Comunicações Comerciais',
            '12. Responsabilidade', '13. Despesas', '14. Lei Aplicável e Resolução de Litígios', '15. Assinaturas',
        ] as $titulo) {
            $this->assertStringContainsString($titulo, $texto);
        }

        $this->assertStringContainsString('angariador independente da Izzycar', $texto);
        $this->assertStringContainsString('Lei n.º 41/2004', $texto);
        $this->assertStringContainsString('não são reembolsadas', $texto);
        $this->assertStringContainsString('única remuneração', $texto);
        $this->assertStringContainsString('Não conferem direito a comissão as leads obtidas através de comunicações ou meios que violem as secções 10 e 11', $texto);
        $this->assertStringContainsString('O incumprimento das secções 10, 11 ou 12 constitui incumprimento grave', $texto);
        $this->assertStringContainsString('deixa de imediato de usar o nome Izzycar', $texto);
    }
}
