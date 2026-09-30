<?php

namespace Tests\Unit;

use App\Services\ContactNormalizer;
use PHPUnit\Framework\TestCase;

class ContactNormalizerTest extends TestCase
{
    private ContactNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new ContactNormalizer();
    }

    public function test_international_formats_of_the_same_number_match(): void
    {
        foreach (['+49 171 123 4567', '+491711234567', '0049 171 123 4567', '+49 (171) 123-4567', '491711234567'] as $input) {
            $this->assertSame('491711234567', $this->normalizer->phone($input, 'DE'), $input);
        }
    }

    public function test_national_numbers_use_the_seller_country(): void
    {
        $this->assertSame('491711234567', $this->normalizer->phone('0171 1234567', 'DE'));
        $this->assertSame('351912345678', $this->normalizer->phone('912 345 678', 'PT'));
        $this->assertSame('351912345678', $this->normalizer->phone('351912345678', 'PT'));
        // Itália mantém o 0 no número internacional.
        $this->assertSame('390612345678', $this->normalizer->phone('06 1234 5678', 'IT'));
    }

    public function test_national_numbers_without_country_are_only_stripped(): void
    {
        $this->assertSame('01711234567', $this->normalizer->phone('0171 123 4567'));
        $this->assertSame('491711234567', $this->normalizer->phone('+49 171 123 4567'));
    }

    public function test_empty_or_too_short_numbers_are_null(): void
    {
        $this->assertNull($this->normalizer->phone(null));
        $this->assertNull($this->normalizer->phone('   '));
        $this->assertNull($this->normalizer->phone('123'));
    }

    public function test_email_and_domain(): void
    {
        $this->assertSame('joao@autohaus-muller.de', $this->normalizer->email('  Joao@Autohaus-Muller.de '));
        $this->assertSame('autohaus-muller.de', $this->normalizer->emailDomain('Joao@Autohaus-Muller.de'));
        $this->assertNull($this->normalizer->emailDomain('sem-arroba'));
        $this->assertNull($this->normalizer->email(''));
    }

    public function test_domains_accept_urls_and_lists(): void
    {
        $this->assertSame('autohaus-muller.de', $this->normalizer->domain('https://www.Autohaus-Muller.de/kontakt'));
        $this->assertSame('autohaus-muller.de', $this->normalizer->domain('@autohaus-muller.de'));
        $this->assertNull($this->normalizer->domain('não é domínio'));
        $this->assertSame(
            ['autohaus-muller.de', 'autohaus-muller.com'],
            $this->normalizer->domains("autohaus-muller.de, www.autohaus-muller.com\nautohaus-muller.de")
        );
    }

    public function test_generic_email_providers(): void
    {
        $this->assertTrue($this->normalizer->isGenericDomain('gmx.de'));
        $this->assertTrue($this->normalizer->isGenericDomain('gmail.com'));
        $this->assertFalse($this->normalizer->isGenericDomain('autohaus-muller.de'));
    }
}
