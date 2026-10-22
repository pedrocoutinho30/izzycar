<?php

namespace Tests\Feature;

use App\Models\Setting;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class AngariadorManualAccessTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // O rodapé do site lê estas definições.
        foreach (['address', 'email', 'facebook', 'insta', 'phone', 'vat_number', 'logo'] as $label) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => 'x']);
        }
    }

    public function test_visitor_without_login_is_redirected_to_login(): void
    {
        $this->get('/manual-angariador')->assertRedirect(route('login'));
    }

    public function test_logged_in_user_can_see_the_manual(): void
    {
        $this->actingAs($this->backofficeUser('admin'))
            ->get('/manual-angariador')
            ->assertOk();
    }

    public function test_landing_page_stays_public(): void
    {
        $this->get(route('public.angariador.landing'))->assertOk();
    }
}
