<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\PostController;
use App\Repository\PostRepository;
use App\Services\Cache\PostCache;
use Doctrine\ORM\EntityManagerInterface;
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
    private PostRepository $postRepository;

    private EntityManagerInterface $entityManager;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->postRepository = self::getContainer()->get(PostRepository::class);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public static function getPostControllerData(): \Generator
    {
        yield 'fr index post page' => ['fr', '/fr/articles'];

        yield 'en index post page' => ['en', '/en/posts'];
    }

    #[DataProvider('getPostControllerData')]
    public function testIndex(string $locale, string $baseUrl): void
    {
        $this->entityManager->getFilters()->enable('locale_filter')->setParameter('locale', $locale);

        $this->client->request(Request::METHOD_GET, $baseUrl);

        self::assertResponseIsSuccessful();
    }

    #[DataProvider('getPostControllerData')]
    public function testShowWithFoundPost(string $locale, string $baseUrl): void
    {
        $this->entityManager->getFilters()->enable('locale_filter')->setParameter('locale', $locale);

        $posts = $this->postRepository->findAllActive();

        self::assertNotEmpty($posts);

        $post = $posts[array_rand($posts)];

        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s/%s', $baseUrl, $post->getCategory()->getSlug(), $post->getSlug()),
        );

        self::assertResponseIsSuccessful();

        $title = $crawler
            ->filter(\sprintf('html:contains("%s")', $post->getTitle()))
            ->getNode(0)
            ->textContent;

        self::assertStringContainsString($post->getTitle(), $title);
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
        $this->entityManager->getFilters()->enable('locale_filter')->setParameter('locale', 'fr');

        $posts = $this->postRepository->findAllActive();

        self::assertNotEmpty($posts);

        $post = $posts[array_rand($posts)];

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('/fr/articles/bad-category-slug/%s', $post->getSlug()),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
