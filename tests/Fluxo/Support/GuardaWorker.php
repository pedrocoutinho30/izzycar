<?php

namespace Tests\Fluxo\Support;

use Tests\Fluxo\Concerns\UsaMysqlIsolado;

/**
 * Expõe a MESMA guarda dos testes (UsaMysqlIsolado::garantirBaseIsolada) aos processos
 * worker, que não são testes PHPUnit. Não acrescenta lógica: só dá uma classe ao trait.
 */
final class GuardaWorker
{
    use UsaMysqlIsolado;
}
