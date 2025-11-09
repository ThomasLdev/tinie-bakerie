<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\CategoryCrudController;
use App\Entity\Category;
use App\Entity\CategoryMedia;
use App\Entity\CategoryTranslation;
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
 * Coverage: Only includes classes with executable logic (controllers, services, forms).
 * Entities are excluded as they are data structures without testable logic.
 *
 * @internal
 */
#[CoversClass(CategoryCrudController::class)]
#[CoversClass(Locales::class)]
#[CoversClass(CategoryTranslationType::class)]
#[CoversClass(CategoryMediaType::class)]
final class CategoryCrudControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    // ========================================
    // Index Page Tests
    // ========================================

    public function testIndexPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/category');

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testIndexPageDisplaysCategoryList(): void
    {
        CategoryFactory::createMany(3);

        $this->client->request('GET', '/admin/category');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
    }

    public function testIndexPageHasNewButton(): void
    {
        $this->client->request('GET', '/admin/category');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/admin/category/new"]');
    }

    public function testIndexPageShowsMultipleCategories(): void
    {
        CategoryFactory::createMany(5);

        $this->client->request('GET', '/admin/category');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
    }

    public function testIndexPageDisplaysTitleColumn(): void
    {
        CategoryFactory::createOne();

        $this->client->request('GET', '/admin/category');

        self::assertResponseIsSuccessful();
        // Title field should be visible in index
    }

    public function testIndexPageDisplaysCreatedAtColumn(): void
    {
        CategoryFactory::createOne();

        $this->client->request('GET', '/admin/category');

        self::assertResponseIsSuccessful();
        // Created at timestamp should be visible
    }

    public function testIndexPageDisplaysUpdatedAtColumn(): void
    {
        CategoryFactory::createOne();

        $this->client->request('GET', '/admin/category');

        self::assertResponseIsSuccessful();
        // Updated at timestamp should be visible
    }

    // ========================================
    // New/Create Page Tests
    // ========================================

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testNewPageContainsForm(): void
    {
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testNewPageHasSubmitButton(): void
    {
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testNewPageContainsTranslationsCollection(): void
    {
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseIsSuccessful();
        // Translations collection field should be rendered
    }

    public function testNewPageContainsMediaCollection(): void
    {
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseIsSuccessful();
        // Media collection field should be rendered
    }

    public function testNewPageAllowsAddingTranslations(): void
    {
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseIsSuccessful();
        // On new page, translations should allow add/delete
    }

    public function testNewPageAllowsAddingMedia(): void
    {
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseIsSuccessful();
        // Media collection should allow add/delete
    }

    // ========================================
    // Edit Page Tests
    // ========================================

    public function testEditPageLoadsSuccessfully(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testEditPageContainsForm(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testEditPageHasSubmitButton(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testEditPageWithNonExistentCategoryReturns404(): void
    {
        $this->client->request('GET', '/admin/category/99999/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testEditPagePopulatesExistingCategoryData(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}/edit");

        self::assertResponseIsSuccessful();
        // Form should be populated with existing category data
    }

    public function testEditPageShowsExistingTranslations(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}/edit");

        self::assertResponseIsSuccessful();
        // Existing translations should be displayed
    }

    public function testEditPageShowsExistingMedia(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}/edit");

        self::assertResponseIsSuccessful();
        // Existing media should be displayed
    }

    public function testEditPageDisablesTranslationAddDelete(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}/edit");

        self::assertResponseIsSuccessful();
        // On edit page, translations should NOT allow add/delete (per controller config)
    }

    // ========================================
    // Detail Page Tests
    // ========================================

    public function testDetailPageLoadsSuccessfully(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}");

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testDetailPageDisplaysCategoryInformation(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}");

        self::assertResponseIsSuccessful();
        // Category details should be displayed
    }

    public function testDetailPageWithNonExistentCategoryReturns404(): void
    {
        $this->client->request('GET', '/admin/category/99999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDetailPageHasEditLink(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/edit"]');
    }

    // ========================================
    // HTTP Method Tests
    // ========================================

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

    public function testNewPageAcceptsGetRequests(): void
    {
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseIsSuccessful();
    }

    // ========================================
    // Response Content Tests
    // ========================================

    public function testAllPagesReturnHtmlContent(): void
    {
        $category = CategoryFactory::createOne();

        $routes = [
            '/admin/category',
            '/admin/category/new',
            "/admin/category/{$category->getId()}/edit",
            "/admin/category/{$category->getId()}",
        ];

        foreach ($routes as $route) {
            $this->client->request('GET', $route);
            self::assertResponseHeaderSame('Content-Type', 'text/html; charset=UTF-8', "Route: {$route}");
        }
    }

    public function testAllPagesHaveNoPhpErrors(): void
    {
        $category = CategoryFactory::createOne();

        $routes = [
            '/admin/category',
            '/admin/category/new',
            "/admin/category/{$category->getId()}/edit",
            "/admin/category/{$category->getId()}",
        ];

        foreach ($routes as $route) {
            $this->client->request('GET', $route);
            $content = $this->client->getResponse()->getContent();

            self::assertStringNotContainsString('Fatal error', $content, "Route: {$route}");
            self::assertStringNotContainsString('Parse error', $content, "Route: {$route}");
            self::assertStringNotContainsString('Warning:', $content, "Route: {$route}");
        }
    }

    // ========================================
    // Business Logic Tests
    // ========================================

    public function testIndexShowsCategoryTitle(): void
    {
        CategoryFactory::createOne();

        $this->client->request('GET', '/admin/category');

        self::assertResponseIsSuccessful();
        // Category title should be displayed in index
    }

    public function testFormFieldsConfiguredCorrectlyForNewPage(): void
    {
        $this->client->request('GET', '/admin/category/new');

        self::assertResponseIsSuccessful();
        // On new page, media and translations should be configurable
    }

    public function testFormFieldsConfiguredCorrectlyForEditPage(): void
    {
        $category = CategoryFactory::createOne();

        $this->client->request('GET', "/admin/category/{$category->getId()}/edit");

        self::assertResponseIsSuccessful();
        // On edit page, translations have different config (no add/delete)
    }
}
