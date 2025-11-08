<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\PostController;
use App\Repository\PostRepository;
use App\Services\Cache\PostCache;
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
final class PostControllerTest extends BaseControllerTestCase
{
    private const string BASE_URL_FR = '/fr/articles';

    private const string BASE_URL_EN = '/en/posts';

    #[DataProvider('getPostControllerIndexData')]
    public function testIndex(array $expected, string $baseUrl): void
    {
        /** @var PostControllerTestStory $story */
        $story = $this->loadStory(fn() => PostControllerTestStory::load());
        $activePosts = $story->getActivePosts();
        $activePostsCount = count($activePosts);

        $crawler = $this->client->request(Request::METHOD_GET, $baseUrl);

        self::assertResponseIsSuccessful();
        self::assertCount(
            $activePostsCount,
            $crawler->filter('[data-test-id^="post-card-"]'),
            sprintf('Expected %s active posts to be displayed on the index page', $activePostsCount)
        );

        foreach ($expected as $key => $title) {
            $selector = sprintf('[data-test-id="post-title-%s"]', $story->getActivePost($key)->getId());
            $text = $crawler->filter($selector)->text();

            self::assertSame($text, $title);
        }
    }

    #[DataProvider('getPostControllerShowData')]
    public function testShowWithFoundPost(string $expected, string $locale, string $baseUrl): void
    {
        /** @var PostControllerTestStory $story */
        $story = $this->loadStory(fn() => PostControllerTestStory::load());
        $post = $story->getActivePost(0);
        $postSlug = $story->getPostSlug($post, $locale);
        $categorySlug = $story->getCategorySlug($post->getCategory(), $locale);

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
        $story = $this->loadStory(fn() => PostControllerTestStory::load());
        $post = $story->getInactivePost();
        $postSlug = $story->getPostSlug($post, $locale);
        $categorySlug = $story->getCategorySlug($post->getCategory(), $locale);

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
                \sprintf('%s/bad-category-slug/%s', $baseUrl, 'unknown-category/unknown-post')
            );

            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[DataProvider('getPostControllerShowNotFoundData')]
    public function testShowWithBadCategorySlug(string $locale, string $baseUrl): void
    {
        /** @var PostControllerTestStory $story */
        $story = $this->loadStory(fn() => PostControllerTestStory::load());
        $post = $story->getActivePost(0);
        $postSlug = $story->getPostSlug($post, $locale);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/bad-category-slug/%s', $baseUrl, $postSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public static function getPostControllerIndexData(): \Generator
    {
        yield 'should find two posts on fr post index' => [['Article Test 1 FR', 'Article Test 2 FR'], self::BASE_URL_FR];

        yield 'should find two posts on en post index' => [['Test Post 1 EN', 'Test Post 2 EN'], self::BASE_URL_EN];
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
}
