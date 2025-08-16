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
    /**
     * @return array<string, array<string, string>>
     */
    public static function getHomepageControllerData(): array
    {
        return [
            'en' => [
                '/en',
            ],
            'fr' => [
                '/fr',
            ],
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function getHomepageControllerRedirectData(): array
    {
        return [
            'no locale default value' => [
                '/',
                'en',
                '/en',
            ],
            'no locale, preferred fr' => [
                '/',
                'fr',
                '/fr',
            ],
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
