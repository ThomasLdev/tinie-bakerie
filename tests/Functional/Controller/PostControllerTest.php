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

        $client->request('GET', '/fr/article');

        self::assertResponseIsSuccessful();
    }

    public function testShow(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/post/bakery/fresh-bread');

        self::assertResponseIsSuccessful();

        $crawler->filter('html:contains("Fresh Bread")');

        $crawler = $client->request('GET', '/fr/article/boulangerie/pain-frais');

        self::assertResponseIsSuccessful();

        $crawler->filter('html:contains("Pain Frais")');
    }
}
