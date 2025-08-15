<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\HomePageController;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HomePageController::class)]
class HomepageControllerTest extends BaseControllerTestCase
{
//    public function testIndex(): void
//    {
//        $this->client->request('GET', '/en/');
//
//        self::assertResponseIsSuccessful();
//
//        $this->client->request('GET', '/fr/');
//
//        self::assertResponseIsSuccessful();
//    }
//
//    public function testNoLocaleIndex(): void
//    {
//        $this->client->request('GET', '/');
//
//        self::assertResponseRedirects('/en/');
//
//        $this->client->followRedirect();
//
//        self::assertResponseIsSuccessful();
//
//        $this->client->request('GET', '/', [], [], [
//            'HTTP_ACCEPT_LANGUAGE' => 'fr',
//        ]);
//
//        self::assertResponseRedirects('/fr/');
//
//        $this->client->followRedirect();
//
//        self::assertResponseIsSuccessful();
//    }
}
