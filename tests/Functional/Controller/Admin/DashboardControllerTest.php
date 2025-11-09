<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\DashboardController;
use App\EventSubscriber\KernelRequestSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Smoke tests for EasyAdmin Dashboard.
 * Ensures the admin dashboard loads successfully.
 *
 * Note: Testing individual CRUD operations (new/edit/detail) requires proper
 * EasyAdmin context and is better suited for E2E tests with Playwright.
 * These smoke tests verify the dashboard is accessible and renders correctly.
 *
 * @internal
 */
#[CoversClass(DashboardController::class)]
#[CoversClass(KernelRequestSubscriber::class)]
final class DashboardControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testDashboardIndexReturns200(): void
    {
        $this->client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }
}
