<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class AngariadorRegistrationDocumentsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        Mail::fake();
        Role::findOrCreate('angariador', 'web');
    }

    private function dados(array $extra = []): array
    {
        return $extra + [
            'name' => 'Rita', 'last_name' => 'Costa', 'email' => 'rita@example.com', 'phone' => '912345678',
            'documento_frente' => UploadedFile::fake()->image('frente.jpg'),
            'documento_verso' => UploadedFile::fake()->create('verso.pdf', 200, 'application/pdf'),
        ];
    }

    public function test_the_form_asks_for_front_and_back_of_the_id_document(): void
    {
        $this->get(route('public.angariador.register'))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="documento_frente"', false)
            ->assertSee('name="documento_verso"', false);
    }

    public function test_application_stores_both_sides_privately(): void
    {
        $this->post(route('public.angariador.register.store'), $this->dados())->assertRedirect();

        $user = User::where('email', 'rita@example.com')->firstOrFail();
        $this->assertSame('pendente', $user->status);
        $this->assertNotNull($user->documento_frente_path);
        $this->assertNotNull($user->documento_verso_path);

        // Disco privado: nunca o disco público, que é servido pelo site.
        Storage::disk('local')->assertExists($user->documento_frente_path);
        Storage::disk('local')->assertExists($user->documento_verso_path);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_both_sides_are_required_and_nothing_is_created_without_them(): void
    {
        $this->post(route('public.angariador.register.store'), $this->dados(['documento_verso' => null]))
            ->assertSessionHasErrors('documento_verso');
        $this->post(route('public.angariador.register.store'), $this->dados(['email' => 'outra@example.com', 'documento_frente' => null]))
            ->assertSessionHasErrors('documento_frente');

        $this->assertSame(0, User::whereIn('email', ['rita@example.com', 'outra@example.com'])->count());
    }

    public function test_only_jpg_png_or_pdf_up_to_5mb_are_accepted(): void
    {
        $this->post(route('public.angariador.register.store'), $this->dados(['documento_frente' => UploadedFile::fake()->create('x.exe', 10, 'application/octet-stream')]))
            ->assertSessionHasErrors('documento_frente');
        $this->post(route('public.angariador.register.store'), $this->dados(['documento_frente' => UploadedFile::fake()->create('x.php', 10, 'application/x-php')]))
            ->assertSessionHasErrors('documento_frente');
        $this->post(route('public.angariador.register.store'), $this->dados(['documento_verso' => UploadedFile::fake()->create('grande.pdf', 6000, 'application/pdf')]))
            ->assertSessionHasErrors('documento_verso');

        $this->assertSame(0, User::where('email', 'rita@example.com')->count());
    }

    public function test_the_public_registration_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('public.angariador.register.store'), []);
        }

        $this->post(route('public.angariador.register.store'), [])->assertStatus(429);
    }

    public function test_admin_can_open_both_sides_and_guests_cannot(): void
    {
        $this->post(route('public.angariador.register.store'), $this->dados());
        $user = User::where('email', 'rita@example.com')->firstOrFail();

        $this->get(route('admin.v2.angariadores.identity-document', [$user->id, 'frente']))->assertRedirect(route('login'));

        $admin = $this->backofficeUser('admin');
        $this->actingAs($admin)->get(route('admin.v2.angariadores.identity-document', [$user->id, 'frente']))->assertOk();
        $this->actingAs($admin)->get(route('admin.v2.angariadores.identity-document', [$user->id, 'verso']))->assertOk();
        $this->actingAs($admin)->get(route('admin.v2.angariadores.show', $user->id))
            ->assertOk()->assertSee('Documento de identificação')->assertSee('Ver frente')->assertSee('Ver verso');
    }

    public function test_unknown_side_or_non_angariador_gives_404(): void
    {
        $this->post(route('public.angariador.register.store'), $this->dados());
        $user = User::where('email', 'rita@example.com')->firstOrFail();
        $admin = $this->backofficeUser('admin');

        $this->actingAs($admin)->get('/gestao/v2/angariadores/' . $user->id . '/documento/outro')->assertNotFound();
        $this->actingAs($admin)->get(route('admin.v2.angariadores.identity-document', [$admin->id, 'frente']))->assertNotFound();
    }

    public function test_rejecting_or_deleting_the_application_deletes_the_documents(): void
    {
        $admin = $this->backofficeUser('admin');

        $this->post(route('public.angariador.register.store'), $this->dados());
        $user = User::where('email', 'rita@example.com')->firstOrFail();
        $frente = $user->documento_frente_path;
        $verso = $user->documento_verso_path;

        $this->actingAs($admin)->post(route('admin.v2.angariadores.reject', $user->id))->assertRedirect();

        Storage::disk('local')->assertMissing($frente);
        Storage::disk('local')->assertMissing($verso);
        $this->assertNull($user->refresh()->documento_frente_path);

        $this->post(route('public.angariador.register.store'), $this->dados(['email' => 'joao@example.com']));
        $outro = User::where('email', 'joao@example.com')->firstOrFail();
        $path = $outro->documento_frente_path;

        $this->actingAs($admin)->delete(route('admin.v2.angariadores.destroy', $outro->id))->assertRedirect();

        Storage::disk('local')->assertMissing($path);
    }
}
