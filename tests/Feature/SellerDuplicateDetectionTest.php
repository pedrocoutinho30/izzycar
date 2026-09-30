<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\SellerContact;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class SellerDuplicateDetectionTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    private User $user;

    private Seller $muller;

    private SellerContact $hans;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');

        $this->user = User::factory()->create(['password' => 'secret', 'last_name' => 'Teste']);
        $this->muller = Seller::create(['name' => 'Autohaus Müller GmbH', 'country' => 'DE']);
        $this->muller->syncDomains(['autohaus-muller.de']);
        $this->hans = $this->muller->contacts()->create([
            'name' => 'Hans Müller',
            'email' => 'hans@autohaus-muller.de',
            'phone' => '+49 171 123 4567',
            'whatsapp' => '+49 160 999 8888',
            'is_primary' => true,
        ]);
        $this->muller->contacts()->create(['name' => 'Peter Schmidt', 'email' => 'peter@autohaus-muller.de']);
    }

    private function check(array $data)
    {
        return $this->actingAs($this->user)->postJson(route('admin.v2.sellers.check-duplicates'), $data)->assertOk();
    }

    public function test_exact_email_is_detected_ignoring_case_and_spaces(): void
    {
        $this->check(['email' => '  HANS@Autohaus-Muller.de '])
            ->assertJsonPath('blocking', true)
            ->assertJsonPath('email.name', 'Hans Müller')
            ->assertJsonPath('email.seller.name', 'Autohaus Müller GmbH')
            ->assertJsonPath('domain', []);
    }

    public function test_exact_email_blocks_creation_even_when_confirmed(): void
    {
        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.store'), [
                'name' => 'Outro Stand',
                'confirm_matches' => 1,
                'contact' => ['name' => 'Hans', 'email' => 'hans@autohaus-muller.de'],
            ])
            ->assertSessionHasErrors('contact.email');

        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.contacts.store', $this->muller->id), [
                'confirm_matches' => 1,
                'contact' => ['name' => 'Hans 2', 'email' => 'hans@autohaus-muller.de'],
            ])
            ->assertSessionHasErrors('contact.email');

        $this->assertSame(1, Seller::count());
        $this->assertSame(2, SellerContact::count());
    }

    public function test_phone_is_detected_in_any_format(): void
    {
        foreach (['0049 171 123 4567', '+491711234567', '0171 1234567'] as $phone) {
            $this->check(['phone' => $phone, 'country' => 'DE'])
                ->assertJsonPath('blocking', false)
                ->assertJsonPath('needs_confirmation', true)
                ->assertJsonPath('phone.0.name', 'Hans Müller');
        }
    }

    public function test_whatsapp_matches_phone_or_whatsapp_of_other_contacts(): void
    {
        $this->check(['whatsapp' => '+49 160 999 8888'])->assertJsonPath('whatsapp.0.name', 'Hans Müller');
        $this->check(['whatsapp' => '+49 171 123 4567'])->assertJsonPath('whatsapp.0.name', 'Hans Müller');
    }

    public function test_phone_match_needs_confirmation_then_creates_new_contact(): void
    {
        $payload = ['contact' => ['name' => 'Anna Weber', 'phone' => '+49 171 123 4567']];

        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.contacts.store', $this->muller->id), $payload)
            ->assertSessionHasErrors('matches');
        $this->assertSame(2, SellerContact::count());

        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.contacts.store', $this->muller->id), $payload + ['confirm_matches' => 1])
            ->assertSessionHasNoErrors();
        $this->assertSame(3, SellerContact::count());
    }

    public function test_domain_suggests_seller_with_its_contacts(): void
    {
        $this->check(['email' => 'joao@Autohaus-Muller.de'])
            ->assertJsonPath('blocking', false)
            ->assertJsonPath('needs_confirmation', true)
            ->assertJsonPath('domain_name', 'autohaus-muller.de')
            ->assertJsonPath('domain.0.name', 'Autohaus Müller GmbH')
            ->assertJsonCount(2, 'domain.0.contacts');
    }

    public function test_domain_also_matches_contact_emails_when_seller_has_no_domains(): void
    {
        $this->muller->syncDomains([]);

        $this->check(['email' => 'joao@autohaus-muller.de'])->assertJsonPath('domain.0.name', 'Autohaus Müller GmbH');
    }

    public function test_domain_never_associates_automatically(): void
    {
        $payload = ['name' => 'Novo Stand', 'contact' => ['name' => 'João', 'email' => 'joao@autohaus-muller.de']];

        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.store'), $payload)
            ->assertSessionHasErrors('matches');
        $this->assertSame(1, Seller::count());

        // "Criar novo vendedor": fica num vendedor novo, não no Müller.
        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.store'), $payload + ['confirm_matches' => 1])
            ->assertSessionHasNoErrors();

        $joao = SellerContact::where('name', 'João')->sole();
        $this->assertNotSame($this->muller->id, $joao->seller_id);
        $this->assertSame('Novo Stand', $joao->seller->name);
    }

    public function test_add_to_existing_seller_after_domain_suggestion(): void
    {
        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.store'), [
                'name' => 'Autohaus Müller',
                'existing_seller_id' => $this->muller->id,
                'confirm_matches' => 1,
                'contact' => ['name' => 'João', 'email' => 'joao@autohaus-muller.de'],
            ])
            ->assertRedirect(route('admin.v2.sellers.show', $this->muller->id));

        $this->assertSame(1, Seller::count());
        $this->assertSame(3, $this->muller->contacts()->count());
    }

    public function test_domain_of_own_seller_is_not_a_warning(): void
    {
        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.contacts.store', $this->muller->id), [
                'contact' => ['name' => 'Anna Weber', 'email' => 'anna@autohaus-muller.de'],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(3, $this->muller->contacts()->count());
    }

    public function test_generic_email_domains_are_ignored(): void
    {
        $other = Seller::create(['name' => 'Kleiner Händler']);
        $other->contacts()->create(['name' => 'Karl', 'email' => 'karl@gmx.de']);

        $this->check(['email' => 'fritz@gmx.de'])
            ->assertJsonPath('needs_confirmation', false)
            ->assertJsonPath('domain', []);
    }

    public function test_names_are_never_a_duplicate_criterion(): void
    {
        $this->check(['email' => 'hans.mueller@outro-stand.de'])->assertJsonPath('needs_confirmation', false);

        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.store'), [
                'name' => 'Autohaus Müller GmbH',
                'contact' => ['name' => 'Hans Müller'],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Seller::where('name', 'Autohaus Müller GmbH')->count());
        $this->assertSame(2, SellerContact::where('name', 'Hans Müller')->count());
    }

    public function test_editing_a_contact_ignores_itself(): void
    {
        $this->actingAs($this->user)
            ->put(route('admin.v2.sellers.contacts.update', [$this->muller->id, $this->hans->id]), [
                'contact' => ['name' => 'Hans Müller', 'email' => 'hans@autohaus-muller.de', 'phone' => '+49 171 123 4567', 'role' => 'CEO'],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('CEO', $this->hans->fresh()->role);
    }

    public function test_new_seller_domain_that_exists_elsewhere_needs_confirmation(): void
    {
        $this->actingAs($this->user)
            ->post(route('admin.v2.sellers.store'), ['name' => 'Filial', 'domains' => 'autohaus-muller.de'])
            ->assertSessionHasErrors('matches');

        $this->check(['domains' => 'autohaus-muller.de'])->assertJsonPath('domain.0.name', 'Autohaus Müller GmbH');
    }
}
