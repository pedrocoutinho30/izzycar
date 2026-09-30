<?php

namespace Tests\Feature;

use App\Enums\ChecklistItemStatus;
use App\Enums\ContactStatus;
use App\Enums\OpportunityStatus;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\OpportunityChecklistItem;
use App\Models\Seller;
use App\Models\User;
use App\Services\ImportOpportunityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class ImportOpportunityTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;


    private User $user;

    private FormProposal $formProposal;

    protected function setUp(): void
    {
        parent::setUp();

        // O View Composer global (AppServiceProvider) lê menus/logo da cache —
        // pré-preencher evita depender das tabelas do site nas views.
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->user = User::factory()->create(['password' => 'secret', 'last_name' => 'Teste']);
        $this->formProposal = FormProposal::create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'phone' => '912345678',
            'brand' => 'Tesla',
            'model' => 'Model 3',
            'fuel' => 'eletrico',
            'budget' => 25000,
        ]);
    }

    private function route(string $name, ...$params): string
    {
        return route("admin.v2.form-proposals.opportunities.{$name}", [$this->formProposal->id, ...$params]);
    }

    private function makeOpportunity(array $attributes = [], ?FormProposal $formProposal = null): ImportOpportunity
    {
        return ($formProposal ?? $this->formProposal)->opportunities()->create($attributes + [
            'brand' => 'Tesla',
            'model' => 'Model 3',
            'year' => 2022,
            'price' => 23900,
        ]);
    }

    private function makeSeller(string $name = 'Autohaus XYZ'): Seller
    {
        $seller = Seller::create(['name' => $name, 'country' => 'DE']);
        $seller->contacts()->create(['name' => 'Hans Müller', 'email' => 'hans@autohaus-xyz.de', 'is_primary' => true]);

        return $seller;
    }

    private function item(string $checklist, string $slug): OpportunityChecklistItem
    {
        return OpportunityChecklistItem::whereHas('checklist', fn ($q) => $q->where('slug', $checklist))
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->post($this->route('store'), ['brand' => 'Tesla', 'model' => 'Model 3'])
            ->assertRedirect(route('login'));
    }

    public function test_angariador_only_users_cannot_access(): void
    {
        Role::create(['name' => 'angariador']);
        $angariador = User::factory()->create(['password' => 'secret', 'last_name' => 'Teste']);
        $angariador->assignRole('angariador');

        $this->actingAs($angariador)
            ->post($this->route('store'), ['brand' => 'Tesla', 'model' => 'Model 3'])
            ->assertForbidden();
    }

    public function test_quick_create_only_requires_brand_and_model(): void
    {
        $seller = $this->makeSeller();

        $this->actingAs($this->user)
            ->post($this->route('store'), [
                'brand' => 'BMW',
                'model' => 'i4',
                'year' => 2022,
                'mileage' => 68400,
                'price' => 29500,
                'listing_url' => 'https://suchen.mobile.de/fahrzeuge/details.html?id=1',
                'seller_id' => $seller->id,
                'seller_contact_id' => $seller->contacts->first()->id,
            ])
            ->assertRedirect(route('admin.v2.form-proposals.show', $this->formProposal->id) . '#oportunidades')
            ->assertSessionHasNoErrors();

        $opportunity = $this->formProposal->opportunities()->sole();

        $this->assertSame('BMW i4 2022', $opportunity->title);
        $this->assertSame(OpportunityStatus::ToContact, $opportunity->status);
        $this->assertSame(ContactStatus::NotContacted, $opportunity->contact_status);
        $this->assertSame('EUR', $opportunity->currency);
        $this->assertSame($this->user->id, $opportunity->created_by);
        $this->assertTrue($opportunity->seller->is($seller));
        $this->assertSame('Hans Müller', $opportunity->sellerContact->name);
    }

    public function test_opportunity_contact_must_belong_to_the_chosen_seller(): void
    {
        $seller = $this->makeSeller();
        $other = $this->makeSeller('Outro Stand');

        $this->actingAs($this->user)
            ->post($this->route('store'), [
                'brand' => 'BMW',
                'model' => 'i4',
                'seller_id' => $seller->id,
                'seller_contact_id' => $other->contacts->first()->id,
            ])
            ->assertSessionHasErrors('seller_contact_id');

        $this->assertSame(0, ImportOpportunity::count());
    }

    public function test_create_validates_input(): void
    {
        $this->actingAs($this->user)
            ->post($this->route('store'), ['model' => 'i4', 'year' => 1800, 'listing_url' => 'not-a-url', 'vin' => 'ABC'])
            ->assertSessionHasErrors(['brand', 'year', 'listing_url', 'vin']);

        $this->assertSame(0, ImportOpportunity::count());
    }

    public function test_photo_upload_is_stored_and_removed(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user)->post($this->route('store'), [
            'brand' => 'BMW',
            'model' => 'i4',
            'photo' => UploadedFile::fake()->image('car.jpg'),
        ]);

        $opportunity = ImportOpportunity::sole();
        Storage::disk('public')->assertExists($opportunity->photo_path);


        $path = $opportunity->photo_path;
        $this->actingAs($this->user)->delete($this->route('destroy', $opportunity->id));
        Storage::disk('public')->assertMissing($path);
    }

    public function test_photo_url_uses_the_current_host_not_app_url(): void
    {
        // APP_URL antigo (ex. túnel ngrok desligado): o URL da foto não pode depender dele.
        // (Storage::fake ignora o "url" do disco, por isso usa-se um disco local real numa pasta temporária.)
        config(['filesystems.disks.public' => [
            'driver' => 'local',
            'root' => sys_get_temp_dir() . '/izzycar-test-public',
            'url' => 'https://tunel-antigo.example/storage',
        ]]);
        Storage::forgetDisk('public');

        $opportunity = $this->makeOpportunity(['photo_path' => 'import-opportunities/1/car.jpg']);

        $this->assertSame(asset('storage/' . $opportunity->photo_path), $opportunity->photo_url);
        $this->assertStringNotContainsString('tunel-antigo.example', $opportunity->photo_url);
    }

    public function test_opportunity_from_another_request_returns_404(): void
    {
        $other = FormProposal::create(['name' => 'Outro', 'email' => 'o@example.com', 'phone' => '910000000']);
        $foreign = $this->makeOpportunity([], $other);

        $this->actingAs($this->user)
            ->patchJson($this->route('update-status', $foreign->id), ['status' => 'selecionado'])
            ->assertNotFound();
    }

    public function test_status_can_be_changed_via_json(): void
    {
        $opportunity = $this->makeOpportunity();

        $this->actingAs($this->user)
            ->patchJson($this->route('update-status', $opportunity->id), ['status' => 'em_negociacao'])
            ->assertOk()
            ->assertJsonPath('status.label', 'Em negociação');

        $this->assertSame(OpportunityStatus::Negotiating, $opportunity->fresh()->status);

        $this->actingAs($this->user)
            ->patchJson($this->route('update-status', $opportunity->id), ['status' => 'invalido'])
            ->assertUnprocessable();
    }

    public function test_partial_update_keeps_other_fields(): void
    {
        $seller = $this->makeSeller();
        $opportunity = $this->makeOpportunity(['seller_id' => $seller->id]);

        $this->actingAs($this->user)
            ->put($this->route('update', $opportunity->id), [
                'section' => 'contacto',
                'contact_method' => 'whatsapp',
                'contact_status' => 'respondeu',
                'next_followup_at' => '2026-10-02',
            ])
            ->assertSessionHasNoErrors();

        $opportunity->refresh();
        $this->assertSame($seller->id, $opportunity->seller_id);
        $this->assertSame('Tesla', $opportunity->brand);
        $this->assertSame(ContactStatus::Replied, $opportunity->contact_status);
        $this->assertSame('2026-10-02', $opportunity->next_followup_at->toDateString());
    }

    public function test_electric_checklist_only_applies_to_electric_vehicles(): void
    {
        $service = app(ImportOpportunityService::class);

        $petrol = $this->makeOpportunity(['fuel' => 'gasolina']);
        $electric = $this->makeOpportunity(['fuel' => 'eletrico']);

        $this->assertSame(['base'], $service->checklistsFor($petrol)->pluck('slug')->all());
        $this->assertSame(['base', 'electric'], $service->checklistsFor($electric)->pluck('slug')->all());
        $this->assertSame(10, $service->progress($petrol)->total);
        $this->assertSame(17, $service->progress($electric)->total);
        $this->assertSame(['vin', 'coc', 'historico_manutencao'], $service->checklistsFor($petrol)->first()->items->take(3)->pluck('slug')->all());

        $baseLabels = $service->checklistsFor($petrol)->flatMap->items->pluck('label')->all();
        $this->assertNotContains('Cabos de carregamento', $baseLabels);
        $this->assertNotContains('Certificado SOH', $baseLabels);
        $this->assertNotContains('Preço bruto confirmado', $baseLabels);
        $this->assertContains('Garantia do veículo', $baseLabels);
        $this->assertSame(
            ['Certificado SOH / teste da bateria', 'Tipo/química da bateria', 'Garantia da bateria', 'Cabos de carregamento', 'Cabo Type 2', 'Cabo CCS', 'Carregador doméstico'],
            $service->checklistsFor($electric)->last()->items->pluck('label')->all()
        );

        $this->actingAs($this->user)
            ->patchJson($this->route('update-checklist', $petrol->id), [
                'item_id' => $this->item('electric', 'teste_bateria')->id,
                'status' => 'confirmed',
            ])
            ->assertStatus(422);
    }

    public function test_checklist_progress_treats_not_applicable_separately(): void
    {
        $opportunity = $this->makeOpportunity(['fuel' => 'gasolina']);

        foreach (['vin', 'coc', 'fatura'] as $slug) {
            $this->actingAs($this->user)->patchJson($this->route('update-checklist', $opportunity->id), [
                'item_id' => $this->item('base', $slug)->id,
                'status' => 'confirmed',
            ])->assertOk();
        }

        $response = $this->actingAs($this->user)->patchJson($this->route('update-checklist', $opportunity->id), [
            'item_id' => $this->item('base', 'manual')->id,
            'status' => 'not_applicable',
        ]);

        // 10 itens: 3 confirmados, 1 N/A, 6 por confirmar → 3/9 = 33%.
        $response->assertOk()
            ->assertJsonPath('progress.total', 10)
            ->assertJsonPath('progress.confirmed', 3)
            ->assertJsonPath('progress.not_applicable', 1)
            ->assertJsonPath('progress.pending', 6)
            ->assertJsonPath('progress.percent', 33);

        $this->assertNotContains('VIN', $response->json('progress.pending_labels'));
        $this->assertNotContains('Manual', $response->json('progress.pending_labels'));
        $this->assertContains('Histórico de acidentes', $response->json('progress.pending_labels'));

        // Voltar a "por confirmar" atualiza o mesmo registo.
        $this->actingAs($this->user)->patchJson($this->route('update-checklist', $opportunity->id), [
            'item_id' => $this->item('base', 'vin')->id,
            'status' => ChecklistItemStatus::Pending->value,
        ])->assertJsonPath('progress.confirmed', 2);

        $this->assertSame(4, $opportunity->checklistEntries()->count());
    }

    public function test_logging_contacts_updates_tracking_fields(): void
    {
        $opportunity = $this->makeOpportunity();

        $this->actingAs($this->user)->post($this->route('contacts.store', $opportunity->id), [
            'contacted_at' => '2026-09-29 10:00',
            'method' => 'whatsapp',
            'type' => 'enviado',
            'message' => 'Pedido VIN + SOH + histórico de manutenção.',
        ])->assertSessionHasNoErrors();

        $opportunity->refresh();
        $this->assertSame(OpportunityStatus::Contacted, $opportunity->status);
        $this->assertSame(ContactStatus::Contacted, $opportunity->contact_status);
        $this->assertSame('2026-09-29 10:00', $opportunity->last_contacted_at->format('Y-m-d H:i'));
        $this->assertSame('whatsapp', $opportunity->contact_method->value);

        $this->actingAs($this->user)->post($this->route('contacts.store', $opportunity->id), [
            'contacted_at' => '2026-09-30 15:30',
            'method' => 'telefone',
            'type' => 'recebido',
            'message' => 'Vendedor respondeu e enviou VIN.',
        ]);

        // Uma nota interna não conta como contacto.
        $this->actingAs($this->user)->post($this->route('contacts.store', $opportunity->id), [
            'contacted_at' => '2026-10-01 09:00',
            'method' => 'outro',
            'type' => 'nota',
            'message' => 'Confirmar garantia em Portugal.',
        ]);

        $opportunity->refresh();
        $this->assertSame(ContactStatus::Replied, $opportunity->contact_status);
        $this->assertSame('2026-09-30 15:30', $opportunity->last_contacted_at->format('Y-m-d H:i'));
        $this->assertSame('telefone', $opportunity->contact_method->value);
        $this->assertSame(3, $opportunity->contacts()->count());
        $this->assertSame($this->user->id, $opportunity->contacts()->first()->user_id);

        // Um estado avançado manualmente nunca regride ao registar contactos.
        $opportunity->update(['status' => OpportunityStatus::Negotiating]);
        $this->actingAs($this->user)->post($this->route('contacts.store', $opportunity->id), [
            'contacted_at' => '2026-10-02 09:00', 'method' => 'whatsapp', 'type' => 'enviado',
        ]);
        $this->assertSame(OpportunityStatus::Negotiating, $opportunity->fresh()->status);
    }

    public function test_deleting_a_contact_recalculates_last_contact(): void
    {
        $opportunity = $this->makeOpportunity();
        $service = app(ImportOpportunityService::class);

        $service->logContact($opportunity, ['contacted_at' => '2026-09-29 10:00', 'method' => 'whatsapp', 'type' => 'enviado'], $this->user);
        $latest = $service->logContact($opportunity->fresh(), ['contacted_at' => '2026-09-30 10:00', 'method' => 'telefone', 'type' => 'chamada'], $this->user);

        $this->actingAs($this->user)
            ->delete($this->route('contacts.destroy', $opportunity->id, $latest->id))
            ->assertRedirect();

        $opportunity->refresh();
        $this->assertSame('2026-09-29 10:00', $opportunity->last_contacted_at->format('Y-m-d H:i'));
        $this->assertSame('whatsapp', $opportunity->contact_method->value);
    }

    public function test_notes_autosave(): void
    {
        $opportunity = $this->makeOpportunity();

        $this->actingAs($this->user)
            ->patchJson($this->route('update-notes', $opportunity->id), ['notes' => "Vendedor diz que está impecável.\nPreço negociável."])
            ->assertOk()
            ->assertJsonStructure(['saved_at']);

        $this->assertStringContainsString('Preço negociável.', $opportunity->fresh()->notes);
    }

    public function test_deleting_the_import_request_cascades_to_opportunities(): void
    {
        $opportunity = $this->makeOpportunity();
        app(ImportOpportunityService::class)->logContact($opportunity, ['contacted_at' => now(), 'method' => 'whatsapp', 'type' => 'enviado'], $this->user);
        app(ImportOpportunityService::class)->setChecklistStatus($opportunity, $this->item('base', 'vin')->id, ChecklistItemStatus::Confirmed, $this->user);

        $this->actingAs($this->user)
            ->delete(route('admin.v2.form-proposals.destroy', $this->formProposal->id))
            ->assertRedirect(route('admin.v2.form-proposals.index'));

        $this->assertDatabaseCount('import_opportunities', 0);
        $this->assertDatabaseCount('import_opportunity_contacts', 0);
        $this->assertDatabaseCount('import_opportunity_checklist_entries', 0);
    }

    public function test_opportunities_section_renders_cards_with_summary(): void
    {
        $opportunity = $this->makeOpportunity([
            'brand' => 'BMW', 'model' => 'i4', 'year' => 2022, 'mileage' => 68400, 'price' => 29500,
            'seller_id' => $this->makeSeller()->id, 'status' => 'aguardar_resposta', 'contact_method' => 'whatsapp',
            'last_contacted_at' => '2026-09-29 10:00', 'fuel' => 'eletrico',
        ]);
        $this->makeOpportunity(['brand' => 'Tesla', 'model' => 'Model 3', 'status' => 'rejeitado']);

        $service = app(ImportOpportunityService::class);
        $service->setChecklistStatus($opportunity, $this->item('base', 'vin')->id, ChecklistItemStatus::Confirmed, $this->user);

        $formProposal = $this->formProposal->load(['opportunities.checklistEntries', 'opportunities.seller']);
        $html = view('admin.v2.form-proposals.partials.opportunities', [
            'formProposal' => $formProposal,
            'opportunityProgress' => $formProposal->opportunities->mapWithKeys(fn ($o) => [$o->id => $service->progress($o)]),
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('Oportunidades', $html);
        $this->assertStringContainsString('BMW i4 2022', $html);
        $this->assertStringContainsString('68.400 km', $html);
        $this->assertStringContainsString('29.500 €', $html);
        $this->assertStringContainsString('A aguardar resposta', $html);
        $this->assertStringContainsString('Autohaus XYZ', $html);
        $this->assertStringContainsString('Último contacto: 29/09/2026', $html);
        $this->assertMatchesRegularExpression('/<strong data-role="confirmed">1<\/strong>\/<span data-role="total">17<\/span> confirmados/', $html);
        $this->assertStringContainsString('data-filter-status="rejeitado"', $html);
        $this->assertStringNotContainsStringIgnoringCase('post-it', $html);
    }

    public function test_refine_migration_moves_existing_states_to_merged_items(): void
    {
        $migration = require base_path('database/migrations/2026_09_29_150000_refine_opportunity_checklist_items.php');
        $migration->down();

        $opportunity = $this->makeOpportunity(['fuel' => 'eletrico']);
        $service = app(ImportOpportunityService::class);
        $service->setChecklistStatus($opportunity, $this->item('base', 'livro_revisoes')->id, ChecklistItemStatus::Confirmed, $this->user);
        $service->setChecklistStatus($opportunity, $this->item('electric', 'soh')->id, ChecklistItemStatus::Confirmed, $this->user);
        $service->setChecklistStatus($opportunity, $this->item('base', 'cabos_carregamento')->id, ChecklistItemStatus::NotApplicable, $this->user);
        $service->setChecklistStatus($opportunity, $this->item('electric', 'heat_pump')->id, ChecklistItemStatus::Confirmed, $this->user);

        $migration->up();

        $statuses = app(ImportOpportunityService::class)->checklistStatuses($opportunity->fresh());
        $this->assertSame(ChecklistItemStatus::Confirmed, $statuses[$this->item('base', 'historico_manutencao')->id]);
        $this->assertSame(ChecklistItemStatus::Confirmed, $statuses[$this->item('electric', 'teste_bateria')->id]);
        $this->assertSame(ChecklistItemStatus::NotApplicable, $statuses[$this->item('electric', 'cabos_carregamento')->id]);
        $this->assertCount(17, $statuses);
        $this->assertSame(3, $opportunity->checklistEntries()->count());
    }
}
