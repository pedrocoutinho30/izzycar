<?php

namespace Tests\Feature\Mobile;

use App\Models\User;
use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class MobileAccessTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_novos_utilizadores_ficam_sem_acesso_mobile_e_o_campo_nao_e_atribuivel_em_massa(): void
    {
        // A factory não define last_name (NOT NULL em sqlite) e o hash por omissão é bcrypt custo 10,
        // mas o phpunit usa BCRYPT_ROUNDS=4: definir os dois como os restantes testes (ver tests/TestCase.php).
        $user = User::factory()->create(['password' => 'secret', 'last_name' => 'Teste']);
        $this->assertFalse($user->refresh()->access_mobile);

        $user->fill(['access_mobile' => true]);
        $this->assertFalse((bool) $user->access_mobile);
    }
}
