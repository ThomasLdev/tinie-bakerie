<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\PostController;
use App\Entity\Post;
use App\Repository\PostRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

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

//    #[DataProvider('getShowIndexData')]
//    public function testIndex(array $localizedData): void
//    {
//        $this->client->request('GET', $localizedData['baseUrl']);
//        self::assertResponseIsSuccessful();
//    }

    #[DataProvider('getShowIndexData')]
    public function testShow(array $localizedData): void
    {
        /** @var Post $post */
        $post = $this->postRepository->findOneBy([]);

        $post->setLocale($localizedData['locale']);
        $url = $localizedData['baseUrl'] . '/' . $post->getCategory()->setLocale($localizedData['locale'])->getSlug() . '/' . $post->getSlug();

        $crawler = $this->client->request('GET', $url);

        self::assertResponseIsSuccessful();

        $crawler->filter(sprintf('html:contains("%s")', $post->getTitle()));
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
                ]
            ],
            'en' => [
                [
                    'baseUrl' => '/en/posts',
                    'locale' => 'en',
                ]
            ],
        ];
    }
}
