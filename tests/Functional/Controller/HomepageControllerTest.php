<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\HomePageController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(HomePageController::class)]
class HomepageControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();

        $client->request('GET', '/en/');

        self::assertResponseIsSuccessful();

        $client->request('GET', '/fr/');

        self::assertResponseIsSuccessful();
    }

    public function testNoLocaleIndex(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        self::assertResponseRedirects('/en/');

        $client->followRedirect();

        self::assertResponseIsSuccessful();

        $client->request('GET', '/', [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'fr',
        ]);

        self::assertResponseRedirects('/fr/');

        $client->followRedirect();

        self::assertResponseIsSuccessful();
    }
}
