<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\RemoveEntityFromIndexMessage;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\SearchService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RemoveEntityFromIndexMessageHandler
{
    public function __construct(
        private SearchService $searchService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(RemoveEntityFromIndexMessage $message): void
    {
        $entityClass = $message->getEntityClass();
        $entityId = $message->getEntityId();

        // Check if entity class is configured in meilisearch.yaml
        if (!$this->searchService->isSearchable($entityClass)) {
            $this->logger->debug('Entity type not searchable, skipping removal', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
            ]);

            return;
        }

        $indexName = $this->searchService->searchableAs($entityClass);

        $this->logger->info('Processing remove from index message', [
            'entity_class' => $entityClass,
            'entity_id' => $entityId,
            'index' => $indexName,
        ]);

        try {
            // For deletion, we need to create a stub entity with the ID
            // since the entity is already deleted from the database
            $entity = $this->createStubEntity($entityClass, $entityId);

            // Remove from index
            $this->searchService->remove($this->entityManager, $entity);

            $this->logger->info('Entity removed from index successfully', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
                'index' => $indexName,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to remove entity from index', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
                'index' => $indexName,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create a stub entity with just the ID for deletion from index.
     */
    private function createStubEntity(string $entityClass, int $entityId): object
    {
        // Use reflection to create entity with ID set
        $reflectionClass = new \ReflectionClass($entityClass);
        $entity = $reflectionClass->newInstanceWithoutConstructor();

        // Set the ID property
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setValue($entity, $entityId);

        return $entity;
    }
}
