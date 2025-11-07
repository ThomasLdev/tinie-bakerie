<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @internal
 */
abstract class BaseControllerTestCase extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    protected KernelBrowser $client;

    protected Container $container;

    protected function setUp(): void
    {
        $this->client = self::createClient(
            options: [
                'environment' => 'test',
                'debug' => false,
            ],
            server: [
                'HTTP_HOST' => 'local.tinie-bakerie.com',
                'HTTPS' => 'on',
            ],
        );

        $this->container = self::getContainer();
    }
}
