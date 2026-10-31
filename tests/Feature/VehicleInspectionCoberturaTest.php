<?php

namespace Tests\Feature;

use App\Models\V3Vehicle;
use App\Models\VehicleInspection;
use App\Models\VehicleInspectionCategory;
use App\Models\VehicleInspectionEntry;
use App\Models\VehicleInspectionItem;
use App\Models\VehicleInspectionMedia;
use App\Models\VehicleInspectionTemplate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Cobertura do módulo de inspeções (testes de caracterização).
 * Nota: o template por omissão não é criado por nenhuma migration;
 * ensureDefaultTemplate() só faz where(is_default) e rebenta se não existir.
 */
class VehicleInspectionCoberturaTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private VehicleInspectionTemplate $template;

    private VehicleInspectionItem $itemOk;

    private VehicleInspectionItem $itemCritical;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Storage::fake('public');
        Storage::fake('local');

        $this->template = VehicleInspectionTemplate::create(['name' => 'Checklist', 'slug' => 'checklist', 'is_default' => true]);
        $category = VehicleInspectionCategory::create([
            'vehicle_inspection_template_id' => $this->template->id, 'name' => 'Motor', 'slug' => 'motor', 'sort_order' => 1,
        ]);
        $this->itemOk = VehicleInspectionItem::create([
            'vehicle_inspection_category_id' => $category->id, 'name' => 'Nível de óleo', 'slug' => 'oleo', 'sort_order' => 1,
        ]);
        $this->itemCritical = VehicleInspectionItem::create([
            'vehicle_inspection_category_id' => $category->id, 'name' => 'Correia de distribuição', 'slug' => 'correia', 'sort_order' => 2, 'is_critical' => true,
        ]);
    }

    private function inspection(array $attributes = []): VehicleInspection
    {
        $inspection = VehicleInspection::create($attributes + [
            'vehicle_inspection_template_id' => $this->template->id, 'status' => 'draft', 'brand' => 'BMW', 'model' => 'i4', 'fuel' => 'Gasolina',
        ]);
        foreach ([$this->itemOk, $this->itemCritical] as $item) {
            $inspection->entries()->create(['vehicle_inspection_item_id' => $item->id, 'status' => 'nao_verificado', 'priority' => 'baixa']);
        }

        return $inspection->fresh();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $inspection = $this->inspection();

        $this->get(route('admin.v3.inspections.index'))->assertRedirect(route('login'));
        $this->post(route('admin.v3.inspections.store'), [])->assertRedirect(route('login'));
        $this->post(route('admin.v3.inspections.convert', $inspection))->assertRedirect(route('login'));
        $this->assertSame(0, V3Vehicle::count());
    }

    public function test_angariador_is_blocked(): void
    {
        $inspection = $this->inspection();
        $angariador = $this->backofficeUser('angariador');

        $this->actingAs($angariador)->get(route('admin.v3.inspections.index'))->assertForbidden();
        $this->actingAs($angariador)->post(route('admin.v3.inspections.convert', $inspection))->assertForbidden();
        $this->assertNull($inspection->fresh()->v3_vehicle_id);
    }

    public function test_index_lists_and_filters_inspections_and_counts_only_originals(): void
    {
        $this->inspection(['brand' => 'Lancia', 'model' => 'Ypsilon', 'registration' => 'AA-11-BB']);
        $original = $this->inspection(['brand' => 'Subaru', 'model' => 'Forester', 'status' => 'completed', 'fuel' => 'Diesel']);
        $this->inspection(['brand' => 'Subaru', 'model' => 'Forester', 'parent_inspection_id' => $original->id]);

        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->get(route('admin.v3.inspections.index', ['search' => 'AA-11']))
            ->assertOk()->assertSee('Lancia')->assertDontSee('Subaru');

        $response = $this->actingAs($admin)->get(route('admin.v3.inspections.index', ['status' => 'completed']))
            ->assertOk()->assertSee('Subaru')->assertDontSee('Lancia');

        // As revisões (com parent) não entram nas estatísticas.
        $stats = $response->viewData('stats');
        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['draft']);
        $this->assertSame(1, $stats['completed']);

        $this->actingAs($admin)->get(route('admin.v3.inspections.index', ['fuel' => 'Diesel']))
            ->assertOk()->assertSee('Subaru')->assertDontSee('Lancia');
    }

    public function test_create_page_renders(): void
    {
        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v3.inspections.create'))
            ->assertOk();
    }

    public function test_store_creates_draft_with_one_entry_per_template_item(): void
    {
        $response = $this->actingAs($this->backofficeUser('admin'))->post(route('admin.v3.inspections.store'), [
            'brand' => 'Seat', 'model' => 'Leon', 'year' => 2020, 'fuel' => 'Diesel',
        ]);

        $inspection = VehicleInspection::firstOrFail();
        $response->assertRedirect(route('admin.v3.inspections.edit', $inspection));
        $this->assertSame('draft', $inspection->status);
        $this->assertSame('Seat', $inspection->brand);
        $this->assertSame($this->template->id, $inspection->vehicle_inspection_template_id);
        $this->assertSame(2, $inspection->entries()->count());
        // BUG conhecido (VehicleInspectionController::store/ensureEntries): o recalculate usa a relação
        // `entries` carregada ANTES de as entradas serem criadas, por isso o resumo guardado fica a zeros
        // (o correto seria unverified_items = 2).
        $this->assertSame(0, (int) $inspection->unverified_items);
    }

    public function test_store_validates_fields(): void
    {
        $this->actingAs($this->backofficeUser('admin'))
            ->post(route('admin.v3.inspections.store'), ['fuel' => 'Gás', 'year' => 1800, 'kilometers' => -5, 'template_id' => 9999])
            ->assertSessionHasErrors(['fuel', 'year', 'kilometers', 'template_id']);

        $this->assertSame(0, VehicleInspection::count());
    }

    public function test_update_saves_entries_and_recalculates_summary(): void
    {
        $inspection = $this->inspection();
        $entryOk = $inspection->entries()->where('vehicle_inspection_item_id', $this->itemOk->id)->first();
        $entryBad = $inspection->entries()->where('vehicle_inspection_item_id', $this->itemCritical->id)->first();

        $this->actingAs($this->backofficeUser('admin'))->put(route('admin.v3.inspections.update', $inspection), [
            'status' => 'in_progress',
            'entries' => [
                $entryOk->id => ['status' => 'ok'],
                $entryBad->id => ['status' => 'problema', 'priority' => 'alta', 'notes' => 'Ruído'],
            ],
        ])->assertRedirect();

        $inspection->refresh();
        $this->assertSame('in_progress', $inspection->status);
        $this->assertSame('Ruído', $entryBad->fresh()->notes);
        $this->assertSame('problema', $entryBad->fresh()->status);

        // BUG conhecido (VehicleInspectionController::update): o resumo persistido é calculado com as
        // entradas carregadas antes da alteração, ou seja, fica um "save" atrasado. Após esta
        // gravação o correto seria verified_items = 2.
        $this->assertSame(0, $inspection->verified_items);

        // Uma segunda gravação (sem alterações) já persiste os valores corretos.
        $this->actingAs($this->backofficeUser('admin'))->put(route('admin.v3.inspections.update', $inspection), [])->assertRedirect();
        $inspection->refresh();
        $this->assertSame(2, $inspection->verified_items);
        $this->assertSame(1, $inspection->ok_items);
        $this->assertSame(1, $inspection->problem_items);
        $this->assertSame(1, $inspection->critical_issues);
        $this->assertSame('Crítico', $inspection->inspection_result);
    }

    public function test_update_returns_json_summary_when_requested_and_ignores_foreign_entries(): void
    {
        $inspection = $this->inspection();
        $other = $this->inspection();
        $foreignEntry = $other->entries()->first();

        $this->actingAs($this->backofficeUser('admin'))->putJson(route('admin.v3.inspections.update', $inspection), [
            'entries' => [$foreignEntry->id => ['status' => 'ok']],
        ])->assertOk()->assertJsonPath('success', true)->assertJsonStructure(['summary' => ['percentage']]);

        $this->assertSame('nao_verificado', $foreignEntry->fresh()->status);
    }

    public function test_update_completed_sets_completed_at_and_rejects_invalid_status(): void
    {
        $inspection = $this->inspection();
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->put(route('admin.v3.inspections.update', $inspection), ['status' => 'xpto'])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)->put(route('admin.v3.inspections.update', $inspection), ['status' => 'completed'])->assertRedirect();
        $this->assertNotNull($inspection->fresh()->completed_at);
    }

    public function test_edit_redirects_to_report_when_completed_or_converted(): void
    {
        $admin = $this->backofficeUser('admin');
        $completed = $this->inspection(['status' => 'completed']);
        $draft = $this->inspection();

        $this->actingAs($admin)->get(route('admin.v3.inspections.edit', $completed))
            ->assertRedirect(route('admin.v3.inspections.report', $completed));
        $this->actingAs($admin)->get(route('admin.v3.inspections.edit', $draft))->assertOk();
    }

    public function test_report_shows_only_verified_items(): void
    {
        $inspection = $this->inspection(['status' => 'completed']);
        $inspection->entries()->where('vehicle_inspection_item_id', $this->itemOk->id)->update(['status' => 'ok']);

        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v3.inspections.report', $inspection))
            ->assertOk()
            ->assertSee('Nível de óleo')
            ->assertDontSee('Correia de distribuição');
    }

    public function test_duplicate_creates_linked_revision_copying_entries(): void
    {
        $inspection = $this->inspection(['status' => 'completed', 'vin' => 'WBA123']);
        $inspection->entries()->where('vehicle_inspection_item_id', $this->itemOk->id)->update(['status' => 'atencao', 'priority' => 'media', 'notes' => 'Baixo']);

        $this->actingAs($this->backofficeUser('admin'))
            ->post(route('admin.v3.inspections.duplicate', $inspection))
            ->assertRedirect();

        $revision = VehicleInspection::where('parent_inspection_id', $inspection->id)->firstOrFail();
        $this->assertSame('draft', $revision->status);
        $this->assertSame('WBA123', $revision->vin);
        $copied = $revision->entries()->where('vehicle_inspection_item_id', $this->itemOk->id)->first();
        $this->assertSame('atencao', $copied->status);
        $this->assertSame('Baixo', $copied->notes);
    }

    public function test_convert_creates_v3_vehicle_copies_media_and_marks_converted(): void
    {
        $inspection = $this->inspection(['year' => 2021, 'kilometers' => 50000, 'registration' => 'AB-12-CD']);
        $entry = $inspection->entries()->first();

        Storage::disk('public')->put('inspections/x/foto.jpg', 'img');
        Storage::disk('public')->put('inspections/x/video.mp4', 'vid');
        VehicleInspectionMedia::create(['vehicle_inspection_entry_id' => $entry->id, 'type' => 'image', 'path' => 'inspections/x/foto.jpg', 'original_name' => 'foto.jpg', 'sort_order' => 1]);
        VehicleInspectionMedia::create(['vehicle_inspection_entry_id' => $entry->id, 'type' => 'video', 'path' => 'inspections/x/video.mp4', 'original_name' => 'video.mp4', 'sort_order' => 2]);

        $response = $this->actingAs($this->backofficeUser('admin'))->post(route('admin.v3.inspections.convert', $inspection));

        $vehicle = V3Vehicle::firstOrFail();
        $response->assertRedirect(route('admin.v3.vehicles.edit', $vehicle->id));
        $this->assertSame('BMW', $vehicle->brand);
        $this->assertSame('em_stock', $vehicle->status);
        $this->assertFalse((bool) $vehicle->show_online);
        $this->assertSame($inspection->id, (int) $vehicle->generated_from_inspection_id);
        $this->assertCount(1, $vehicle->photos);
        $this->assertTrue((bool) $vehicle->photos->first()->is_cover);
        $this->assertCount(1, $vehicle->documents);
        $this->assertSame('inspection-video', $vehicle->documents->first()->tipo);

        $inspection->refresh();
        $this->assertSame('converted', $inspection->status);
        $this->assertSame($vehicle->id, $inspection->v3_vehicle_id);
        $this->assertNotNull($inspection->converted_at);
    }

    public function test_convert_twice_does_not_duplicate_vehicle(): void
    {
        $inspection = $this->inspection();
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->post(route('admin.v3.inspections.convert', $inspection));
        $this->actingAs($admin)->post(route('admin.v3.inspections.convert', $inspection->fresh()))->assertRedirect();

        $this->assertSame(1, V3Vehicle::count());
    }

    public function test_destroy_blocks_completed_original_but_allows_draft_and_revisions(): void
    {
        $admin = $this->backofficeUser('admin');
        $completed = $this->inspection(['status' => 'completed']);
        $draft = $this->inspection();
        $revision = $this->inspection(['status' => 'completed', 'parent_inspection_id' => $completed->id]);

        $this->actingAs($admin)->post(route('admin.v3.inspections.destroy', $completed))->assertSessionHas('error');
        $this->assertNotNull(VehicleInspection::find($completed->id));

        $this->actingAs($admin)->post(route('admin.v3.inspections.destroy', $draft))
            ->assertRedirect(route('admin.v3.inspections.index'));
        $this->assertNull(VehicleInspection::find($draft->id));
        $this->assertSame(0, VehicleInspectionEntry::where('vehicle_inspection_id', $draft->id)->count());

        $this->actingAs($admin)->post(route('admin.v3.inspections.destroy', $revision))->assertRedirect(route('admin.v3.inspections.index'));
        $this->assertNull(VehicleInspection::find($revision->id));
    }

    public function test_entry_media_upload_reorder_and_delete(): void
    {
        $admin = $this->backofficeUser('admin');
        $inspection = $this->inspection();
        $entry = $inspection->entries()->first();

        $this->actingAs($admin)->post(route('admin.v3.inspections.media.store', [$inspection, $entry]), [
            'files' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->create('b.mp4', 100, 'video/mp4')],
        ])->assertRedirect();

        $media = $entry->media()->orderBy('sort_order')->get();
        $this->assertCount(2, $media);
        $this->assertSame(['image', 'video'], $media->pluck('type')->all());
        Storage::disk('public')->assertExists($media[0]->path);

        $this->actingAs($admin)->postJson(route('admin.v3.inspections.media.reorder', [$inspection, $entry]), [
            'media_ids' => [$media[1]->id, $media[0]->id],
        ])->assertOk();
        $this->assertSame(1, $media[1]->fresh()->sort_order);

        $this->actingAs($admin)->delete(route('admin.v3.inspections.media.destroy', [$inspection, $entry, $media[0]]))->assertRedirect();
        $this->assertNull(VehicleInspectionMedia::find($media[0]->id));
        Storage::disk('public')->assertMissing($media[0]->path);
    }

    public function test_media_upload_requires_files_and_entry_must_belong_to_inspection(): void
    {
        $admin = $this->backofficeUser('admin');
        $inspection = $this->inspection();
        $foreignEntry = $this->inspection()->entries()->first();

        $this->actingAs($admin)->post(route('admin.v3.inspections.media.store', [$inspection, $inspection->entries()->first()]), [])
            ->assertSessionHasErrors('files');

        $this->actingAs($admin)->post(route('admin.v3.inspections.media.store', [$inspection, $foreignEntry]), [
            'files' => [UploadedFile::fake()->image('a.jpg')],
        ])->assertNotFound();
        $this->assertSame(0, VehicleInspectionMedia::count());
    }

    public function test_general_media_upload_and_delete(): void
    {
        $admin = $this->backofficeUser('admin');
        $inspection = $this->inspection();

        $this->actingAs($admin)->post(route('admin.v3.inspections.general-media.store', $inspection), [
            'files' => [UploadedFile::fake()->image('geral.jpg')],
        ])->assertRedirect();

        $media = VehicleInspectionMedia::where('vehicle_inspection_id', $inspection->id)->firstOrFail();
        $this->assertNull($media->vehicle_inspection_entry_id);

        $this->actingAs($admin)->delete(route('admin.v3.inspections.general-media.destroy', [$inspection, $media]))->assertRedirect();
        $this->assertNull(VehicleInspectionMedia::find($media->id));
    }

    /**
     * BUG conhecido: update() aceita editar uma inspeção concluída/convertida
     * (só o GET edit redireciona para o relatório). Documenta o comportamento atual.
     */
    public function test_update_still_edits_completed_inspection_known_bug(): void
    {
        $inspection = $this->inspection(['status' => 'completed']);

        $this->actingAs($this->backofficeUser('admin'))
            ->put(route('admin.v3.inspections.update', $inspection), ['brand' => 'Alterada'])
            ->assertRedirect();

        $this->assertSame('Alterada', $inspection->fresh()->brand);
    }
}
