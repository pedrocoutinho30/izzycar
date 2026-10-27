<?php

namespace Tests\Feature;

use Tests\RefreshesDatabaseWithoutMysqlOnlyMigrations;
use Tests\TestCase;

class PaginasErroTest extends TestCase
{
    use RefreshesDatabaseWithoutMysqlOnlyMigrations;

    public function test_url_inexistente_devolve_404_personalizado_com_noindex(): void
    {
        $this->get('/esta-pagina-nao-existe')
            ->assertNotFound()
            ->assertSee('Página não encontrada')
            ->assertSee('Voltar à página inicial')
            ->assertSee('Ver viaturas')
            ->assertSee('<meta name="robots" content="noindex">', false);
    }

    public function test_paginas_de_erro_renderizam_sem_erro(): void
    {
        foreach ([403, 419, 429, 500, 503] as $codigo) {
            $html = view("errors.$codigo")->render();
            $this->assertStringContainsString('geral@izzycar.pt', $html, "errors.$codigo");
            $this->assertStringContainsString('noindex', $html, "errors.$codigo");
        }
    }
}
