<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\PostController;
use App\Entity\Post;
use App\EventSubscriber\KernelRequestSubscriber;
use App\Repository\PostRepository;
use App\Services\Cache\AbstractEntityCache;
use App\Services\Cache\CacheKeyGenerator;
use App\Services\Cache\PostCache;
use App\Services\Filter\LocaleFilter;
use App\Tests\Story\PostControllerTestStory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(PostController::class)]
#[CoversClass(PostRepository::class)]
#[CoversClass(PostCache::class)]
#[CoversClass(AbstractEntityCache::class)]
#[CoversClass(CacheKeyGenerator::class)]
#[CoversClass(LocaleFilter::class)]
#[CoversClass(KernelRequestSubscriber::class)]
final class PostControllerTest extends BaseControllerTestCase
{
    private const string BASE_URL_FR = '/fr/articles';

    private const string BASE_URL_EN = '/en/posts';

    /**
     * @param array<string> $expectedTitles
     */
    #[DataProvider('getPostControllerIndexData')]
    public function testIndex(array $expectedTitles, string $baseUrl): void
    {
        /** @var PostControllerTestStory $story */
        $story = $this->loadStory(static fn (): PostControllerTestStory => PostControllerTestStory::load());
        $activePosts = $story->getActivePosts();
        $activePostsCount = \count($activePosts);

        $crawler = $this->client->request(Request::METHOD_GET, $baseUrl);

        self::assertResponseIsSuccessful();

        // Verify correct number of posts are displayed
        $postCards = $crawler->filter('[data-test-id^="post-card-"]');
        self::assertCount(
            $activePostsCount,
            $postCards,
            \sprintf('Expected %s active posts to be displayed on the index page', $activePostsCount),
        );

        // Verify all expected titles are present and in the correct order (by createdAt DESC)
        $html = $crawler->html();

        foreach ($expectedTitles as $title) {
            self::assertStringContainsString($title, $html, \sprintf('Post title "%s" should be present', $title));
        }

        // Verify ordering: newer post (expectedTitles[0]) appears before older post (expectedTitles[1])
        $firstPos = strpos($html, (string) $expectedTitles[0]);
        $secondPos = strpos($html, (string) $expectedTitles[1]);

        self::assertLessThan(
            $secondPos,
            $firstPos,
            \sprintf(
                'Post "%s" (newer) should appear before "%s" (older) in HTML (ordered by createdAt DESC)',
                $expectedTitles[0],
                $expectedTitles[1],
            ),
        );
    }

    #[DataProvider('getPostControllerShowData')]
    public function testShowWithFoundPost(string $expected, string $locale, string $baseUrl): void
    {
        /** @var PostControllerTestStory $story */
        $story = $this->loadStory(static fn (): PostControllerTestStory => PostControllerTestStory::load());
        $post = $story->getActivePost(0);
        $postSlug = $story->getPostSlug($post, $locale);
        $category = $post->getCategory();
        self::assertNotNull($category, 'Post should have a category');
        $categorySlug = $story->getCategorySlug($category, $locale);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s/%s', $baseUrl, $categorySlug, $postSlug),
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('[data-test-id="post-show-title"]', $expected);
    }

