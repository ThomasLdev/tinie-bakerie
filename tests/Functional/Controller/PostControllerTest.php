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
    public static function getPostControllerIndexData(): \Generator
    {
        yield 'fr post base url' => ['/fr/articles'];

        yield 'en post base url' => ['/en/posts'];
    }

    public static function getPostControllerShowData(): \Generator
    {
        yield 'fr post base url' => ['fr', '/fr/articles'];

        yield 'en post base url' => ['en', '/en/posts'];
    }

    #[DataProvider('getPostControllerIndexData')]
    public function testIndex(string $baseUrl): void
    {
        $this->loadStory(fn() => PostControllerTestStory::load());

        $this->client->request(Request::METHOD_GET, $baseUrl);

        self::assertResponseIsSuccessful();
        // TODO : count posts on index page, should be 2
    }

    #[DataProvider('getPostControllerShowData')]
    public function testShowWithFoundPost(string $locale, string $baseUrl): void
    {
        $story = PostControllerTestStory::load();
        $post = $story->getActivePost1();
        $postSlug = $story->getPostSlug($post, $locale);
        $postTitle = $post->getTitle();
        $categorySlug = $story->getCategorySlug($post->getCategory(), $locale);

        $this->entityManager->clear();

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s/%s', $baseUrl, $categorySlug, $postSlug),
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $postTitle);
    }

    /**
     * Note: Tests that expect 404 responses will show "NotFoundHttpException"
     * error messages in the output. This is expected behavior as Symfony logs
     * exceptions before converting them to HTTP responses.
     */
    public function testShowWithNotFoundPost(): void
    {
        $this->client->request(Request::METHOD_GET, '/fr/articles/unknown-category/unknown-post');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testShowWithBadCategorySlug(): void
    {
        $story = $this->loadStory(fn() => PostControllerTestStory::load());
        $post = $story->getActivePost1();

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/fr/articles/bad-category-slug/%s', $post->getSlug()),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
