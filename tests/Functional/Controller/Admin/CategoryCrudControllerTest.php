<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\CategoryCrudController;
use App\EventSubscriber\KernelRequestSubscriber;
use App\Factory\CategoryFactory;
use App\Form\CategoryMediaType;
use App\Form\CategoryTranslationType;
use App\Services\Locale\Locales;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Smoke tests for Category CRUD controller in EasyAdmin.
 * Tests all CRUD operations (index, new, edit, detail) for Category entities.
 *
 * @internal
 */
#[CoversClass(CategoryCrudController::class)]
#[CoversClass(Locales::class)]
#[CoversClass(CategoryTranslationType::class)]
#[CoversClass(CategoryMediaType::class)]
#[CoversClass(KernelRequestSubscriber::class)]
final class CategoryCrudControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testIndexPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/category');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
        self::assertSelectorExists('a[href*="/admin/category/new"]');
    }

    public function testIndexPageShowsMultipleCategories(): void
    {
        CategoryFactory::createMany(5);

        $this->client->request('GET', '/admin/category');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testEditPageWithNonExistentCategoryReturns404(): void
    {
        $this->client->request('GET', '/admin/category/99999/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDetailPageLoadsSuccessfully(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/edit"]');
    }

    public function testDetailPageWithNonExistentCategoryReturns404(): void
    {
        $this->client->request('GET', '/admin/category/99999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testIndexOnlyAcceptsGetRequests(): void
    {
        $this->client->request('POST', '/admin/category');

        self::assertResponseStatusCodeSame(405);
    }

    public function testDetailOnlyAcceptsGetRequests(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('POST', "/admin/category/{$category->getId()}");

        self::assertResponseStatusCodeSame(405);
    }
}
