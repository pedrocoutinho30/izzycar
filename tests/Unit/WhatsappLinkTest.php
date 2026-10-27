<?php

namespace Tests\Unit;

use App\Services\PhoneNumberService;
use PHPUnit\Framework\TestCase;

class WhatsappLinkTest extends TestCase
{
    public function test_numero_portugues_de_9_digitos_leva_o_indicativo(): void
    {
        $phones = new PhoneNumberService();

        $this->assertSame('https://wa.me/351912345678', $phones->whatsappUrl('912345678'));
        $this->assertSame('https://wa.me/351912345678', $phones->whatsappUrl('912 345 678'));
        $this->assertSame('https://wa.me/351912345678', $phones->whatsappUrl('+351 912-345-678'));
        $this->assertSame('https://wa.me/351912345678', $phones->whatsappUrl('00351912345678'));
        // Estrangeiro com indicativo fica como está.
        $this->assertSame('https://wa.me/34612345678', $phones->whatsappUrl('+34 612 345 678'));
    }

    public function test_sem_numero_nao_ha_link(): void
    {
        $phones = new PhoneNumberService();

        $this->assertNull($phones->whatsappUrl(null));
        $this->assertNull($phones->whatsappUrl(''));
        $this->assertNull($phones->whatsappUrl('sem telefone'));
    }

    public function test_link_de_telefone_so_com_digitos_e_mais(): void
    {
        $phones = new PhoneNumberService();

        $this->assertSame('tel:912345678', $phones->telUrl('912 345 678'));
        $this->assertSame('tel:+351912345678', $phones->telUrl('+351 912 345 678'));
        $this->assertNull($phones->telUrl(' '));
    }
}
