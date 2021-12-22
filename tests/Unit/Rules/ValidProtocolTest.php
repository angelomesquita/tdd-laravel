<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidProtocol;
use PHPUnit\Framework\TestCase;

class ValidProtocolTest extends TestCase
{
    /** @test */
    public function it_only_allow_http_or_https()
    {
        $validProcotol = new ValidProtocol;
        $this->assertTrue($validProcotol->passes('url', 'https://google.com'));
        $this->assertTrue($validProcotol->passes('url', 'http://google.com'));
        $this->assertFalse($validProcotol->passes('url', 'httpsgoogle.com'));
        $this->assertFalse($validProcotol->passes('url', 'https:google.com'));
        $this->assertFalse($validProcotol->passes('url', 'ftp://google.com'));
        $this->assertFalse($validProcotol->passes('url', 'https:/google.com'));
        $this->assertFalse($validProcotol->passes('url', 'googlehttps://.com'));
    }

    /** @test */
    public function it_returns_the_proper_message()
    {
        $validProcotol = new ValidProtocol;
        $this->assertEquals(
            'The URL must include the protocol, e.g: http:// or https://.',
            $validProcotol->message()
        );
    }
}
