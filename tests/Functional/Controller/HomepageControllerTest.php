<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\HomePageController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(HomePageController::class)]
class HomepageControllerTest extends BaseControllerTestCase
{
    public static function getHomepageControllerData(): \Generator
    {
        yield 'get homepage controller for en locale' => [
            '/en',
        ];

        yield 'get homepage controller for fr locale' => [
            '/fr',
        ];
    }

    public static function getHomepageControllerRedirectData(): \Generator
    {
        yield 'no locale default value' => [
            '/',
            'en',
            '/en',
        ];

        yield 'no locale, preferred fr' => [
            '/',
            'fr',
            '/fr',
        ];
    }

    #[DataProvider('getHomepageControllerData')]
    public function testIndex(string $uri): void
    {
        $this->client->request(Request::METHOD_GET, $uri);

        self::assertResponseIsSuccessful();
    }

    #[DataProvider('getHomepageControllerRedirectData')]
    public function testNoLocaleIndex(string $uri, string $browserLocale, string $expected): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $uri, [], [],
            [
                'HTTP_ACCEPT_LANGUAGE' => $browserLocale,
            ]
        );

        self::assertResponseRedirects($expected);

        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
    }
}
