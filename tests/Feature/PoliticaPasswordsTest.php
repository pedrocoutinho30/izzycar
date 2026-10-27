<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

/**
 * Política central de palavras-passe (Password::defaults() no AppServiceProvider):
 * mínimo 10 caracteres, maiúsculas, minúsculas e números.
 */
class PoliticaPasswordsTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
    }

    private function utilizador(): User
    {
        return User::factory()->create(['last_name' => 'Teste', 'password' => 'Antiga12345']);
    }

    public static function passwordsFracas(): array
    {
        return [
            'curta com número' => ['abc123'],
            'dez letras sem número' => ['abcdefGHIJ'],
            'sem maiúsculas' => ['abcdefghi123'],
            'sem minúsculas' => ['ABCDEFGHI123'],
        ];
    }

    /** @dataProvider passwordsFracas */
    public function test_definir_password_recusa_passwords_fracas(string $fraca): void
    {
        $user = $this->utilizador();
        $token = Password::broker('setup')->createToken($user);

        $this->post(route('password.setup.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => $fraca,
            'password_confirmation' => $fraca,
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('Antiga12345', $user->fresh()->password));
    }

    public function test_definir_password_aceita_password_forte(): void
    {
        $user = $this->utilizador();
        $token = Password::broker('setup')->createToken($user);

        $this->post(route('password.setup.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NovaPassword123',
            'password_confirmation' => 'NovaPassword123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NovaPassword123', $user->fresh()->password));
    }

    public function test_reset_de_password_aplica_a_mesma_politica(): void
    {
        $user = $this->utilizador();
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'abcdefGHIJ',
            'password_confirmation' => 'abcdefGHIJ',
        ])->assertSessionHasErrors('password');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NovaPassword123',
            'password_confirmation' => 'NovaPassword123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NovaPassword123', $user->fresh()->password));
    }

    public function test_mensagens_estao_em_portugues(): void
    {
        $this->assertSame('pt', app()->getLocale());

        foreach (['letters', 'mixed', 'numbers', 'uncompromised'] as $chave) {
            $this->assertNotSame("validation.password.$chave", __("validation.password.$chave"));
            $this->assertStringNotContainsString('The :attribute', __("validation.password.$chave"));
        }
    }
}
