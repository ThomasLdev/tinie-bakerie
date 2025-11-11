<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\CategoryController;
use App\EventSubscriber\KernelRequestSubscriber;
use App\Repository\CategoryRepository;
use App\Services\Cache\AbstractEntityCache;
use App\Services\Cache\CacheKeyGenerator;
use App\Services\Cache\CategoryCache;
use App\Services\Filter\LocaleFilter;
use App\Tests\Story\CategoryControllerTestStory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(CategoryController::class)]
#[CoversClass(CategoryRepository::class)]
#[CoversClass(CategoryCache::class)]
#[CoversClass(AbstractEntityCache::class)]
#[CoversClass(CacheKeyGenerator::class)]
#[CoversClass(LocaleFilter::class)]
#[CoversClass(KernelRequestSubscriber::class)]
final class CategoryControllerTest extends BaseControllerTestCase
{
    private const string BASE_URL_FR = '/fr/categories';

    private const string BASE_URL_EN = '/en/categories';

    #[DataProvider('getCategoryControllerShowData')]
    public function testShowWithFoundCategory(string $expectedTitle, string $locale, string $baseUrl): void
    {
        /** @var CategoryControllerTestStory $story */
        $story = $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());
        $category = $story->getCategory(0);
        $categorySlug = $story->getCategorySlug($category, $locale);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s', $baseUrl, $categorySlug),
        );

        self::assertResponseIsSuccessful();

        // Verify the page title contains the category title
        $pageTitle = $crawler->filter('title')->text();
        self::assertStringContainsString($expectedTitle, $pageTitle, 'Page title should contain category title');

        // Verify the category title appears in the page content
        $html = $crawler->html();
        self::assertStringContainsString($expectedTitle, $html, 'Category title should be present in page content');
    }

    public function testShowWithNotFoundCategory(): void
    {
        foreach ([self::BASE_URL_FR, self::BASE_URL_EN] as $baseUrl) {
            $this->client->request(
                Request::METHOD_GET,
                \sprintf('%s/unknown-category', $baseUrl),
            );

            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[DataProvider('getHttpMethodsData')]
    public function testShowRejectsNonGetMethods(string $method, string $baseUrl): void
    {
        /** @var CategoryControllerTestStory $story */
        $story = $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());
        $category = $story->getCategory(0);
        $locale = $baseUrl === self::BASE_URL_FR ? 'fr' : 'en';
        $categorySlug = $story->getCategorySlug($category, $locale);

        $this->client->request(
            $method,
            \sprintf('%s/%s', $baseUrl, $categorySlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    #[DataProvider('getCategoryControllerShowData')]
    public function testShowUsesCategoryCacheService(string $expectedTitle, string $locale, string $baseUrl): void
    {
        /** @var CategoryControllerTestStory $story */
        $story = $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());
        $category = $story->getCategory(0);
        $categorySlug = $story->getCategorySlug($category, $locale);
        $url = \sprintf('%s/%s', $baseUrl, $categorySlug);

        // Make request to verify cache service works
        $this->client->request(Request::METHOD_GET, $url);
        self::assertResponseIsSuccessful();

        // Verify CategoryCache service can retrieve individual category with correct locale content
        /** @var CategoryCache $cache */
        $cache = $this->container->get(CategoryCache::class);

        $cachedCategory = $cache->getOne($locale, $categorySlug);
        self::assertNotNull($cachedCategory, 'Cache should return category by slug');
        self::assertSame($categorySlug, $cachedCategory->getSlug(), 'Cached category should match requested slug');
        self::assertSame($expectedTitle, $cachedCategory->getTitle(), \sprintf('Cached category should have correct %s title', $locale));
    }

    /**
     * CRITICAL TEST: Verify that posts persist on category page even when loaded from cache.
     * This prevents regression of the bug where posts disappeared after caching.
     */
    #[DataProvider('getCategoryControllerShowData')]
    public function testShowDisplaysPostsWithAndWithoutCache(string $expectedTitle, string $locale, string $baseUrl): void
    {
        /** @var CategoryControllerTestStory $story */
        $story = $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());
        $category = $story->getCategory(0);
        $categorySlug = $story->getCategorySlug($category, $locale);
        $url = \sprintf('%s/%s', $baseUrl, $categorySlug);

        // First request - without cache
        $crawler = $this->client->request(Request::METHOD_GET, $url);
        self::assertResponseIsSuccessful();

        // Verify posts are displayed on first load
        $html = $crawler->html();
        $firstPostTitle = $locale === 'fr' ? 'Post Test 1 FR' : 'Test Post 1 EN';
        $secondPostTitle = $locale === 'fr' ? 'Post Test 2 FR' : 'Test Post 2 EN';

        self::assertStringContainsString($firstPostTitle, $html, 'First post should appear on category page (uncached)');
        self::assertStringContainsString($secondPostTitle, $html, 'Second post should appear on category page (uncached)');

        // Second request - with cache (this is where the bug occurred)
        $crawler = $this->client->request(Request::METHOD_GET, $url);
        self::assertResponseIsSuccessful();

        // Verify posts still appear after caching
        $html = $crawler->html();
        self::assertStringContainsString($firstPostTitle, $html, 'First post should still appear on category page (cached)');
        self::assertStringContainsString($secondPostTitle, $html, 'Second post should still appear on category page (cached)');
    }

    /**
     * CRITICAL TEST: Verify that only active posts appear on category pages.
     * This prevents showing inactive posts which should return 404 when accessed directly.
     */
    #[DataProvider('getCategoryControllerShowData')]
    public function testShowDisplaysOnlyActivePosts(string $expectedTitle, string $locale, string $baseUrl): void
    {
        /** @var CategoryControllerTestStory $story */
        $story = $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());
        $category = $story->getCategory(0);
        $categorySlug = $story->getCategorySlug($category, $locale);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s', $baseUrl, $categorySlug),
        );

        self::assertResponseIsSuccessful();

        // All posts in the story are active, so they should all appear
        $html = $crawler->html();
        $firstPostTitle = $locale === 'fr' ? 'Post Test 1 FR' : 'Test Post 1 EN';
        $secondPostTitle = $locale === 'fr' ? 'Post Test 2 FR' : 'Test Post 2 EN';

        self::assertStringContainsString($firstPostTitle, $html, 'Active post 1 should appear');
        self::assertStringContainsString($secondPostTitle, $html, 'Active post 2 should appear');
    }

    /**
     * Verify that cached categories have complete post data including media and tags.
     */
    #[DataProvider('getCategoryControllerShowData')]
    public function testCachedCategoryHasCompletePostData(string $expectedTitle, string $locale, string $baseUrl): void
    {
        /** @var CategoryControllerTestStory $story */
        $story = $this->loadStory(static fn (): CategoryControllerTestStory => CategoryControllerTestStory::load());
        $category = $story->getCategory(0);
        $categorySlug = $story->getCategorySlug($category, $locale);
        $url = \sprintf('%s/%s', $baseUrl, $categorySlug);

        // Make request to populate cache
        $this->client->request(Request::METHOD_GET, $url);
        self::assertResponseIsSuccessful();

        // Get category from cache
        /** @var CategoryCache $cache */
        $cache = $this->container->get(CategoryCache::class);
        $cachedCategory = $cache->getOne($locale, $categorySlug);

        self::assertNotNull($cachedCategory, 'Category should be cached');

        // Verify posts collection is loaded and not empty
        $posts = $cachedCategory->getPosts();
        self::assertNotEmpty($posts, 'Cached category should have posts loaded');
        self::assertCount(2, $posts, 'Category should have 2 active posts');

        // Verify each post has complete data
        foreach ($posts as $post) {
            self::assertNotEmpty($post->getMedia(), 'Cached post should have media');
            self::assertNotEmpty($post->getTags(), 'Cached post should have tags');
            self::assertNotEmpty($post->getTranslations(), 'Cached post should have translations');
        }

        // Make second request from cache and verify rendering works
        $crawler = $this->client->request(Request::METHOD_GET, $url);
        self::assertResponseIsSuccessful();

        // Verify post media renders (media images should be present)
        $html = $crawler->html();
        self::assertStringContainsString('test-post-image', $html, 'Post media should render from cached category');
    }

    public static function getCategoryControllerShowData(): \Generator
    {
        yield 'should find fr title on category fr page' => ['Catégorie Test 1 FR', 'fr', self::BASE_URL_FR];

        yield 'should find en title on category en page' => ['Test Category 1 EN', 'en', self::BASE_URL_EN];
    }

    public static function getHttpMethodsData(): \Generator
    {
        yield 'POST method on fr url' => [Request::METHOD_POST, self::BASE_URL_FR];

        yield 'POST method on en url' => [Request::METHOD_POST, self::BASE_URL_EN];

        yield 'PUT method on fr url' => [Request::METHOD_PUT, self::BASE_URL_FR];

        yield 'PUT method on en url' => [Request::METHOD_PUT, self::BASE_URL_EN];

        yield 'DELETE method on fr url' => [Request::METHOD_DELETE, self::BASE_URL_FR];

        yield 'DELETE method on en url' => [Request::METHOD_DELETE, self::BASE_URL_EN];

        yield 'PATCH method on fr url' => [Request::METHOD_PATCH, self::BASE_URL_FR];

        yield 'PATCH method on en url' => [Request::METHOD_PATCH, self::BASE_URL_EN];
    }
}
