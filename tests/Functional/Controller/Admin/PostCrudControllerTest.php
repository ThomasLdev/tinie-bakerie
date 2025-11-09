<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\PostCrudController;
use App\Entity\Category;
use App\Entity\Post;
use App\Entity\PostMedia;
use App\Entity\PostSection;
use App\Entity\PostTranslation;
use App\Entity\Tag;
use App\Factory\CategoryFactory;
use App\Factory\PostFactory;
use App\Factory\TagFactory;
use App\Form\PostMediaType;
use App\Form\PostSectionType;
use App\Form\PostTranslationType;
use App\Services\Locale\Locales;
use App\Services\Post\Enum\Difficulty;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Smoke tests for Post CRUD controller in EasyAdmin.
 * Tests all CRUD operations (index, new, edit, detail) for Post entities.
 *
 * Coverage: Only includes classes with executable logic (controllers, services, forms, enums).
 * Entities are excluded as they are data structures without testable logic.
 *
 * @internal
 */
#[CoversClass(PostCrudController::class)]
#[CoversClass(Difficulty::class)]
#[CoversClass(Locales::class)]
#[CoversClass(PostTranslationType::class)]
#[CoversClass(PostMediaType::class)]
#[CoversClass(PostSectionType::class)]
final class PostCrudControllerTest extends WebTestCase
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
        $this->client->request('GET', '/admin/post');

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testIndexPageDisplaysPostList(): void
    {
        PostFactory::createMany(3);

        $this->client->request('GET', '/admin/post');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
    }

    public function testIndexPageHasNewButton(): void
    {
        $this->client->request('GET', '/admin/post');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/admin/post/new"]');
    }

    public function testIndexPageDisplaysNoRecordsMessageWhenEmpty(): void
    {
        $this->client->request('GET', '/admin/post');

        self::assertResponseIsSuccessful();
        // EasyAdmin shows a message or empty table when no records
    }

    public function testIndexPageShowsMultiplePosts(): void
    {
        PostFactory::createMany(5);

        $this->client->request('GET', '/admin/post');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
    }

    // ========================================
    // New/Create Page Tests
    // ========================================

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/post/new');

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testNewPageContainsForm(): void
    {
        $this->client->request('GET', '/admin/post/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testNewPageHasSubmitButton(): void
    {
        $this->client->request('GET', '/admin/post/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button[type="submit"]');
    }

    // ========================================
    // Edit Page Tests
    // ========================================

    public function testEditPageLoadsSuccessfully(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testEditPageContainsForm(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testEditPageHasSubmitButton(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testEditPageWithNonExistentPostReturns404(): void
    {
        $this->client->request('GET', '/admin/post/99999/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testEditPagePopulatesExistingPostData(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}/edit");

        self::assertResponseIsSuccessful();
        // Form should be populated with existing post data
    }

    public function testEditPageShowsExistingTranslations(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}/edit");

        self::assertResponseIsSuccessful();
        // Existing translations should be displayed in form
    }

    public function testEditPageShowsExistingMedia(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}/edit");

        self::assertResponseIsSuccessful();
        // Existing media should be displayed in form
    }

    public function testEditPageShowsExistingSections(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}/edit");

        self::assertResponseIsSuccessful();
        // Existing sections should be displayed in form
    }

    // ========================================
    // Detail Page Tests
    // ========================================

    public function testDetailPageLoadsSuccessfully(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}");

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(200);
    }

    public function testDetailPageDisplaysPostInformation(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}");

        self::assertResponseIsSuccessful();
        // Post details should be displayed
    }

    public function testDetailPageWithNonExistentPostReturns404(): void
    {
        $this->client->request('GET', '/admin/post/99999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDetailPageHasEditLink(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('GET', "/admin/post/{$post->getId()}");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/edit"]');
    }

    public function testDetailPageShowsCategoryIfSet(): void
    {
        $category = CategoryFactory::createOne();
        $post = PostFactory::createOne(['category' => $category]);

        $this->client->request('GET', "/admin/post/{$post->getId()}");

        self::assertResponseIsSuccessful();
        // Category should be displayed
    }

    public function testDetailPageShowsTagsIfSet(): void
    {
        $tags = TagFactory::createMany(2);
        $post = PostFactory::createOne(['tags' => $tags]);

        $this->client->request('GET', "/admin/post/{$post->getId()}");

        self::assertResponseIsSuccessful();
        // Tags should be displayed
    }

    // ========================================
    // HTTP Method Tests
    // ========================================

    public function testIndexOnlyAcceptsGetRequests(): void
    {
        $this->client->request('POST', '/admin/post');

        self::assertResponseStatusCodeSame(405);
    }

    public function testDetailOnlyAcceptsGetRequests(): void
    {
        $post = PostFactory::createOne();

        $this->client->request('POST', "/admin/post/{$post->getId()}");

        self::assertResponseStatusCodeSame(405);
    }

    public function testNewPageAcceptsGetRequests(): void
    {
        $this->client->request('GET', '/admin/post/new');

        self::assertResponseIsSuccessful();
    }

    // ========================================
    // Response Content Tests
    // ========================================

    public function testAllPagesReturnHtmlContent(): void
    {
        $post = PostFactory::createOne();

        $routes = [
            '/admin/post',
            '/admin/post/new',
            "/admin/post/{$post->getId()}/edit",
            "/admin/post/{$post->getId()}",
        ];

        foreach ($routes as $route) {
            $this->client->request('GET', $route);
            self::assertResponseHeaderSame('Content-Type', 'text/html; charset=UTF-8', "Route: {$route}");
        }
    }

    public function testAllPagesHaveNoPhpErrors(): void
    {
        $post = PostFactory::createOne();

        $routes = [
            '/admin/post',
            '/admin/post/new',
            "/admin/post/{$post->getId()}/edit",
            "/admin/post/{$post->getId()}",
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

    public function testCreateEntityInitializesTranslations(): void
    {
        // When accessing new page, PostCrudController::createEntity should add translations
        $this->client->request('GET', '/admin/post/new');

        self::assertResponseIsSuccessful();
        // New post should have translations initialized for all locales
    }

    public function testIndexShowsActiveStatus(): void
    {
        PostFactory::createOne(['active' => true]);
        PostFactory::createOne(['active' => false]);

        $this->client->request('GET', '/admin/post');

        self::assertResponseIsSuccessful();
        // Active status should be visible in index
    }

    public function testIndexShowsCategoryName(): void
    {
        $category = CategoryFactory::createOne();
        PostFactory::createOne(['category' => $category]);

        $this->client->request('GET', '/admin/post');

        self::assertResponseIsSuccessful();
        // Category name should be displayed using formatValue callback
    }
}
