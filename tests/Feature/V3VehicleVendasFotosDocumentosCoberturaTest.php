<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Legalization;
use App\Models\LegalizationDocument;
use App\Models\Sale;
use App\Models\V3Vehicle;
use App\Models\V3VehicleDocument;
use App\Models\V3VehiclePhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Vendas V3 (listagem, anular), fotos, documentos e separador de legalização da viatura V3.
 */
class V3VehicleVendasFotosDocumentosCoberturaTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private V3Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Storage::fake('local');
        Storage::fake('public');

        $this->vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Volvo', 'model' => 'XC40', 'status' => 'em_stock']);
    }

    private function admin()
    {
        return $this->actingAs($this->backofficeUser('admin'));
    }

    private function sell(?V3Vehicle $vehicle = null): Sale
    {
        $vehicle ??= $this->vehicle;
        $client = Client::first() ?? Client::create(['name' => 'Comprador Teste', 'is_lead' => false, 'lead_status' => 'nova']);
        $sale = Sale::create([
            'v3_vehicle_id' => $vehicle->id, 'client_id' => $client->id, 'sale_date' => '2026-10-10', 'sale_price' => 20000, 'vat_rate' => 23, 'payment_method' => 'transferencia',
        ]);
        $vehicle->update(['status' => 'vendido']);

        return $sale;
    }

    private function photo(array $attributes = [], ?V3Vehicle $vehicle = null): V3VehiclePhoto
    {
        $vehicle ??= $this->vehicle;
        static $n = 0;
        $n++;
        Storage::disk('public')->put("v3-vehicles/{$vehicle->id}/photos/f{$n}.jpg", 'x');

        return V3VehiclePhoto::create($attributes + [
            'v3_vehicle_id' => $vehicle->id, 'path' => "v3-vehicles/{$vehicle->id}/photos/f{$n}.jpg", 'order_position' => $n, 'is_cover' => false,
        ]);
    }

    // ── Acesso ───────────────────────────────────────────────────────────────

    public function test_guest_is_redirected_to_login_on_v3_actions(): void
    {
        $photo = $this->photo();

        $this->post(route('admin.v3.vehicles.sale.store', $this->vehicle->id), [])->assertRedirect(route('login'));
        $this->delete(route('admin.v3.vehicles.sale.destroy', [$this->vehicle->id, 1]))->assertRedirect(route('login'));
        $this->post(route('admin.v3.vehicles.photos.upload', $this->vehicle->id), [])->assertRedirect(route('login'));
        $this->delete(route('admin.v3.vehicles.photos.destroy', [$this->vehicle->id, $photo->id]))->assertRedirect(route('login'));
        $this->post(route('admin.v3.vehicles.documents.upload', $this->vehicle->id), [])->assertRedirect(route('login'));
        $this->delete(route('admin.v3.vehicles.destroy', $this->vehicle->id))->assertRedirect(route('login'));
        $this->get(route('admin.v2.sales.index'))->assertRedirect(route('login'));

        $this->assertNotNull(V3VehiclePhoto::find($photo->id));
        $this->assertNotNull(V3Vehicle::find($this->vehicle->id));
    }

    public function test_angariador_is_blocked_on_v3_actions(): void
    {
        $sale = $this->sell();
        $user = $this->backofficeUser('angariador');

        $this->actingAs($user)->delete(route('admin.v3.vehicles.sale.destroy', [$this->vehicle->id, $sale->id]))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.v3.vehicles.destroy', $this->vehicle->id))->assertForbidden();
        $this->actingAs($user)->get(route('admin.v2.sales.index'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.v3.vehicles.photos.upload', $this->vehicle->id), [])->assertForbidden();

        $this->assertSame('vendido', $this->vehicle->fresh()->status);
        $this->assertNotNull(Sale::find($sale->id));
    }

    // ── Vendas ───────────────────────────────────────────────────────────────

    public function test_sales_index_lists_v3_sale_with_vehicle_and_client(): void
    {
        $this->sell();

        $this->admin()->get(route('admin.v2.sales.index'))
            ->assertOk()
            ->assertSee('Volvo XC40')
            ->assertSee('Comprador Teste')
            ->assertSee($this->vehicle->reference);
    }

    public function test_sales_index_filters_by_client_name_date_and_payment_method(): void
    {
        $this->sell();
        $outro = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Saab', 'model' => '9-3', 'status' => 'em_stock']);
        $cliente = Client::create(['name' => 'Outra Pessoa', 'is_lead' => false, 'lead_status' => 'nova']);
        Sale::create(['v3_vehicle_id' => $outro->id, 'client_id' => $cliente->id, 'sale_date' => '2025-01-05', 'sale_price' => 5000, 'vat_rate' => 23, 'payment_method' => 'dinheiro']);

        $this->admin()->get(route('admin.v2.sales.index', ['search' => 'Outra']))->assertOk()->assertSee('Saab 9-3')->assertDontSee('Volvo XC40');
        $this->admin()->get(route('admin.v2.sales.index', ['date_from' => '2026-01-01']))->assertOk()->assertSee('Volvo XC40')->assertDontSee('Saab 9-3');
        $this->admin()->get(route('admin.v2.sales.index', ['payment_method' => 'dinheiro']))->assertOk()->assertSee('Saab 9-3')->assertDontSee('Volvo XC40');
    }

    /**
     * BUG conhecido (SaleV2Controller::index, ~linha 42): a pesquisa por viatura só olha para a relação
     * legada `vehicle`, pelo que pesquisar a marca/referência de uma viatura V3 não devolve nada.
     * O correto seria encontrar a venda. Documenta o comportamento atual.
     */
    public function test_sales_search_by_v3_vehicle_brand_finds_nothing_known_bug(): void
    {
        $this->sell();

        $this->admin()->get(route('admin.v2.sales.index', ['search' => 'Volvo']))
            ->assertOk()
            ->assertDontSee('Volvo XC40');
    }

    public function test_destroy_sale_restores_vehicle_to_stock_and_removes_sale(): void
    {
        $sale = $this->sell();

        $this->admin()->delete(route('admin.v3.vehicles.sale.destroy', [$this->vehicle->id, $sale->id]))
            ->assertSessionHas('success');

        $this->assertNull(Sale::find($sale->id));
        $this->assertSame('em_stock', $this->vehicle->fresh()->status);
    }

    public function test_destroy_sale_of_another_vehicle_is_404_and_changes_nothing(): void
    {
        $sale = $this->sell();
        $outro = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Saab', 'model' => '9-3', 'status' => 'reservado']);

        $this->admin()->delete(route('admin.v3.vehicles.sale.destroy', [$outro->id, $sale->id]))->assertNotFound();

        $this->assertNotNull(Sale::find($sale->id));
        $this->assertSame('reservado', $outro->fresh()->status);
        $this->assertSame('vendido', $this->vehicle->fresh()->status);
    }

    /**
     * BUG conhecido (SaleV2Controller::destroy, ~linha 235): eliminar a venda pela listagem não devolve
     * a viatura V3 a stock (destroySale no V3VehicleController faz-o). A viatura fica "vendido" sem venda.
     */
    public function test_deleting_sale_from_list_leaves_vehicle_sold_known_bug(): void
    {
        $sale = $this->sell();

        $this->admin()->delete(route('admin.v2.sales.destroy', $sale->id))->assertRedirect(route('admin.v2.sales.index'));

        $this->assertNull(Sale::find($sale->id));
        $this->assertSame('vendido', $this->vehicle->fresh()->status); // o correto seria 'em_stock'
    }

    public function test_sale_without_price_is_rejected_and_zero_price_is_accepted(): void
    {
        $client = Client::create(['name' => 'Maria', 'is_lead' => false, 'lead_status' => 'nova']);
        $base = ['client_id' => $client->id, 'sale_date' => '2026-10-10', 'vat_type' => 'margem', 'payment_method' => 'transferencia'];

        $this->admin()->post(route('admin.v3.vehicles.sale.store', $this->vehicle->id), $base)->assertSessionHasErrors('sale_price');
        $this->assertSame(0, Sale::count());

        // Caracterização: o preço 0 passa a validação (min:0) e a viatura fica vendida.
        $this->admin()->post(route('admin.v3.vehicles.sale.store', $this->vehicle->id), $base + ['sale_price' => 0])->assertSessionHasNoErrors();
        $this->assertSame(1, Sale::count());
        $this->assertSame('vendido', $this->vehicle->fresh()->status);
    }

    /**
     * BUG conhecido (V3VehicleController::storeSale, ~linha 497-503): a validação marca `client_id` e
     * `payment_method` como nullable, mas `sales.client_id` e `sales.payment_method` são NOT NULL
     * (migration 2025_03_01_091026_create_sales_table). Sem eles o pedido rebenta com 500 e
     * nada é gravado (a transação reverte, a viatura continua em stock). O correto seria 422.
     */
    public function test_sale_without_payment_method_or_client_fails_with_500_known_bug(): void
    {
        $r = $this->admin()->post(route('admin.v3.vehicles.sale.store', $this->vehicle->id), [
            'sale_date' => '2026-10-10', 'sale_price' => 1000, 'vat_type' => 'margem',
        ]);

        $r->assertStatus(500);
        $this->assertSame(0, Sale::count());
        $this->assertSame('em_stock', $this->vehicle->fresh()->status);
    }

    public function test_sale_preview_returns_metrics_json(): void
    {
        $this->admin()->postJson(route('admin.v3.vehicles.sale.preview', $this->vehicle->id), ['sale_price' => 10000, 'vat_type' => 'margem'])
            ->assertOk()
            ->assertJsonStructure(['gross_margin']);

        $this->admin()->postJson(route('admin.v3.vehicles.sale.preview', $this->vehicle->id), ['vat_type' => 'x'])
            ->assertStatus(422)->assertJsonValidationErrors(['sale_price', 'vat_type']);
    }

    // ── Viatura: criar, listar, apagar ───────────────────────────────────────

    public function test_store_creates_vehicle_in_stock_with_reference_and_validates(): void
    {
        $this->admin()->post(route('admin.v3.vehicles.store'), ['brand' => 'Kia', 'model' => 'Ceed', 'year' => 1800])->assertSessionHasErrors('year');

        $this->admin()->post(route('admin.v3.vehicles.store'), ['brand' => 'Kia', 'model' => 'Ceed', 'year' => 2020])->assertRedirect();

        $novo = V3Vehicle::where('brand', 'Kia')->firstOrFail();
        $this->assertSame('em_stock', $novo->status);
        $this->assertStringStartsWith('V3-' . now()->year . '-', $novo->reference);
    }

    public function test_index_lists_and_filters_by_status(): void
    {
        $vendido = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Saab', 'model' => '9-5', 'status' => 'vendido']);

        $this->admin()->get(route('admin.v3.vehicles.index', ['status' => 'vendido']))
            ->assertOk()->assertSee('Saab')->assertDontSee('XC40');
        $this->admin()->get(route('admin.v3.vehicles.index', ['search' => 'Volvo']))
            ->assertOk()->assertSee('XC40')->assertDontSee('9-5');
    }

    public function test_destroy_vehicle_removes_files_and_record(): void
    {
        $photo = $this->photo();
        Storage::put("v3-vehicles/{$this->vehicle->id}/documents/d.pdf", 'x');

        $this->admin()->delete(route('admin.v3.vehicles.destroy', $this->vehicle->id))->assertRedirect(route('admin.v3.vehicles.index'));

        $this->assertNull(V3Vehicle::find($this->vehicle->id));
        Storage::disk('public')->assertMissing($photo->path);
        Storage::assertMissing("v3-vehicles/{$this->vehicle->id}/documents/d.pdf");
    }

    // ── Fotos ────────────────────────────────────────────────────────────────

    public function test_upload_photos_first_is_cover_and_order_increments(): void
    {
        $this->admin()->post(route('admin.v3.vehicles.photos.upload', $this->vehicle->id), [
            'fotos' => [UploadedFile::fake()->image('a.jpg', 100, 100), UploadedFile::fake()->image('b.jpg', 100, 100)],
        ])->assertSessionHas('success');

        $fotos = $this->vehicle->photos()->get();
        $this->assertCount(2, $fotos);
        $this->assertTrue($fotos[0]->is_cover);
        $this->assertFalse($fotos[1]->is_cover);
        $this->assertSame([1, 2], $fotos->pluck('order_position')->all());

        // Um segundo upload continua a ordem e não muda a capa.
        $this->admin()->post(route('admin.v3.vehicles.photos.upload', $this->vehicle->id), ['fotos' => [UploadedFile::fake()->image('c.jpg', 100, 100)]]);
        $this->assertSame(3, $this->vehicle->photos()->count());
        $this->assertSame(1, $this->vehicle->photos()->where('is_cover', true)->count());
        $this->assertSame(3, (int) $this->vehicle->photos()->max('order_position'));
    }

    public function test_upload_photos_validations(): void
    {
        $this->admin()->post(route('admin.v3.vehicles.photos.upload', $this->vehicle->id), [])->assertSessionHasErrors('fotos');
        $this->admin()->post(route('admin.v3.vehicles.photos.upload', $this->vehicle->id), [
            'fotos' => [UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')],
        ])->assertSessionHasErrors('fotos.0');
        $this->admin()->post(route('admin.v3.vehicles.photos.upload', $this->vehicle->id), [
            'fotos' => [UploadedFile::fake()->create('grande.jpg', 6000, 'image/jpeg')],
        ])->assertSessionHasErrors('fotos.0');

        $this->assertSame(0, $this->vehicle->photos()->count());
    }

    public function test_upload_photo_to_unknown_vehicle_is_404(): void
    {
        $this->admin()->post(route('admin.v3.vehicles.photos.upload', 9999), ['fotos' => [UploadedFile::fake()->image('a.jpg')]])->assertNotFound();
    }

    public function test_set_cover_changes_cover_photo(): void
    {
        $a = $this->photo(['is_cover' => true]);
        $b = $this->photo();

        $this->admin()->post(route('admin.v3.vehicles.photos.cover', [$this->vehicle->id, $b->id]))->assertRedirect();

        $this->assertFalse($a->fresh()->is_cover);
        $this->assertTrue($b->fresh()->is_cover);
    }

    /**
     * BUG conhecido (V3VehicleController::setCoverPhoto, ~linha 373): não verifica que a foto pertence à
     * viatura; com o id de uma foto de outra viatura (ou inexistente) todas as capas da viatura são
     * limpas e fica sem capa. O correto seria 404 e manter a capa.
     */
    public function test_set_cover_with_foreign_photo_leaves_vehicle_without_cover_known_bug(): void
    {
        $a = $this->photo(['is_cover' => true]);
        $outro = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Saab', 'model' => '9-3']);
        $foreign = $this->photo([], $outro);

        $this->admin()->post(route('admin.v3.vehicles.photos.cover', [$this->vehicle->id, $foreign->id]))->assertRedirect();

        $this->assertFalse($a->fresh()->is_cover); // o correto seria continuar capa
        $this->assertFalse($foreign->fresh()->is_cover);
    }

    public function test_delete_cover_promotes_first_remaining_photo_and_removes_file(): void
    {
        $capa = $this->photo(['is_cover' => true, 'order_position' => 1]);
        $segunda = $this->photo(['order_position' => 2]);
        $terceira = $this->photo(['order_position' => 3]);

        $this->admin()->delete(route('admin.v3.vehicles.photos.destroy', [$this->vehicle->id, $capa->id]))->assertSessionHas('success');

        Storage::disk('public')->assertMissing($capa->path);
        $this->assertNull(V3VehiclePhoto::find($capa->id));
        $this->assertTrue($segunda->fresh()->is_cover);
        $this->assertFalse($terceira->fresh()->is_cover);
    }

    public function test_delete_photo_of_another_vehicle_is_404(): void
    {
        $outro = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Saab', 'model' => '9-3']);
        $foreign = $this->photo([], $outro);

        $this->admin()->delete(route('admin.v3.vehicles.photos.destroy', [$this->vehicle->id, $foreign->id]))->assertNotFound();

        $this->assertNotNull(V3VehiclePhoto::find($foreign->id));
    }

    public function test_reorder_photos_updates_positions_and_ignores_foreign_ids(): void
    {
        $a = $this->photo(['order_position' => 1]);
        $b = $this->photo(['order_position' => 2]);
        $outro = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Saab', 'model' => '9-3']);
        $foreign = $this->photo(['order_position' => 7], $outro);

        $this->admin()->postJson(route('admin.v3.vehicles.photos.reorder', $this->vehicle->id), ['order' => [$b->id, $a->id, $foreign->id]])
            ->assertOk()->assertJson(['success' => true]);

        $this->assertSame(1, $a->fresh()->order_position);
        $this->assertSame(0, $b->fresh()->order_position);
        $this->assertSame(7, $foreign->fresh()->order_position);

        $this->admin()->postJson(route('admin.v3.vehicles.photos.reorder', $this->vehicle->id), [])->assertStatus(422);
    }

    public function test_focal_point_is_saved_and_validated(): void
    {
        $foto = $this->photo();

        $this->admin()->postJson(route('admin.v3.vehicles.photos.focal', [$this->vehicle->id, $foto->id]), ['focal_x' => 33.337, 'focal_y' => 66])
            ->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(33.34, $foto->fresh()->focal_x);

        $this->admin()->postJson(route('admin.v3.vehicles.photos.focal', [$this->vehicle->id, $foto->id]), ['focal_x' => 120, 'focal_y' => -1])
            ->assertStatus(422)->assertJsonValidationErrors(['focal_x', 'focal_y']);
    }

    public function test_crop_and_restore_answer_404_json_when_file_or_backup_missing(): void
    {
        $foto = V3VehiclePhoto::create(['v3_vehicle_id' => $this->vehicle->id, 'path' => 'v3-vehicles/inexistente/nao-existe.jpg', 'order_position' => 1]);

        $this->admin()->postJson(route('admin.v3.vehicles.photos.crop', [$this->vehicle->id, $foto->id]), ['x' => 0, 'y' => 0, 'width' => 50, 'height' => 50])
            ->assertStatus(404)->assertJson(['success' => false]);
        $this->admin()->postJson(route('admin.v3.vehicles.photos.restore', [$this->vehicle->id, $foto->id]))
            ->assertStatus(404)->assertJson(['success' => false]);
        $this->admin()->postJson(route('admin.v3.vehicles.photos.crop', [$this->vehicle->id, $foto->id]), ['x' => 0, 'y' => 0, 'width' => 5, 'height' => 5])
            ->assertStatus(422)->assertJsonValidationErrors(['width', 'height']);
    }

    // ── Documentos ───────────────────────────────────────────────────────────

    public function test_upload_download_and_delete_document(): void
    {
        $this->admin()->post(route('admin.v3.vehicles.documents.upload', $this->vehicle->id), [
            'ficheiro' => UploadedFile::fake()->create('livrete.pdf', 50, 'application/pdf'), 'titulo' => 'Livrete', 'tipo' => 'livrete',
        ])->assertSessionHas('success');

        $doc = V3VehicleDocument::firstOrFail();
        $this->assertSame('livrete.pdf', $doc->nome_original);
        $this->assertSame('Livrete', $doc->titulo);
        Storage::assertExists($doc->caminho);

        $this->admin()->get(route('admin.v3.vehicles.documents.download', [$this->vehicle->id, $doc->id]))
            ->assertOk()->assertDownload('livrete.pdf');

        $this->admin()->delete(route('admin.v3.vehicles.documents.destroy', [$this->vehicle->id, $doc->id]))->assertSessionHas('success');
        $this->assertNull(V3VehicleDocument::find($doc->id));
        Storage::assertMissing($doc->caminho);
    }

    public function test_document_upload_validations(): void
    {
        $this->admin()->post(route('admin.v3.vehicles.documents.upload', $this->vehicle->id), [])->assertSessionHasErrors('ficheiro');
        $this->admin()->post(route('admin.v3.vehicles.documents.upload', $this->vehicle->id), [
            'ficheiro' => UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('ficheiro');
        $this->admin()->post(route('admin.v3.vehicles.documents.upload', $this->vehicle->id), [
            'ficheiro' => UploadedFile::fake()->create('enorme.pdf', 21000, 'application/pdf'),
        ])->assertSessionHasErrors('ficheiro');

        $this->assertSame(0, V3VehicleDocument::count());
    }

    public function test_document_of_another_vehicle_cannot_be_downloaded_or_deleted(): void
    {
        $outro = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'Saab', 'model' => '9-3']);
        Storage::put('v3-vehicles/x/d.pdf', 'x');
        $doc = V3VehicleDocument::create(['v3_vehicle_id' => $outro->id, 'nome_original' => 'd.pdf', 'caminho' => 'v3-vehicles/x/d.pdf']);

        $this->admin()->get(route('admin.v3.vehicles.documents.download', [$this->vehicle->id, $doc->id]))->assertNotFound();
        $this->admin()->delete(route('admin.v3.vehicles.documents.destroy', [$this->vehicle->id, $doc->id]))->assertNotFound();

        $this->assertNotNull(V3VehicleDocument::find($doc->id));
        Storage::assertExists('v3-vehicles/x/d.pdf');
    }

    public function test_download_of_missing_file_is_404(): void
    {
        $doc = V3VehicleDocument::create(['v3_vehicle_id' => $this->vehicle->id, 'nome_original' => 'x.pdf', 'caminho' => 'v3-vehicles/nada/x.pdf']);

        $this->admin()->get(route('admin.v3.vehicles.documents.download', [$this->vehicle->id, $doc->id]))->assertNotFound();
    }

    // ── Separador de legalização da viatura ─────────────────────────────────

    private function legalization(): Legalization
    {
        return Legalization::create([
            'v3_vehicle_id' => $this->vehicle->id, 'marca' => 'Volvo', 'modelo' => 'XC40', 'combustivel' => 'Gasolina', 'steps_completed' => [],
        ]);
    }

    public function test_create_legalization_from_vehicle_is_idempotent(): void
    {
        $this->vehicle->update(['registration' => 'AA-00-AA', 'fuel' => 'Diesel']);

        $this->admin()->post(route('admin.v3.vehicles.legalization.create', $this->vehicle->id))->assertSessionHas('success');
        $this->admin()->post(route('admin.v3.vehicles.legalization.create', $this->vehicle->id));

        $this->assertSame(1, Legalization::where('v3_vehicle_id', $this->vehicle->id)->count());
        $leg = Legalization::firstOrFail();
        $this->assertSame('Volvo', $leg->marca);
        $this->assertSame('AA-00-AA', $leg->matricula);
        $this->assertSame('Diesel', $leg->combustivel);
    }

    public function test_toggle_step_requires_previous_step(): void
    {
        $leg = $this->legalization();
        $url = route('admin.v3.vehicles.legalization.toggle-step', $this->vehicle->id);

        $this->admin()->postJson($url, ['step' => 3])->assertStatus(422)->assertJsonPath('error', 'Conclui o passo 2 primeiro.');
        $this->assertSame([], $leg->fresh()->steps_completed);

        $this->admin()->postJson($url, ['step' => 1])->assertOk()->assertJsonPath('completed', [1]);
        $this->admin()->postJson($url, ['step' => 2])->assertOk()->assertJsonPath('completed', [1, 2]);

        $this->admin()->postJson($url, ['step' => 9])->assertStatus(422)->assertJsonValidationErrors('step');
    }

    /**
     * BUG conhecido (V3VehicleController::toggleLegalizationStep, ~linha 829): a regra N-1 só é verificada
     * ao concluir; desmarcar um passo intermédio deixa os seguintes concluídos (ex.: [1,3]).
     */
    public function test_untoggling_intermediate_step_leaves_later_steps_done_known_bug(): void
    {
        $leg = $this->legalization();
        $leg->update(['steps_completed' => [1, 2, 3]]);

        $this->admin()->postJson(route('admin.v3.vehicles.legalization.toggle-step', $this->vehicle->id), ['step' => 2])
            ->assertOk()->assertJsonPath('completed', [1, 3]);
    }

    public function test_save_legalization_data_and_requires_existing_legalization(): void
    {
        $this->admin()->post(route('admin.v3.vehicles.legalization.save', $this->vehicle->id), ['matricula' => 'ZZ'])->assertNotFound();

        $leg = $this->legalization();
        $this->admin()->post(route('admin.v3.vehicles.legalization.save', $this->vehicle->id), ['matricula' => '11-AA-22', 'notas' => 'ok'])->assertSessionHas('success');
        $this->assertSame('11-AA-22', $leg->fresh()->matricula);

        $this->admin()->post(route('admin.v3.vehicles.legalization.save', $this->vehicle->id), ['matricula' => str_repeat('x', 30)])->assertSessionHasErrors('matricula');
    }

    public function test_legalization_document_upload_replaces_same_type_and_can_be_deleted(): void
    {
        $leg = $this->legalization();
        $tipo = array_key_first(Legalization::DOCUMENTOS);
        $url = route('admin.v3.vehicles.legalization.upload-document', $this->vehicle->id);

        $this->admin()->post($url, ['tipo' => $tipo, 'ficheiro' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])->assertSessionHas('success');
        $primeiro = LegalizationDocument::firstOrFail();
        $this->admin()->post($url, ['tipo' => $tipo, 'ficheiro' => UploadedFile::fake()->create('b.pdf', 10, 'application/pdf')]);

        $this->assertSame(1, LegalizationDocument::count());
        $this->assertSame('b.pdf', LegalizationDocument::first()->nome_original);
        Storage::assertMissing($primeiro->caminho);

        $doc = LegalizationDocument::first();
        $this->admin()->get(route('admin.v3.vehicles.legalization.download-document', [$this->vehicle->id, $doc->id]))->assertOk();
        $this->admin()->delete(route('admin.v3.vehicles.legalization.delete-document', [$this->vehicle->id, $doc->id]))->assertSessionHas('success');
        $this->assertSame(0, LegalizationDocument::count());

        $this->admin()->post($url, ['tipo' => 'invalido', 'ficheiro' => UploadedFile::fake()->create('c.pdf', 10, 'application/pdf')])->assertSessionHasErrors('tipo');
        $this->admin()->post($url, ['tipo' => $tipo, 'ficheiro' => UploadedFile::fake()->create('c.exe', 10, 'application/octet-stream')])->assertSessionHasErrors('ficheiro');
    }
}
