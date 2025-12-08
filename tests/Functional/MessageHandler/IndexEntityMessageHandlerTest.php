<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Message\IndexEntityMessage;
use App\MessageHandler\IndexEntityMessageHandler;
use App\Serializer\Normalizer\PostNormalizer;
use App\Services\Search\EntityIndexer;
use App\Services\Search\IndexNameResolver;
use App\Services\Search\MeilisearchIndexer;
use App\Tests\Story\MeilisearchIndexingStory;
use Meilisearch\Client;
use Meilisearch\Contracts\TasksQuery;
use Meilisearch\Exceptions\ApiException;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @internal
 */
#[CoversClass(IndexEntityMessageHandler::class)]
#[CoversClass(PostNormalizer::class)]
#[CoversClass(IndexEntityMessage::class)]
#[CoversClass(EntityIndexer::class)]
#[CoversClass(IndexNameResolver::class)]
#[CoversClass(MeilisearchIndexer::class)]
final class IndexEntityMessageHandlerTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private MessageBusInterface $messageBus;
    private Client $meilisearchClient;
    private string $indexPrefix;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $this->messageBus = $container->get(MessageBusInterface::class);
        $this->meilisearchClient = $container->get(Client::class);
        $this->indexPrefix = $container->getParameter('meilisearch.prefix');

        $this->clearMeilisearchIndexes();
    }

    public function testIndexesPostInFrenchIndex(): void
    {
        $story = MeilisearchIndexingStory::load();
        $post = $story->getPostWithBothLocales();

        $this->dispatchIndexMessage(Post::class, $post->getId());

        $document = $this->getDocument('posts_fr', $post->getId());

        self::assertNotNull($document);
        self::assertSame($post->getId(), $document['id']);
        self::assertSame('fr', $document['locale']);
        self::assertSame(MeilisearchIndexingStory::FR_TITLE, $document['title']);
        self::assertSame(MeilisearchIndexingStory::FR_EXCERPT, $document['excerpt']);
        self::assertSame(MeilisearchIndexingStory::CATEGORY_FR_TITLE, $document['categoryTitle']);
        self::assertContains(MeilisearchIndexingStory::TAG_FR, $document['tags']);
    }

    public function testIndexesPostInEnglishIndex(): void
    {
        $story = MeilisearchIndexingStory::load();
        $post = $story->getPostWithBothLocales();

        $this->dispatchIndexMessage(Post::class, $post->getId());

        $document = $this->getDocument('posts_en', $post->getId());

        self::assertNotNull($document);
        self::assertSame($post->getId(), $document['id']);
        self::assertSame('en', $document['locale']);
        self::assertSame(MeilisearchIndexingStory::EN_TITLE, $document['title']);
        self::assertSame(MeilisearchIndexingStory::EN_EXCERPT, $document['excerpt']);
        self::assertSame(MeilisearchIndexingStory::CATEGORY_EN_TITLE, $document['categoryTitle']);
        self::assertContains(MeilisearchIndexingStory::TAG_EN, $document['tags']);
    }

    public function testSkipsIndexingForLocaleWithoutTranslation(): void
    {
        $story = MeilisearchIndexingStory::load();
        $post = $story->getPostWithFrenchOnly();

        $this->dispatchIndexMessage(Post::class, $post->getId());

        $frDocument = $this->getDocument('posts_fr', $post->getId());
        self::assertNotNull($frDocument);

        $enDocument = $this->getDocument('posts_en', $post->getId());
        self::assertNull($enDocument);
    }

    public function testSkipsNonExistentEntity(): void
    {
        $this->dispatchIndexMessage(Post::class, 999999);

        self::assertNull($this->getDocument('posts_fr', 999999));
        self::assertNull($this->getDocument('posts_en', 999999));
    }

    public function testSkipsNonSearchableEntity(): void
    {
        $this->dispatchIndexMessage(PostTranslation::class, 1);

        self::assertNull($this->getDocument('posts_fr', 1));
        self::assertNull($this->getDocument('posts_en', 1));
    }

    private function clearMeilisearchIndexes(): void
    {
        foreach (['posts_fr', 'posts_en'] as $index) {
            $task = $this->meilisearchClient->deleteIndex($this->indexPrefix . $index);
            $this->meilisearchClient->waitForTask($task['taskUid']);
        }
    }

    private function getDocument(string $indexName, int $id): ?array
    {
        $this->waitForPendingTasks();

        try {
            return (array) $this->meilisearchClient->index($this->indexPrefix . $indexName)->getDocument($id);
        } catch (ApiException $e) {
            if ($e->httpStatus === 404) {
                return null;
            }
            throw $e;
        }
    }

    private function dispatchIndexMessage(string $class, int $id): void
    {
        $this->messageBus->dispatch(new IndexEntityMessage($class, $id));
    }

    private function waitForPendingTasks(): void
    {
        $query = (new TasksQuery())->setStatuses(['enqueued', 'processing']);
        $tasks = $this->meilisearchClient->getTasks($query);

        foreach ($tasks->getResults() as $task) {
            $this->meilisearchClient->waitForTask($task['uid']);
        }
    }
}
