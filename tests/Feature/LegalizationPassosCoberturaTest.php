<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Legalization;
use App\Models\LegalizationDocument;
use App\Models\Task;
use App\Models\V3Vehicle;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * LegalizationController: passos (toggleStep), tarefa do Modelo 9, CRUD, documentos e fatura.
 * O kanban (move) já tem testes em LegalizationKanbanTest.
 */
class LegalizationPassosCoberturaTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Storage::fake('local');
    }

    private function legalization(array $over = []): Legalization
    {
        return Legalization::create($over + ['marca' => 'BMW', 'modelo' => 'i4', 'combustivel' => 'Gasolina', 'steps_completed' => []]);
    }

    private function admin()
    {
        return $this->actingAs($this->backofficeUser('admin'));
    }

    private function toggle(Legalization $l, int $step)
    {
        return $this->admin()->postJson(route('admin.legalizations.toggle-step', $l), ['step' => $step]);
    }

    public function test_guest_is_redirected_and_angariador_is_blocked(): void
    {
        $l = $this->legalization();

        $this->postJson(route('admin.legalizations.toggle-step', $l), ['step' => 1])->assertUnauthorized();
        $this->get(route('admin.legalizations.index'))->assertRedirect(route('login'));
        $this->delete(route('admin.legalizations.destroy', $l))->assertRedirect(route('login'));

        $angariador = $this->backofficeUser('angariador');
        $this->actingAs($angariador)->get(route('admin.legalizations.index'))->assertForbidden();
        $this->actingAs($angariador)->post(route('admin.legalizations.toggle-step', $l), ['step' => 1])->assertForbidden();
        $this->actingAs($angariador)->delete(route('admin.legalizations.destroy', $l))->assertForbidden();

        $this->assertSame([], $l->fresh()->steps_completed);
        $this->assertNotNull(Legalization::find($l->id));
    }

    public function test_toggle_step_advances_in_order_and_reports_progress(): void
    {
        $l = $this->legalization();

        $this->toggle($l, 1)->assertOk()->assertJsonPath('completed', [1])->assertJsonPath('task_created', false);
        $this->toggle($l, 2)->assertOk()->assertJsonPath('completed', [1, 2]);

        $this->assertSame([1, 2], $l->fresh()->steps_completed);
        $this->assertSame(3, $l->fresh()->currentStep());
    }

    public function test_toggle_step_rejects_skipping_a_step_and_invalid_numbers(): void
    {
        $l = $this->legalization(['steps_completed' => [1]]);

        $this->toggle($l, 3)->assertStatus(422)->assertJsonPath('error', 'Conclui o passo 2 antes de avançar.');
        $this->assertSame([1], $l->fresh()->steps_completed);

        $this->toggle($l, 0)->assertStatus(422)->assertJsonValidationErrors('step');
        $this->toggle($l, 8)->assertStatus(422)->assertJsonValidationErrors('step');
        $this->admin()->postJson(route('admin.legalizations.toggle-step', $l), [])->assertStatus(422);
    }

    public function test_toggle_step_again_unchecks_it(): void
    {
        $l = $this->legalization(['steps_completed' => [1, 2]]);

        $this->toggle($l, 2)->assertOk()->assertJsonPath('completed', [1]);
        $this->assertSame([1], $l->fresh()->steps_completed);
    }

    /**
     * BUG conhecido (LegalizationController::toggleStep, ~linha 130): a regra "N-1" só é validada ao
     * concluir. Desmarcar um passo intermédio deixa os seguintes concluídos ([1,3]), um estado que o
     * fluxo normal nunca produz (e o kanban recalcula como 1..N-1).
     */
    public function test_unchecking_intermediate_step_keeps_later_ones_known_bug(): void
    {
        $l = $this->legalization(['steps_completed' => [1, 2, 3]]);

        $this->toggle($l, 2)->assertOk()->assertJsonPath('completed', [1, 3]);
    }

    public function test_completing_step_five_creates_modelo9_task_once(): void
    {
        $client = Client::create(['name' => 'Rita Cliente', 'is_lead' => false, 'lead_status' => 'nova']);
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4']);
        $l = $this->legalization(['steps_completed' => [1, 2, 3, 4], 'client_id' => $client->id, 'v3_vehicle_id' => $vehicle->id, 'matricula' => 'AA-11-BB']);

        $this->toggle($l, 5)->assertOk()->assertJsonPath('task_created', true);

        $task = Task::sole();
        $this->assertSame('Entregar Modelo 9 IMT — BMW i4', $task->title);
        $this->assertSame('pendente', $task->status);
        $this->assertSame($vehicle->id, (int) $task->v3_vehicle_id);
        $this->assertStringContainsString('Rita Cliente', $task->description);
        $this->assertStringContainsString('AA-11-BB', $task->description);
        $this->assertTrue($task->due_date->isSameDay(now()->addDays(30)));
        $this->assertTrue($task->reminder_date->isSameDay(now()->addDays(15)));

        // Desmarcar e voltar a marcar o passo 5 não duplica a tarefa em aberto.
        $this->toggle($l, 5)->assertOk()->assertJsonPath('task_created', false);
        $this->toggle($l, 5)->assertOk()->assertJsonPath('task_created', false);
        $this->assertSame(1, Task::count());
    }

    public function test_modelo9_task_is_recreated_when_previous_one_is_closed_and_is_per_process(): void
    {
        $l = $this->legalization(['steps_completed' => [1, 2, 3, 4]]);
        $outra = $this->legalization(['steps_completed' => [1, 2, 3, 4]]);

        $this->toggle($l, 5)->assertJsonPath('task_created', true);
        $this->toggle($outra, 5)->assertJsonPath('task_created', true); // outro processo: tarefa própria
        $this->assertSame(2, Task::count());

        Task::query()->update(['status' => 'concluida']);
        $this->toggle($l, 5); // desmarca
        $this->toggle($l, 5)->assertJsonPath('task_created', true); // tarefa anterior já fechada
        $this->assertSame(3, Task::count());
    }

    public function test_task_is_created_without_client_or_registration(): void
    {
        $l = $this->legalization(['steps_completed' => [1, 2, 3, 4]]);

        $this->toggle($l, 5)->assertOk();

        $descricao = Task::sole()->description;
        $this->assertStringContainsString('Cliente sem nome', $descricao);
        $this->assertStringContainsString('matrícula não registada', $descricao);
    }

    /**
     * O separador Legalização da viatura V3 também cria a tarefa do Modelo 9 ao concluir o passo 5.
     * (Era um bug: V3VehicleController::toggleLegalizationStep não chamava LegalizationTaskService;
     * corrigido por alteração em curso no código, por isso este teste afirma já o comportamento correto.)
     */
    public function test_step_five_from_v3_vehicle_tab_creates_the_task_once(): void
    {
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4']);
        $l = $this->legalization(['steps_completed' => [1, 2, 3, 4], 'v3_vehicle_id' => $vehicle->id]);
        $url = route('admin.v3.vehicles.legalization.toggle-step', $vehicle->id);

        $this->admin()->postJson($url, ['step' => 5])->assertOk();

        $this->assertContains(5, $l->fresh()->steps_completed);
        $this->assertSame(1, Task::count());
        $this->assertSame($vehicle->id, (int) Task::sole()->v3_vehicle_id);

        // Desmarcar e voltar a marcar não duplica.
        $this->admin()->postJson($url, ['step' => 5])->assertOk();
        $this->admin()->postJson($url, ['step' => 5])->assertOk();
        $this->assertSame(1, Task::count());
    }

    public function test_store_requires_vehicle_data_without_v3_vehicle_and_copies_from_vehicle(): void
    {
        $this->admin()->post(route('admin.legalizations.store'), [])->assertSessionHasErrors(['marca', 'modelo', 'combustivel']);

        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Audi', 'model' => 'A4', 'fuel' => 'Diesel', 'registration' => 'ZZ-99-ZZ']);
        $this->admin()->post(route('admin.legalizations.store'), ['v3_vehicle_id' => $vehicle->id, 'marca' => 'Ignorada'])->assertRedirect();

        $l = Legalization::sole();
        $this->assertSame('Audi', $l->marca);
        $this->assertSame('A4', $l->modelo);
        $this->assertSame('Diesel', $l->combustivel);
        $this->assertSame('ZZ-99-ZZ', $l->matricula);
        $this->assertFalse((bool) $l->regime_especial_isv);
    }

    public function test_store_with_manual_data_and_show_page(): void
    {
        $this->admin()->post(route('admin.legalizations.store'), ['marca' => 'Fiat', 'modelo' => 'Panda', 'combustivel' => 'Gasolina', 'matricula' => 'AB-12-CD'])
            ->assertRedirect();
        $l = Legalization::sole();

        $this->admin()->get(route('admin.legalizations.show', $l))->assertOk()->assertSee('Fiat')->assertSee('Panda');
        $this->admin()->get(route('admin.legalizations.edit', $l))->assertOk();
        $this->admin()->get(route('admin.legalizations.create'))->assertOk();
    }

    public function test_update_saves_modelo9_extra_fields_and_syncs_from_vehicle(): void
    {
        $l = $this->legalization();
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Audi', 'model' => 'A4', 'fuel' => 'Diesel']);

        $this->admin()->put(route('admin.legalizations.update', $l), ['v3_vehicle_id' => $vehicle->id, 'notas' => 'nota'])
            ->assertRedirect(route('admin.legalizations.show', $l));

        $l->refresh();
        $this->assertSame('Audi', $l->marca);
        $this->assertSame($vehicle->id, $l->v3_vehicle_id);
        $this->assertSame('nota', $l->notas);
        $this->assertIsArray($l->modelo9_dados);
    }

    public function test_destroy_removes_record_and_document_files(): void
    {
        $l = $this->legalization();
        Storage::disk('local')->put("legalizations/{$l->id}/a.pdf", 'x');
        LegalizationDocument::create(['legalization_id' => $l->id, 'tipo' => array_key_first(Legalization::DOCUMENTOS), 'nome_original' => 'a.pdf', 'caminho' => "legalizations/{$l->id}/a.pdf"]);

        $this->admin()->delete(route('admin.legalizations.destroy', $l))->assertRedirect(route('admin.legalizations.index'));

        $this->assertNull(Legalization::find($l->id));
        Storage::disk('local')->assertMissing("legalizations/{$l->id}/a.pdf");
    }

    public function test_document_upload_replace_download_delete_and_ownership(): void
    {
        $l = $this->legalization();
        $outra = $this->legalization();
        $tipo = array_key_first(Legalization::DOCUMENTOS);

        $this->admin()->post(route('admin.legalizations.upload-document', $l), ['tipo' => $tipo, 'ficheiro' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])->assertSessionHas('success');
        $primeiro = LegalizationDocument::sole();
        $this->admin()->post(route('admin.legalizations.upload-document', $l), ['tipo' => $tipo, 'ficheiro' => UploadedFile::fake()->create('b.pdf', 10, 'application/pdf')]);

        $this->assertSame(1, LegalizationDocument::count());
        $doc = LegalizationDocument::sole();
        $this->assertSame('b.pdf', $doc->nome_original);
        Storage::disk('local')->assertMissing($primeiro->caminho);
        Storage::disk('local')->assertExists($doc->caminho);

        $this->admin()->get(route('admin.legalizations.download-document', [$l, $doc]))->assertOk()->assertDownload('b.pdf');

        // Documento de outro processo: 403.
        $this->admin()->get(route('admin.legalizations.download-document', [$outra, $doc]))->assertForbidden();
        $this->admin()->delete(route('admin.legalizations.delete-document', [$outra, $doc]))->assertForbidden();
        $this->assertNotNull(LegalizationDocument::find($doc->id));

        $this->admin()->delete(route('admin.legalizations.delete-document', [$l, $doc]))->assertSessionHas('success');
        $this->assertSame(0, LegalizationDocument::count());
    }

    public function test_document_upload_validations(): void
    {
        $l = $this->legalization();
        $tipo = array_key_first(Legalization::DOCUMENTOS);
        $url = route('admin.legalizations.upload-document', $l);

        $this->admin()->post($url, [])->assertSessionHasErrors(['tipo', 'ficheiro']);
        $this->admin()->post($url, ['tipo' => 'inventado', 'ficheiro' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])->assertSessionHasErrors('tipo');
        $this->admin()->post($url, ['tipo' => $tipo, 'ficheiro' => UploadedFile::fake()->create('a.exe', 10, 'application/octet-stream')])->assertSessionHasErrors('ficheiro');
        $this->admin()->post($url, ['tipo' => $tipo, 'ficheiro' => UploadedFile::fake()->create('a.pdf', 11000, 'application/pdf')])->assertSessionHasErrors('ficheiro');
        $this->assertSame(0, LegalizationDocument::count());
    }

    public function test_regime_especial_document_is_accepted_and_listed_when_regime_is_active(): void
    {
        $l = $this->legalization(['regime_especial_isv' => true]);
        $tipoEspecial = array_key_first(Legalization::DOCUMENTOS_REGIME_ESPECIAL);

        $this->admin()->post(route('admin.legalizations.upload-document', $l), ['tipo' => $tipoEspecial, 'ficheiro' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, LegalizationDocument::count());
        $this->assertArrayHasKey($tipoEspecial, $l->fresh()->allDocumentos());
    }

    public function test_invoice_upload_replaces_previous_download_and_delete(): void
    {
        $l = $this->legalization();

        $this->admin()->get(route('admin.legalizations.download-invoice', $l))->assertNotFound();

        $this->admin()->post(route('admin.legalizations.upload-invoice', $l), ['invoice' => UploadedFile::fake()->create('f1.pdf', 10, 'application/pdf')])->assertSessionHas('success');
        $primeira = $l->fresh()->invoice_path;
        Storage::disk('local')->assertExists($primeira);

        $this->admin()->post(route('admin.legalizations.upload-invoice', $l), ['invoice' => UploadedFile::fake()->create('f2.pdf', 10, 'application/pdf')]);
        $segunda = $l->fresh()->invoice_path;
        $this->assertNotSame($primeira, $segunda);
        Storage::disk('local')->assertMissing($primeira);

        $this->admin()->get(route('admin.legalizations.download-invoice', $l))->assertOk()->assertDownload('fatura_' . $l->id . '.pdf');

        $this->admin()->delete(route('admin.legalizations.delete-invoice', $l))->assertSessionHas('success');
        $this->assertNull($l->fresh()->invoice_path);
        Storage::disk('local')->assertMissing($segunda);

        $this->admin()->post(route('admin.legalizations.upload-invoice', $l), [])->assertSessionHasErrors('invoice');
        $this->admin()->post(route('admin.legalizations.upload-invoice', $l), ['invoice' => UploadedFile::fake()->create('f.docx', 10)])->assertSessionHasErrors('invoice');
    }

    public function test_index_filters_by_state(): void
    {
        $this->legalization(['marca' => 'Nova', 'modelo' => 'Zero']);
        $this->legalization(['marca' => 'Feita', 'modelo' => 'Sete', 'steps_completed' => [1, 2, 3, 4, 5, 6, 7]]);

        $this->admin()->get(route('admin.legalizations.index', ['estado' => 'concluida']))->assertOk()->assertSee('Feita')->assertDontSee('Zero');
        $this->admin()->get(route('admin.legalizations.index', ['estado' => 'nova']))->assertOk()->assertSee('Zero')->assertDontSee('Sete');
    }
}
