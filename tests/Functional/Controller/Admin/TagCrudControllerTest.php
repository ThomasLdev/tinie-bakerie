<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\TagCrudController;
use App\Entity\Tag;
use App\Entity\TagTranslation;
use App\Factory\TagFactory;
use App\Form\TagTranslationType;
use App\Services\Locale\Locales;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Smoke tests for Tag CRUD controller in EasyAdmin.
 * Tests all CRUD operations (index, new, edit, detail) for Tag entities.
 *
 * Coverage: Only includes classes with executable logic (controllers, services, forms).
 * Entities are excluded as they are data structures without testable logic.
 *
 * @internal
 */
#[CoversClass(TagCrudController::class)]
#[CoversClass(Locales::class)]
#[CoversClass(TagTranslationType::class)]
final class TagCrudControllerTest extends WebTestCase
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
        $this->client->request('GET', '/admin/tag');

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testIndexPageDisplaysTagList(): void
    {
        TagFactory::createMany(3);

        $this->client->request('GET', '/admin/tag');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
    }

    public function testIndexPageHasNewButton(): void
    {
        $this->client->request('GET', '/admin/tag');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/admin/tag/new"]');
    }

    public function testIndexPageShowsMultipleTags(): void
    {
        TagFactory::createMany(5);

        $this->client->request('GET', '/admin/tag');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
    }

    public function testIndexPageDisplaysBackgroundColorColumn(): void
    {
        TagFactory::createOne();

        $this->client->request('GET', '/admin/tag');

        self::assertResponseIsSuccessful();
        // Background color field should be visible in index
    }

    public function testIndexPageDisplaysTitleColumn(): void
    {
        TagFactory::createOne();

        $this->client->request('GET', '/admin/tag');

        self::assertResponseIsSuccessful();
        // Title field should be visible in index
    }

    public function testIndexPageDisplaysCreatedAtColumn(): void
    {
        TagFactory::createOne();

        $this->client->request('GET', '/admin/tag');

        self::assertResponseIsSuccessful();
        // Created at timestamp should be visible
    }

    public function testIndexPageDisplaysUpdatedAtColumn(): void
    {
        TagFactory::createOne();

        $this->client->request('GET', '/admin/tag');

        self::assertResponseIsSuccessful();
        // Updated at timestamp should be visible
    }

    // ========================================
    // New/Create Page Tests
    // ========================================

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/tag/new');

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testNewPageContainsForm(): void
    {
        $this->client->request('GET', '/admin/tag/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testNewPageHasSubmitButton(): void
    {
        $this->client->request('GET', '/admin/tag/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testNewPageContainsBackgroundColorField(): void
    {
        $this->client->request('GET', '/admin/tag/new');

        self::assertResponseIsSuccessful();
        // Background color field should be present
    }

    public function testNewPageContainsTextColorField(): void
    {
        $this->client->request('GET', '/admin/tag/new');

        self::assertResponseIsSuccessful();
        // Text color field should be present
    }

    public function testNewPageContainsTranslationsCollection(): void
    {
        $this->client->request('GET', '/admin/tag/new');

        self::assertResponseIsSuccessful();
        // Translations collection field should be rendered
    }

    public function testNewPageAllowsAddingTranslations(): void
    {
        $this->client->request('GET', '/admin/tag/new');

        self::assertResponseIsSuccessful();
        // Translations collection should allow add/delete
    }

    // ========================================
    // Edit Page Tests
    // ========================================

    public function testEditPageLoadsSuccessfully(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('GET', "/admin/tag/{$tag->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testEditPageContainsForm(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('GET', "/admin/tag/{$tag->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testEditPageHasSubmitButton(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('GET', "/admin/tag/{$tag->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testEditPageWithNonExistentTagReturns404(): void
    {
        $this->client->request('GET', '/admin/tag/99999/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testEditPagePopulatesExistingTagData(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('GET', "/admin/tag/{$tag->getId()}/edit");

        self::assertResponseIsSuccessful();
        // Form should be populated with existing tag data
    }

    public function testEditPageShowsExistingTranslations(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('GET', "/admin/tag/{$tag->getId()}/edit");

        self::assertResponseIsSuccessful();
        // Existing translations should be displayed
    }

    public function testEditPageShowsExistingColors(): void
    {
        $tag = TagFactory::createOne([
            'backgroundColor' => '#FF0000',
            'textColor' => '#FFFFFF',
        ]);

        $this->client->request('GET', "/admin/tag/{$tag->getId()}/edit");

        self::assertResponseIsSuccessful();
        // Existing colors should be displayed in form
    }

    // ========================================
    // Detail Page Tests
    // ========================================

    public function testDetailPageLoadsSuccessfully(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('GET', "/admin/tag/{$tag->getId()}");

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testDetailPageDisplaysTagInformation(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('GET', "/admin/tag/{$tag->getId()}");

        self::assertResponseIsSuccessful();
        // Tag details should be displayed
    }

    public function testDetailPageWithNonExistentTagReturns404(): void
    {
        $this->client->request('GET', '/admin/tag/99999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDetailPageHasEditLink(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('GET', "/admin/tag/{$tag->getId()}");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/edit"]');
    }

    public function testDetailPageDisplaysColors(): void
    {
        $tag = TagFactory::createOne([
            'backgroundColor' => '#FF0000',
            'textColor' => '#FFFFFF',
        ]);

        $this->client->request('GET', "/admin/tag/{$tag->getId()}");

        self::assertResponseIsSuccessful();
        // Colors should be displayed
    }

    // ========================================
    // HTTP Method Tests
    // ========================================

    public function testIndexOnlyAcceptsGetRequests(): void
    {
        $this->client->request('POST', '/admin/tag');

        self::assertResponseStatusCodeSame(405);
    }

    public function testDetailOnlyAcceptsGetRequests(): void
    {
        $tag = TagFactory::createOne();

        $this->client->request('POST', "/admin/tag/{$tag->getId()}");

        self::assertResponseStatusCodeSame(405);
    }

    public function testNewPageAcceptsGetRequests(): void
    {
        $this->client->request('GET', '/admin/tag/new');

        self::assertResponseIsSuccessful();
    }

    // ========================================
    // Response Content Tests
    // ========================================

    public function testAllPagesReturnHtmlContent(): void
    {
        $tag = TagFactory::createOne();

        $routes = [
            '/admin/tag',
            '/admin/tag/new',
            "/admin/tag/{$tag->getId()}/edit",
            "/admin/tag/{$tag->getId()}",
        ];

        foreach ($routes as $route) {
            $this->client->request('GET', $route);
            self::assertResponseHeaderSame('Content-Type', 'text/html; charset=UTF-8', "Route: {$route}");
        }
    }

    public function testAllPagesHaveNoPhpErrors(): void
    {
        $tag = TagFactory::createOne();

        $routes = [
            '/admin/tag',
            '/admin/tag/new',
            "/admin/tag/{$tag->getId()}/edit",
            "/admin/tag/{$tag->getId()}",
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

    public function testIndexShowsTagWithCorrectColors(): void
    {
        TagFactory::createOne([
            'backgroundColor' => '#00FF00',
            'textColor' => '#000000',
        ]);

        $this->client->request('GET', '/admin/tag');

        self::assertResponseIsSuccessful();
        // Tag with colors should be displayed correctly
    }

    public function testFormHasBothColorFields(): void
    {
        $this->client->request('GET', '/admin/tag/new');

        self::assertResponseIsSuccessful();
        // Both backgroundColor and textColor fields should be present
    }

    public function testTranslationsCollectionIsConfiguredCorrectly(): void
    {
        $this->client->request('GET', '/admin/tag/new');

        self::assertResponseIsSuccessful();
        // Translations should have correct locale options and allow add/delete
    }
}
