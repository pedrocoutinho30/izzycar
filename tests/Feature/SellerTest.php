<?php

namespace Tests\Feature;

use App\Models\FormProposal;
use App\Models\Seller;
use App\Models\SellerContact;
use App\Models\User;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class SellerTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // O View Composer global (AppServiceProvider) lê menus/logo da cache.
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->user = User::factory()->create(['password' => 'secret', 'last_name' => 'Teste']);
    }

    private function makeSeller(array $attributes = []): Seller
    {
        return Seller::create($attributes + ['name' => 'Autohaus Müller GmbH', 'country' => 'DE']);
    }

    private function makeVehicle(Seller $seller, array $attributes = []): V3Vehicle
    {
        return V3Vehicle::create($attributes + [
            'reference' => V3Vehicle::generateReference(),
            'brand' => 'BMW',
            'model' => 'i4',
            'seller_id' => $seller->id,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.v2.sellers.index'))->assertRedirect(route('login'));
    }

    public function test_create_seller_with_first_contact(): void
    {
        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.store'), [
                'name' => 'Autohaus Müller GmbH',
                'website' => 'https://autohaus-muller.de',
                'country' => 'de',
                'domains' => 'autohaus-muller.de, www.autohaus-muller.com',
                'notes' => 'Responde rapidamente.',
                'contact' => [
                    'name' => 'Hans Müller',
                    'role' => 'Sales Manager',
                    'email' => 'Hans@Autohaus-Muller.de',
                    'phone' => '0171 123 4567',
                    'whatsapp' => '+49 171 123 4567',
                ],
            ])
            ->assertSessionHasNoErrors();

        $seller = Seller::sole();
        $this->assertSame('DE', $seller->country);
        $this->assertSame($this->user->id, $seller->created_by);
        $this->assertEqualsCanonicalizing(['autohaus-muller.de', 'autohaus-muller.com'], $seller->domains->pluck('domain')->all());

        $contact = $seller->contacts()->sole();
        $this->assertSame('hans@autohaus-muller.de', $contact->email);
        $this->assertSame('autohaus-muller.de', $contact->email_domain);
        $this->assertSame('491711234567', $contact->phone_normalized);
        $this->assertSame('491711234567', $contact->whatsapp_normalized);
        $this->assertTrue($contact->is_primary);
    }

    public function test_seller_without_contact_and_validation(): void
    {
        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.store'), ['name' => '', 'country' => 'XX', 'contact' => ['email' => 'x@y.de']])
            ->assertSessionHasErrors(['name', 'country', 'contact.name']);

        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.store'), ['name' => 'Stand sem contacto'])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, Seller::sole()->contacts()->count());
    }

    public function test_edit_seller_renormalizes_contacts_when_country_changes(): void
    {
        $seller = $this->makeSeller(['country' => null]);
        $contact = $seller->contacts()->create(['name' => 'Hans', 'phone' => '0171 1234567']);
        $this->assertSame('01711234567', $contact->phone_normalized);

        $this->actingAs($this->user)
            ->put(route('admin.v2.sellers.update', $seller->id), ['name' => 'Autohaus Müller', 'country' => 'DE', 'domains' => ''])
            ->assertRedirect(route('admin.v2.sellers.show', $seller->id));

        $this->assertSame('Autohaus Müller', $seller->fresh()->name);
        $this->assertSame('491711234567', $contact->fresh()->phone_normalized);
    }

    public function test_multiple_contacts_and_single_primary(): void
    {
        $seller = $this->makeSeller();

        foreach (['Hans Müller', 'Peter Schmidt', 'Anna Weber'] as $name) {
            $this->actingAs($this->user)
                ->post(route('admin.v2.sellers.contacts.store', $seller->id), ['contact' => ['name' => $name]])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(3, $seller->contacts()->count());
        $this->assertSame('Hans Müller', $seller->primaryContact->name);

        $peter = $seller->contacts()->where('name', 'Peter Schmidt')->sole();
        $this->actingAs($this->user)->patch(route('admin.v2.sellers.contacts.primary', [$seller->id, $peter->id]));

        $this->assertSame(['Peter Schmidt'], $seller->contacts()->where('is_primary', true)->pluck('name')->all());
    }

    public function test_edit_and_deactivate_contact(): void
    {
        $seller = $this->makeSeller();
        $contact = $seller->contacts()->create(['name' => 'Hans', 'is_primary' => true]);

        $this->actingAs($this->user)
            ->put(route('admin.v2.sellers.contacts.update', [$seller->id, $contact->id]), [
                'contact' => ['name' => 'Hans Müller', 'role' => 'Sales Manager', 'email' => 'hans@autohaus-muller.de'],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Sales Manager', $contact->fresh()->role);

        $this->actingAs($this->user)->patch(route('admin.v2.sellers.contacts.toggle-active', [$seller->id, $contact->id]));

        $contact->refresh();
        $this->assertFalse($contact->is_active);
        $this->assertFalse($contact->is_primary);
        $this->assertNull($seller->fresh()->primaryContact);
    }

    public function test_contact_routes_are_scoped_to_the_seller(): void
    {
        $seller = $this->makeSeller();
        $other = $this->makeSeller(['name' => 'Outro']);
        $contact = $other->contacts()->create(['name' => 'Hans']);

        $this->actingAs($this->user)
            ->patch(route('admin.v2.sellers.contacts.primary', [$seller->id, $contact->id]))
            ->assertNotFound();
    }

    public function test_deactivate_keeps_history_and_hides_from_picker_search(): void
    {
        $seller = $this->makeSeller();
        $this->makeVehicle($seller);

        $this->actingAs($this->user)->patch(route('admin.v2.sellers.toggle-active', $seller->id));
        $this->assertFalse($seller->fresh()->is_active);

        $this->actingAs($this->user)
            ->getJson(route('admin.v2.sellers.search', ['q' => 'Müller']))
            ->assertOk()
            ->assertJsonCount(0);

        $this->actingAs($this->user)
            ->get(route('admin.v2.sellers.index', ['status' => 'inativos']))
            ->assertOk()
            ->assertSee('Autohaus Müller GmbH');
    }

    public function test_seller_with_history_cannot_be_deleted(): void
    {
        $seller = $this->makeSeller();
        $seller->contacts()->create(['name' => 'Hans']);

        $this->actingAs($this->user)
            ->delete(route('admin.v2.sellers.destroy', $seller->id))
            ->assertSessionHasErrors('seller');

        $this->assertModelExists($seller);

        $empty = $this->makeSeller(['name' => 'Criado por engano']);
        $this->actingAs($this->user)
            ->delete(route('admin.v2.sellers.destroy', $empty->id))
            ->assertRedirect(route('admin.v2.sellers.index'));
        $this->assertModelMissing($empty);
    }

    public function test_index_lists_counts_and_searches_by_contact_data(): void
    {
        $seller = $this->makeSeller();
        $seller->syncDomains(['autohaus-muller.de']);
        $seller->contacts()->create(['name' => 'Hans Müller', 'email' => 'hans@autohaus-muller.de', 'phone' => '+49 171 123 4567', 'is_primary' => true]);
        $this->makeVehicle($seller);
        $this->makeSeller(['name' => 'Garage Dupont', 'country' => 'FR']);

        $response = $this->actingAs($this->user)->get(route('admin.v2.sellers.index'));
        $response->assertOk()
            ->assertSee('Autohaus Müller GmbH')
            ->assertSee('Hans Müller')
            ->assertSee('hans@autohaus-muller.de')
            ->assertSee('1 contacto')
            ->assertSee('1 veículo')
            ->assertSee('0 oportunidades');

        foreach (['hans@', '1711234567', 'autohaus-muller.de', 'Hans'] as $term) {
            $this->actingAs($this->user)
                ->get(route('admin.v2.sellers.index', ['search' => $term]))
                ->assertSee('Autohaus Müller GmbH')
                ->assertDontSee('Garage Dupont');
        }
    }

    public function test_show_page_lists_contacts_vehicles_and_opportunities(): void
    {
        $seller = $this->makeSeller();
        $contact = $seller->contacts()->create(['name' => 'Hans Müller', 'is_primary' => true]);
        $this->makeVehicle($seller, [
            'seller_contact_id' => $contact->id,
            'vin' => 'WBY71AW0XPFP12345',
            'purchase_date' => '2026-09-29',
            'purchase_price' => 29500,
        ]);
        $formProposal = FormProposal::create(['name' => 'João Silva', 'email' => 'joao@example.com', 'phone' => '912345678']);
        $formProposal->opportunities()->create(['brand' => 'Porsche', 'model' => 'Taycan', 'seller_id' => $seller->id, 'status' => 'selecionado']);

        $this->actingAs($this->user)
            ->get(route('admin.v2.sellers.show', $seller->id))
            ->assertOk()
            ->assertSee('Hans Müller')
            ->assertSee('WBY71AW0XPFP12345')
            ->assertSee('29/09/2026')
            ->assertSee('29.500 €')
            ->assertSee('Porsche Taycan')
            ->assertSee('Selecionado')
            ->assertSee('João Silva')
            ->assertDontSee('Eliminar vendedor');
    }

    public function test_picker_json_endpoints(): void
    {
        $seller = $this->makeSeller();
        $seller->contacts()->create(['name' => 'Hans Müller', 'is_primary' => true]);
        $seller->contacts()->create(['name' => 'Inativo', 'is_active' => false]);

        $this->actingAs($this->user)
            ->getJson(route('admin.v2.sellers.search', ['q' => 'autohaus']))
            ->assertOk()
            ->assertJsonPath('0.name', 'Autohaus Müller GmbH')
            ->assertJsonPath('0.primary_contact', 'Hans Müller')
            ->assertJsonPath('0.country', 'DE');

        $this->actingAs($this->user)
            ->getJson(route('admin.v2.sellers.contacts.index', $seller->id))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'Hans Müller');
    }

    public function test_quick_create_from_opportunity_returns_seller_and_contact(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('admin.v2.sellers.store'), [
                'name' => 'Autohaus Müller GmbH',
                'country' => 'DE',
                'contact' => ['name' => 'Hans Müller', 'email' => 'hans@autohaus-muller.de'],
            ])
            ->assertOk()
            ->assertJsonPath('seller.name', 'Autohaus Müller GmbH')
            ->assertJsonPath('contact.name', 'Hans Müller');

        $this->actingAs($this->user)
            ->postJson(route('admin.v2.sellers.store'), ['name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_quick_contact_for_seller_returns_json(): void
    {
        $seller = $this->makeSeller();

        $this->actingAs($this->user)
            ->postJson(route('admin.v2.sellers.contacts.store', $seller->id), ['contact' => ['name' => 'Peter Schmidt']])
            ->assertOk()
            ->assertJsonPath('contact.name', 'Peter Schmidt')
            ->assertJsonPath('contact.seller_id', $seller->id);
    }

    public function test_vehicle_purchase_tab_saves_seller_and_contact(): void
    {
        $seller = $this->makeSeller();
        $contact = $seller->contacts()->create(['name' => 'Hans Müller']);
        $vehicle = V3Vehicle::create(['reference' => V3Vehicle::generateReference(), 'brand' => 'BMW', 'model' => 'i4']);

        $this->actingAs($this->user)
            ->postJson(route('admin.v3.vehicles.save-purchase', $vehicle->id), [
                'seller_id' => $seller->id,
                'seller_contact_id' => $contact->id,
                // Sem preço: o sync de despesas (Expense) não corre em sqlite.
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $vehicle->refresh();
        $this->assertTrue($vehicle->seller->is($seller));
        $this->assertTrue($vehicle->sellerContact->is($contact));

        $foreign = $this->makeSeller(['name' => 'Outro'])->contacts()->create(['name' => 'X']);
        $this->actingAs($this->user)
            ->postJson(route('admin.v3.vehicles.save-purchase', $vehicle->id), ['seller_id' => $seller->id, 'seller_contact_id' => $foreign->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('seller_contact_id');
    }

    public function test_opportunity_and_vehicle_pages_show_the_seller_picker(): void
    {
        $seller = $this->makeSeller();
        $contact = $seller->contacts()->create(['name' => 'Hans Müller', 'whatsapp' => '+49 171 123 4567', 'is_primary' => true]);
        $formProposal = FormProposal::create(['name' => 'João Silva', 'email' => 'joao@example.com', 'phone' => '912345678']);
        $opportunity = $formProposal->opportunities()->create([
            'brand' => 'BMW', 'model' => 'i4', 'seller_id' => $seller->id, 'seller_contact_id' => $contact->id,
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.v2.form-proposals.opportunities.show', [$formProposal->id, $opportunity->id]))
            ->assertOk()
            ->assertSee(route('admin.v2.sellers.show', $seller->id))
            ->assertSee('https://wa.me/491711234567', false)
            ->assertSee('data-seller-picker', false)
            ->assertSee('id="sellerQuickModal"', false);

        $this->actingAs($this->user)
            ->get(route('admin.v2.form-proposals.show', $formProposal->id))
            ->assertOk()
            ->assertSee('Autohaus Müller GmbH')
            ->assertSee('id="sellerContactQuickModal"', false);

        $vehicle = $this->makeVehicle($seller, ['seller_contact_id' => $contact->id]);
        $this->actingAs($this->user)
            ->get(route('admin.v3.vehicles.edit', $vehicle->id))
            ->assertOk()
            ->assertSee('data-seller-picker', false)
            ->assertSee('Abrir ficha de Autohaus Müller GmbH');
    }

    public function test_seller_with_vehicles_cannot_be_removed_at_database_level(): void
    {
        $seller = $this->makeSeller();
        $this->makeVehicle($seller);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $seller->delete();
    }

    public function test_contact_normalization_uses_seller_country(): void
    {
        $contact = new SellerContact(['name' => 'Hans', 'whatsapp' => '0171 123 4567']);
        $contact->seller()->associate($this->makeSeller());
        $contact->save();

        $this->assertSame('491711234567', $contact->whatsapp_digits);
    }
}
