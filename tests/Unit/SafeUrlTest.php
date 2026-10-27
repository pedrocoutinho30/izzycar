<?php

namespace Tests\Unit;

use App\Support\SafeUrl;
use PHPUnit\Framework\TestCase;

class SafeUrlTest extends TestCase
{
    public function test_aceita_apenas_http_e_https(): void
    {
        $this->assertTrue(SafeUrl::isHttp('https://anuncio.example/1'));
        $this->assertTrue(SafeUrl::isHttp('  HTTP://Anuncio.example/x?a=1 '));

        foreach (['javascript:alert(1)', 'JaVaScRiPt:alert(1)', 'data:text/html,x', 'ftp://x.example', '//x.example', 'anuncio.example', 'https://', 'https://a b.example', '', null] as $invalido) {
            $this->assertFalse(SafeUrl::isHttp($invalido), (string) $invalido);
        }
    }
}
