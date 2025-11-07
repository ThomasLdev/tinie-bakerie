<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\PostController;
use App\Factory\CategoryFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\PostFactory;
use App\Factory\PostTranslationFactory;
use App\Repository\PostRepository;
use App\Services\Cache\PostCache;
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
    public static function getPostControllerData(): \Generator
    {
        yield 'fr index post page' => ['fr', '/fr/articles'];

        yield 'en index post page' => ['en', '/en/posts'];
    }

    #[DataProvider('getPostControllerData')]
    public function testIndex(string $locale, string $baseUrl): void
    {
        $this->client->request(Request::METHOD_GET, $baseUrl);

        self::assertResponseIsSuccessful();
    }

    #[DataProvider('getPostControllerData')]
    public function testShowWithFoundPost(string $locale, string $baseUrl): void
    {
        // Create test data: Category with Post using fixed titles
        $category = CategoryFactory::createOne([
            'translations' => CategoryTranslationFactory::createSequence([
                ['locale' => 'fr', 'title' => 'Catégorie Test FR'],
                ['locale' => 'en', 'title' => 'Test Category EN'],
            ]),
        ])->_real();

        $post = PostFactory::createOne([
            'active' => true,
            'category' => $category,
            'translations' => PostTranslationFactory::createSequence([
                ['locale' => 'fr', 'title' => 'Article Test FR'],
                ['locale' => 'en', 'title' => 'Test Post EN'],
            ]),
        ])->_real();

        // Get translations for the specific locale
        $categoryTranslation = $category->getTranslationByLocale($locale);
        $postTranslation = $post->getTranslationByLocale($locale);

        self::assertNotNull($categoryTranslation, "Category translation for locale '{$locale}' should exist");
        self::assertNotNull($postTranslation, "Post translation for locale '{$locale}' should exist");

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s/%s', $baseUrl, $categoryTranslation->getSlug(), $postTranslation->getSlug()),
        );

        // Verify the page loads successfully with the correct slugs
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('h1');
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
        // Create test data with fixed titles
        $category = CategoryFactory::createOne([
            'translations' => CategoryTranslationFactory::createSequence([
                ['locale' => 'fr', 'title' => 'Catégorie Test FR'],
                ['locale' => 'en', 'title' => 'Test Category EN'],
            ]),
        ])->_real();

        $post = PostFactory::createOne([
            'active' => true,
            'category' => $category,
            'translations' => PostTranslationFactory::createSequence([
                ['locale' => 'fr', 'title' => 'Article Test FR'],
                ['locale' => 'en', 'title' => 'Test Post EN'],
            ]),
        ])->_real();

        // Get FR translation
        $postTranslation = $post->getTranslationByLocale('fr');
        self::assertNotNull($postTranslation, 'Post translation for FR should exist');

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/fr/articles/bad-category-slug/%s', $postTranslation->getSlug()),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
