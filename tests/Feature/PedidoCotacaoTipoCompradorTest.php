<?php

namespace Tests\Feature;

use App\Mail\ImportFormConfirmationMail;
use App\Models\Client;
use App\Models\FormProposal;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class PedidoCotacaoTipoCompradorTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        Mail::fake();
        Notification::fake();
        foreach (['email' => 'geral@izzycar.pt', 'phone' => '928459346', 'facebook' => 'x', 'insta' => 'x'] as $label => $value) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Maria Silva', 'phone' => '912 345 678', 'email' => 'maria@example.com',
            'buyer_type' => 'empresa', 'payment_type' => 'pronto_pagamento', 'estimated_purchase_date' => 'imediato',
            'data_processing_consent' => '1', 'brand' => 'BMW', 'model' => 'i4',
        ];
    }

    public function test_both_public_forms_ask_particular_or_empresa_in_the_purchase_preferences(): void
    {
        $this->get(route('frontend.form-import'))
            ->assertOk()
            ->assertSee('Particular ou Empresa')
            ->assertSee('name="buyer_type"', false)
            ->assertSee('value="particular"', false)
            ->assertSee('value="empresa"', false);

        $modal = view('frontend.forms.proposal', ['brands' => collect()])->render();
        $this->assertStringContainsString('name="buyer_type"', $modal);
        $this->assertStringContainsString('Particular ou Empresa', $modal);
    }

    public function test_the_request_stores_the_buyer_type_and_sets_the_type_of_a_new_client(): void
    {
        $this->post(route('frontend.import-submit'), $this->payload())->assertSuccessful();

        $pedido = FormProposal::where('email', 'maria@example.com')->firstOrFail();
        $this->assertSame('empresa', $pedido->buyer_type);
        $this->assertSame('empresa', Client::findOrFail($pedido->client_id)->client_type);
    }

    public function test_the_field_is_required_and_only_accepts_particular_or_empresa(): void
    {
        $this->post(route('frontend.import-submit'), $this->payload(['buyer_type' => '']))->assertSessionHasErrors('buyer_type');
        $this->postJson(route('frontend.import-submit'), $this->payload(['buyer_type' => 'outro']))->assertStatus(422)->assertJsonValidationErrors('buyer_type');

        $this->assertSame(0, FormProposal::count());
    }

    public function test_an_existing_client_only_gets_the_type_when_it_has_none(): void
    {
        $semTipo = Client::create(['name' => 'Maria Silva', 'email' => 'maria@example.com', 'phone' => '912345678', 'is_lead' => true, 'lead_status' => 'nova']);
        $this->post(route('frontend.import-submit'), $this->payload())->assertSuccessful();
        $this->assertSame('empresa', $semTipo->refresh()->client_type);

        $comTipo = Client::create(['name' => 'Rui', 'email' => 'rui@example.com', 'phone' => '913456789', 'client_type' => 'particular', 'is_lead' => true, 'lead_status' => 'nova']);
        $this->post(route('frontend.import-submit'), $this->payload(['name' => 'Rui', 'email' => 'rui@example.com', 'phone' => '913 456 789', 'buyer_type' => 'empresa']))->assertSuccessful();
        $this->assertSame('particular', $comTipo->refresh()->client_type, 'um tipo já definido nunca é substituído por um formulário público');
        $this->assertSame('empresa', FormProposal::where('email', 'rui@example.com')->firstOrFail()->buyer_type, 'o pedido guarda sempre o que foi escolhido');
    }

    public function test_admin_sees_the_buyer_type_in_the_request(): void
    {
        $this->post(route('frontend.import-submit'), $this->payload())->assertSuccessful();
        $pedido = FormProposal::firstOrFail();

        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v2.form-proposals.show', $pedido->id))
            ->assertOk()
            ->assertSee('Particular ou Empresa')
            ->assertSee('Empresa');
    }

    public function test_the_confirmation_email_to_the_client_shows_the_buyer_type(): void
    {
        $this->post(route('frontend.import-submit'), $this->payload(['buyer_type' => 'particular']))->assertSuccessful();

        Mail::assertSent(ImportFormConfirmationMail::class, function ($mail) {
            $html = $mail->render();

            return str_contains($html, 'Comprador') && str_contains($html, 'Particular');
        });
    }

    public function test_old_requests_without_the_field_still_show_in_the_backoffice(): void
    {
        $pedido = FormProposal::forceCreate(['name' => 'Antigo', 'phone' => '912345678', 'email' => 'antigo@example.com', 'status' => 'novo']);

        $this->actingAs($this->backofficeUser('admin'))
            ->get(route('admin.v2.form-proposals.show', $pedido->id))
            ->assertOk();
    }
}
