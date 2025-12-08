<?php

declare(strict_types=1);

namespace App\Tests\Functional\MessageHandler;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Message\IndexEntityMessage;
use App\Message\RemoveEntityFromIndexMessage;
use App\MessageHandler\RemoveEntityFromIndexMessageHandler;
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
#[CoversClass(RemoveEntityFromIndexMessageHandler::class)]
#[CoversClass(RemoveEntityFromIndexMessage::class)]
#[CoversClass(EntityIndexer::class)]
#[CoversClass(IndexNameResolver::class)]
#[CoversClass(MeilisearchIndexer::class)]
final class RemoveEntityFromIndexMessageHandlerTest extends KernelTestCase
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

    public function testRemovesPostFromAllLocaleIndexes(): void
    {
        $story = MeilisearchIndexingStory::load();
        $post = $story->getPostWithBothLocales();

        $this->indexPost($post->getId());

        self::assertNotNull($this->getDocument('posts_fr', $post->getId()));
        self::assertNotNull($this->getDocument('posts_en', $post->getId()));

        $this->dispatchRemoveMessage(Post::class, $post->getId());

        self::assertNull($this->getDocument('posts_fr', $post->getId()));
        self::assertNull($this->getDocument('posts_en', $post->getId()));
    }

    public function testSkipsNonExistentEntityClass(): void
    {
        $this->dispatchRemoveMessage('App\\Entity\\NonExistentEntity', 1);

        // No exception thrown - handler gracefully skips
        self::assertTrue(true);
    }

    public function testSkipsNonSearchableEntity(): void
    {
        $this->dispatchRemoveMessage(PostTranslation::class, 1);

        // No exception thrown - handler gracefully skips
        self::assertTrue(true);
    }

    private function clearMeilisearchIndexes(): void
    {
        foreach (['posts_fr', 'posts_en'] as $index) {
            try {
                $task = $this->meilisearchClient->deleteIndex($this->indexPrefix . $index);
                $this->meilisearchClient->waitForTask($task['taskUid']);
            } catch (ApiException) {
            }
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

    private function indexPost(int $id): void
    {
        $this->messageBus->dispatch(new IndexEntityMessage(Post::class, $id));
        $this->waitForPendingTasks();
    }

    private function dispatchRemoveMessage(string $class, int $id): void
    {
        $this->messageBus->dispatch(new RemoveEntityFromIndexMessage($class, $id));
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
