<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseControllerTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient(
            options: [
                'environment' => 'test',
                'debug' => false
            ],
            server: [
            'HTTP_HOST' => 'local.tinie-bakerie.com',
            'HTTPS' => 'on',
            ]
        );
    }
}
