<?php

namespace App\Tests\Functional;

use Symfony\Component\Panther\PantherTestCase;

class EndToEndTest extends PantherTestCase
{
    public function testTrue()
    {
        $client = $this->createClient();
        $client->request('GET', '/');

        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}