    #[DataProvider('getPostControllerShowNotFoundData')]
    public function testShowWithInactivePost(string $locale, string $baseUrl): void
    {
        /** @var PostControllerTestStory $story */
        $story = $this->loadStory(static fn (): PostControllerTestStory => PostControllerTestStory::load());
        $post = $story->getInactivePost();
        $postSlug = $story->getPostSlug($post, $locale);
        $category = $post->getCategory();
        self::assertNotNull($category, 'Post should have a category');
        $categorySlug = $story->getCategorySlug($category, $locale);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s/%s', $baseUrl, $categorySlug, $postSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testShowWithNotFoundPost(): void
    {
        foreach ([self::BASE_URL_FR, self::BASE_URL_EN] as $baseUrl) {
            $this->client->request(
                Request::METHOD_GET,
                \sprintf('%s/bad-category-slug/%s', $baseUrl, 'unknown-category/unknown-post'),
            );

            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[DataProvider('getPostControllerShowNotFoundData')]
    public function testShowWithBadCategorySlug(string $locale, string $baseUrl): void
    {
        /** @var PostControllerTestStory $story */
        $story = $this->loadStory(static fn (): PostControllerTestStory => PostControllerTestStory::load());
        $post = $story->getActivePost(0);
        $postSlug = $story->getPostSlug($post, $locale);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/bad-category-slug/%s', $baseUrl, $postSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[DataProvider('getPostControllerBaseUrlData')]
    public function testIndexWithNoPosts(string $baseUrl): void
    {
        // Don't load story - empty database
        $this->client->request(Request::METHOD_GET, $baseUrl);

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('[data-test-id^="post-card-"]');
    }

    #[DataProvider('getHttpMethodsData')]
    public function testIndexRejectsNonGetMethods(string $method, string $baseUrl): void
    {
        $this->client->request($method, $baseUrl);

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    #[DataProvider('getHttpMethodsData')]
    public function testShowRejectsNonGetMethods(string $method, string $baseUrl): void
    {
        /** @var PostControllerTestStory $story */
        $story = $this->loadStory(static fn (): PostControllerTestStory => PostControllerTestStory::load());
        $post = $story->getActivePost(0);
        $locale = $baseUrl === self::BASE_URL_FR ? 'fr' : 'en';
        $postSlug = $story->getPostSlug($post, $locale);
        $category = $post->getCategory();
        self::assertNotNull($category, 'Post should have a category');
        $categorySlug = $story->getCategorySlug($category, $locale);

        $this->client->request(
            $method,
            \sprintf('%s/%s/%s', $baseUrl, $categorySlug, $postSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * @param array<string> $expectedTitles
     */
    #[DataProvider('getPostControllerIndexData')]
    public function testIndexUsesPostCacheService(array $expectedTitles, string $baseUrl): void
    {
        $this->loadStory(static fn (): PostControllerTestStory => PostControllerTestStory::load());

        // Make request to verify cache service is properly integrated
        $this->client->request(Request::METHOD_GET, $baseUrl);
        self::assertResponseIsSuccessful();

        /** @var PostCache $cache */
        $cache = $this->container->get(PostCache::class);

        // Verify cache returns expected locale-specific data
        $locale = $baseUrl === self::BASE_URL_FR ? 'fr' : 'en';
        $posts = $cache->get($locale);

        self::assertNotEmpty($posts, 'Cache should contain posts from database');

        // Verify cached posts contain expected locale-specific titles
        /** @var array<Post> $posts */
        $cachedTitles = array_map(static fn (Post $post): string => $post->getTitle(), $posts);

        foreach ($expectedTitles as $expectedTitle) {
            self::assertContains(
                $expectedTitle,
                $cachedTitles,
                \sprintf('Cache for locale "%s" should contain post with title "%s"', $locale, $expectedTitle),
            );
        }
    }

    #[DataProvider('getPostControllerShowData')]
    public function testShowUsesPostCacheService(string $expectedTitle, string $locale, string $baseUrl): void
    {
        /** @var PostControllerTestStory $story */
        $story = $this->loadStory(static fn (): PostControllerTestStory => PostControllerTestStory::load());
        $post = $story->getActivePost(0);
        $postSlug = $story->getPostSlug($post, $locale);
        $category = $post->getCategory();
        self::assertNotNull($category, 'Post should have a category');
        $categorySlug = $story->getCategorySlug($category, $locale);
        $url = \sprintf('%s/%s/%s', $baseUrl, $categorySlug, $postSlug);

        // Make request to verify cache service works
        $this->client->request(Request::METHOD_GET, $url);
        self::assertResponseIsSuccessful();

        // Verify PostCache service can retrieve individual post with correct locale content
        /** @var PostCache $cache */
        $cache = $this->container->get(PostCache::class);

        $cachedPost = $cache->getOne($locale, $postSlug);
        self::assertNotNull($cachedPost, 'Cache should return post by slug');
        self::assertSame($postSlug, $cachedPost->getSlug(), 'Cached post should match requested slug');
        self::assertSame($expectedTitle, $cachedPost->getTitle(), \sprintf('Cached post should have correct %s title', $locale));
    }

    public static function getPostControllerIndexData(): \Generator
    {
        // Posts are ordered by createdAt DESC, so Post 2 (newer) appears before Post 1 (older)
        yield 'should find two posts on fr post index' => [['Article Test 2 FR', 'Article Test 1 FR'], self::BASE_URL_FR];

        yield 'should find two posts on en post index' => [['Test Post 2 EN', 'Test Post 1 EN'], self::BASE_URL_EN];
    }

    public static function getPostControllerShowData(): \Generator
    {
        yield 'should find fr title on post fr page' => ['Article Test 1 FR', 'fr', self::BASE_URL_FR];

        yield 'should find en title on post en page' => ['Test Post 1 EN', 'en', self::BASE_URL_EN];
    }

    public static function getPostControllerShowNotFoundData(): \Generator
    {
        yield 'fr post base url' => ['fr', self::BASE_URL_FR];

        yield 'en post base url' => ['en', self::BASE_URL_EN];
    }

    public static function getPostControllerBaseUrlData(): \Generator
    {
        yield 'fr base url' => [self::BASE_URL_FR];

        yield 'en base url' => [self::BASE_URL_EN];
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

    /**
     * CRITICAL TEST: Verify controller rejects cross-locale slug combinations.
     * This is the exact bug discovered: French URL with English slug should return 404.
     */
    #[DataProvider('getCrossLocaleSlugData')]
    public function testShowRejectsCrossLocaleSlugCombination(string $urlLocale, string $urlBase, string $slugLocale): void
    {
        /** @var PostControllerTestStory $story */
        $story = $this->loadStory(static fn (): PostControllerTestStory => PostControllerTestStory::load());
        $post = $story->getActivePost(0);
        $category = $post->getCategory();
        self::assertNotNull($category, 'Post should have a category');

        // Get slugs for DIFFERENT locale than URL
        $categorySlug = $story->getCategorySlug($category, $slugLocale);
        $postSlug = $story->getPostSlug($post, $slugLocale);

        // Try to access with WRONG locale combination
        $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s/%s', $urlBase, $categorySlug, $postSlug),
        );

        self::assertResponseStatusCodeSame(
            Response::HTTP_NOT_FOUND,
            \sprintf(
                'Should reject %s URL with %s slug combination (%s/%s/%s)',
                $urlLocale,
                $slugLocale,
                $urlBase,
                $categorySlug,
                $postSlug,
            ),
        );
    }

    public static function getCrossLocaleSlugData(): \Generator
    {
        yield 'French URL with English slug' => ['fr', self::BASE_URL_FR, 'en'];

        yield 'English URL with French slug' => ['en', self::BASE_URL_EN, 'fr'];
    }
}
