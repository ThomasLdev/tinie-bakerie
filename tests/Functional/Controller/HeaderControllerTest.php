<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\HeaderController;
use App\EventSubscriber\KernelRequestSubscriber;
use App\Repository\CategoryRepository;
use App\Services\Cache\CategoryCache;
use App\Services\Cache\HeaderCache;
use App\Services\Filter\LocaleFilter;
use App\Tests\Story\CategoryControllerTestStory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(HeaderController::class)]
#[CoversClass(HeaderCache::class)]
#[CoversClass(CategoryRepository::class)]
#[CoversClass(CategoryCache::class)]
#[CoversClass(LocaleFilter::class)]
#[CoversClass(KernelRequestSubscriber::class)]
final class HeaderControllerTest extends BaseControllerTestCase
{
    private const string HEADER_URL = '/header';

    public function testRenderHeaderReturnsSuccessfulResponse(): void
    {
        $this->client->request(Request::METHOD_GET, self::HEADER_URL);

        self::assertResponseIsSuccessful();
    }

    public function testRenderHeaderRendersCorrectTemplate(): void
    {
        $this->client->request(Request::METHOD_GET, self::HEADER_URL);

        self::assertResponseIsSuccessful();

        // Verify header template elements are present
        self::assertSelectorExists('[data-test-id="header"]', 'Header element should be present');
        self::assertSelectorExists('[data-test-id="navbar"]', 'Navigation element should be present');
    }

    public function testRenderHeaderWithNoCategories(): void
    {
        // Don't load story - empty database
        $this->client->request(Request::METHOD_GET, self::HEADER_URL);

        self::assertResponseIsSuccessful();

        // Categories dropdown should not be rendered when no categories exist
        self::assertSelectorNotExists('[data-dropdown-target="collapsable"] li', 'Category items should not be present when database is empty');
    }

    public function testRenderHeaderWithCategories(): void
    {
        $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());

        $crawler = $this->client->request(Request::METHOD_GET, self::HEADER_URL);

        self::assertResponseIsSuccessful();

        // Verify categories are passed to template and rendered (EN locale by default)
        $html = $crawler->html();

        self::assertStringContainsString('Test Category 1 EN', $html, 'Category 1 title should be present in header');
        self::assertStringContainsString('Test Category 2 EN', $html, 'Category 2 title should be present in header');
    }

    public function testRenderHeaderCategoriesAreOrderedByCreatedAtDesc(): void
    {
        $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());

        $crawler = $this->client->request(Request::METHOD_GET, self::HEADER_URL);

        self::assertResponseIsSuccessful();

        // Category 2 was created after Category 1, so it should appear first (DESC order)
        $html = $crawler->html();
        $category2Pos = strpos($html, 'Test Category 2 EN');
        $category1Pos = strpos($html, 'Test Category 1 EN');

        self::assertNotFalse($category2Pos, 'Category 2 should be present');
        self::assertNotFalse($category1Pos, 'Category 1 should be present');
        self::assertLessThan(
            $category1Pos,
            $category2Pos,
            'Category 2 (newer) should appear before Category 1 (older) in HTML (ordered by createdAt DESC)',
        );
    }

    public function testRenderHeaderCategoriesContainRequiredSlugLinks(): void
    {
        $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());

        $this->client->request(Request::METHOD_GET, self::HEADER_URL);

        self::assertResponseIsSuccessful();

        // Verify category links are present with correct slugs (EN locale)
        self::assertSelectorExists('a[href*="test-category-1-en"]', 'Category 1 link should be present');
        self::assertSelectorExists('a[href*="test-category-2-en"]', 'Category 2 link should be present');
    }

    #[DataProvider('getHttpMethodsData')]
    public function testRenderHeaderRejectsNonGetMethods(string $method): void
    {
        $this->client->request($method, self::HEADER_URL);

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testRenderHeaderWithMultipleCategoriesShowsAllCategories(): void
    {
        /** @var CategoryControllerTestStory $story */
        $story = $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());
        $categories = $story->getCategories();
        $categoriesCount = \count($categories);

        $crawler = $this->client->request(Request::METHOD_GET, self::HEADER_URL);

        self::assertResponseIsSuccessful();

        // Count category items in the dropdown
        $categoryItems = $crawler->filter('[data-dropdown-target="collapsable"] li')->count();

        self::assertSame(
            $categoriesCount,
            $categoryItems,
            \sprintf('Expected %d category items in dropdown, found %d', $categoriesCount, $categoryItems),
        );
    }

    public function testRenderHeaderShowsCategoriesDropdown(): void
    {
        $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());

        $this->client->request(Request::METHOD_GET, self::HEADER_URL);

        self::assertResponseIsSuccessful();

        // Verify the categories dropdown structure exists
        self::assertSelectorExists('[data-controller="dropdown"]', 'Dropdown controller should be present');
        self::assertSelectorExists('[data-dropdown-target="collapsable"]', 'Categories collapsable menu should be present');
    }

    public function testRenderHeaderUsesCacheService(): void
    {
        $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());

        // Make request to verify cache service is properly integrated
        $this->client->request(Request::METHOD_GET, self::HEADER_URL);
        self::assertResponseIsSuccessful();

        // Verify HeaderCache service is registered and accessible
        $container = self::getContainer();
        self::assertTrue($container->has(HeaderCache::class), 'HeaderCache service should be registered');

        $cache = $container->get(HeaderCache::class);
        self::assertInstanceOf(HeaderCache::class, $cache, 'Should be able to retrieve HeaderCache from container');

        // Verify cache returns expected data
        $categories = $cache->getCategories('en');
        self::assertIsArray($categories, 'Cache should return array of categories');
        self::assertNotEmpty($categories, 'Cache should contain categories from database');
    }

    public static function getHttpMethodsData(): \Generator
    {
        yield 'POST method' => [Request::METHOD_POST];

        yield 'PUT method' => [Request::METHOD_PUT];

        yield 'DELETE method' => [Request::METHOD_DELETE];

        yield 'PATCH method' => [Request::METHOD_PATCH];
    }
}
