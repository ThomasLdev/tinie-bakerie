<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\PostController;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(PostController::class)]
#[CoversClass(PostRepository::class)]
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

    /**
     * @return array<'fr'|'en', array<'baseUrl', string>>
     */
    public static function getPostControllerIndexData(): array
    {
        return [
            'fr index post page' => [
                '/fr/articles',
            ],
            'en index post page' => [
                '/en/posts',
            ],
        ];
    }

    /**
     * @return array<'fr'|'en', array<'baseUrl', string>>
     */
    public static function getPostControllerShowData(): array
    {
        return [
            'fr with found post' => [
                '/fr/articles',
                'fr',
                true,
                true,
                Response::HTTP_OK,
            ],
            'en with found post' => [
                '/en/posts',
                'en',
                true,
                true,
                Response::HTTP_OK,
            ],
            'fr without post' => [
                '/fr/articles',
                'fr',
                false,
                false,
                Response::HTTP_NOT_FOUND,
            ],
            'en without post' => [
                '/en/posts',
                'en',
                false,
                false,
                Response::HTTP_NOT_FOUND,
            ],
            'fr with post bad category slug' => [
                '/fr/articles',
                'fr',
                true,
                false,
                Response::HTTP_NOT_FOUND,
            ],
            'en with post bad category slug' => [
                '/en/posts',
                'en',
                true,
                false,
                Response::HTTP_NOT_FOUND,
            ],
        ];
    }

    #[DataProvider('getPostControllerIndexData')]
    public function testIndex(string $baseUrl): void
    {
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
