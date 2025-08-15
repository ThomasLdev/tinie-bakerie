<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\PostController;
use App\Entity\Post;
use App\Repository\PostRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(PostController::class)]
#[CoversClass(PostRepository::class)]
class PostControllerTest extends BaseControllerTestCase
{
    private PostRepository $postRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->postRepository = static::getContainer()->get(PostRepository::class);
    }

    /**
     * @return array<'fr'|'en', array<'baseUrl', string>>
     */
    public static function getShowIndexData(): array
    {
        return [
            'fr' => [
                [
                    'baseUrl' => '/fr/articles',
                    'locale' => 'fr',
                ],
            ],
            'en' => [
                [
                    'baseUrl' => '/en/posts',
                    'locale' => 'en',
                ],
            ],
        ];
    }

    #[DataProvider('getShowIndexData')]
    public function testIndex(array $localizedData): void
    {
        $this->client->request(Request::METHOD_GET, $localizedData['baseUrl']);
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('getShowIndexData')]
    public function testShow(array $localizedData): void
    {
        /** @var Post $post */
        $post = $this->postRepository->findOneBy([]);

        $post->setLocale($localizedData['locale']);
        $url = sprintf('%s/%s/%s', $localizedData['baseUrl'], $post->getCategory()->getSlug(), $post->getSlug());

        $crawler = $this->client->request(Request::METHOD_GET, $url);

        self::assertResponseIsSuccessful();

        $crawler->filter(sprintf('html:contains("%s")', $post->getTitle()));
    }
}
