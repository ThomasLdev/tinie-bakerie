<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\PostController;
use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

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
    public static function getShowIndexData(): array
    {
        return [
            'fr' => [
                '/fr/articles',
                'fr',
            ],
            'en' => [
                '/en/posts',
                'en',
            ],
        ];
    }

    #[DataProvider('getShowIndexData')]
    public function testIndex(string $baseUrl): void
    {
        $this->client->request(Request::METHOD_GET, $baseUrl);
        self::assertResponseIsSuccessful();
    }

    /**
     * @throws ORMException
     */
    #[DataProvider('getShowIndexData')]
    public function testShow(string $baseUrl, string $locale): void
    {
        $post = $this->postRepository->findRandomPublished();

        if (!$post instanceof Post) {
            throw new RuntimeException('No post found for testing.');
        }

        $post->setLocale($locale);
        $this->entityManager->refresh($post);
        $category = $post->getCategory()->setLocale($locale);
        $this->entityManager->refresh($category);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            sprintf('%s/%s/%s', $baseUrl, $category->getSlug(), $post->getSlug())
        );

        self::assertResponseIsSuccessful();

        $crawler->filter(sprintf('html:contains("%s")', $post->getTitle()));
    }
}
