<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();

        $client->request('GET', '/en/post');

        self::assertResponseIsSuccessful();
    }
}
