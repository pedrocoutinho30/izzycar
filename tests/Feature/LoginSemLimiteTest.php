<?php

namespace Tests\Feature;

use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class LoginSemLimiteTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_login_nao_bloqueia_por_excesso_de_tentativas(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $resposta = $this->from('/login')->post('/login', ['email' => 'x@example.com', 'password' => 'errada']);
            $resposta->assertSessionHasErrors('email');
            $this->assertStringNotContainsString('Too many', session('errors')->first('email'));
            $this->assertStringNotContainsStringIgnoringCase('tentativas', session('errors')->first('email'));
        }
    }

    public function test_registo_de_angariador_nao_tem_throttle(): void
    {
        $route = app('router')->getRoutes()->getByName('public.angariador.register.store');
        $this->assertSame([], array_values(array_filter($route->gatherMiddleware(), fn ($m) => is_string($m) && (str_starts_with($m, 'throttle') || $m === 'antispam'))));
    }
}
