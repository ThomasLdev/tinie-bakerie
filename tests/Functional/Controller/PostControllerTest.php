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

        $crawler = $client->request('GET', '/en/post/category-slug/post-slug');

        self::assertResponseIsSuccessful();
        $crawler->filter('html:contains("Post slug")');

        $client->request('GET', '/fr/article/category-slug/post-slug');

        self::assertResponseIsSuccessful();
    }
}
