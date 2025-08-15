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
                [
                    'uri' => '/en',
                ],

            ],
            'fr' => [
                [
                    'uri' => '/fr',
                ]
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
                'uri' => '/',
                'browserLanguage' => 'en',
                'expectedRedirect' => '/en',
            ],
            'no locale, preferred fr' => [
                'uri' => '/',
                'browserLanguage' => 'fr',
                'expectedRedirect' => '/fr',
            ],
        ];
    }

    #[DataProvider('getHomepageControllerData')]
    public function testIndex(array $data): void
    {
        $this->client->request(Request::METHOD_GET, $data['uri']);

        self::assertResponseIsSuccessful();
    }

    #[DataProvider('getHomepageControllerRedirectData')]
    public function testNoLocaleIndex(array $localizedData): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $localizedData['uri'], [], [],
            [
                'HTTP_ACCEPT_LANGUAGE' => $localizedData['browserLanguage'] ?? '',
            ]
        );

        self::assertResponseRedirects($localizedData['expectedRedirect']);

        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
    }
}
