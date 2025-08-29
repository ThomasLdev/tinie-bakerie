<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\PostController;
use App\Repository\PostRepository;
use App\Services\Post\Cache\PostCache;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;

#[CoversClass(PostController::class)]
#[CoversClass(PostRepository::class)]
#[CoversClass(PostCache::class)]
class PostControllerTest extends BaseControllerTestCase
{
    private EntityManagerInterface $entityManager;

    private PostRepository $postRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->postRepository = static::getContainer()->get(PostRepository::class);
    }

    public static function getPostControllerIndexData(): Generator
    {
        yield 'fr index post page' => [
            '/fr/articles',
        ];

        yield 'en index post page' => [
            '/en/posts',
        ];
    }

    public static function getPostControllerShowData(): Generator
    {
        yield 'fr with found post' => [
            '/fr/articles',
            'fr',
            true,
            true,
            Response::HTTP_OK,
        ];

        yield 'en with found post' => [
            '/en/posts',
            'en',
            true,
            true,
            Response::HTTP_OK,
        ];

        yield 'fr without post' => [
            '/fr/articles',
            'fr',
            false,
            false,
            Response::HTTP_NOT_FOUND,
        ];

        yield 'en without post' => [
            '/en/posts',
            'en',
            false,
            false,
            Response::HTTP_NOT_FOUND,
        ];

        yield 'fr with post bad category slug' => [
            '/fr/articles',
            'fr',
            true,
            false,
            Response::HTTP_NOT_FOUND,
        ];

        yield 'en with post bad category slug' => [
            '/en/posts',
            'en',
            true,
            false,
            Response::HTTP_NOT_FOUND,
        ];
    }

    #[DataProvider('getPostControllerIndexData')]
    public function testIndex(string $baseUrl): void
    {
        $cache = static::getContainer()->get(CacheInterface::class);
        $cache->delete('posts_index_fr');
        $cache->delete('posts_index_en');

        $this->client->request(Request::METHOD_GET, $baseUrl);
        self::assertResponseIsSuccessful();
    }

    /**
     * @throws ORMException
     */
    #[DataProvider('getPostControllerShowData')]
    public function testShow(
        string $baseUrl,
        string $locale,
        bool $shouldFindPost,
        bool $shouldHaveCategory,
        int $expectedStatusCode,
    ): void {
        $post = $shouldFindPost ? $this->postRepository->findRandomPublished() : null;
        $postSlug = 'bad-post-slug';
        $categorySlug = 'bad-category-slug';

        $cache = static::getContainer()->get(CacheInterface::class);
        $cache->delete('posts_show_fr_'.$postSlug);

        if ($shouldFindPost) {
            $post->setLocale($locale);
            $this->entityManager->refresh($post);
            $postSlug = $post->getSlug();
        }

        if ($shouldFindPost && $shouldHaveCategory) {
            $category = $post->getCategory()->setLocale($locale);
            $this->entityManager->refresh($category);
            $categorySlug = $category->getSlug();
        }

        $crawler = $this->client->request(
            Request::METHOD_GET,
            sprintf('%s/%s/%s', $baseUrl, $categorySlug, $postSlug)
        );

        self::assertResponseStatusCodeSame($expectedStatusCode);

        if (Response::HTTP_NOT_FOUND === $expectedStatusCode) {
            return;
        }

        $title = $crawler
            ->filter(sprintf('html:contains("%s")', $post->getTitle()))
            ->getNode(0)
            ->textContent;

        self::assertStringContainsString($post->getTitle(), $title);
    }
}
