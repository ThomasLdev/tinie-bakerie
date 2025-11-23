<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\IndexEntityMessage;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\SearchService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class IndexEntityMessageHandler
{
    public function __construct(
        private SearchService $searchService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(IndexEntityMessage $message): void
    {
        $entityClass = $message->getEntityClass();
        $entityId = $message->getEntityId();
        $operation = $message->getOperation();

        // Check if entity class is configured in meilisearch.yaml
        if (!$this->searchService->isSearchable($entityClass)) {
            $this->logger->debug('Entity type not searchable, skipping indexing', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
            ]);

            return;
        }

        $indexName = $this->searchService->searchableAs($entityClass);

        $this->logger->info('Processing index message', [
            'entity_class' => $entityClass,
            'entity_id' => $entityId,
            'operation' => $operation,
            'index' => $indexName,
        ]);

        try {
            // Load entity from database
            $entity = $this->entityManager->find($entityClass, $entityId);

            if ($entity === null) {
                $this->logger->warning('Entity not found for indexing', [
                    'entity_class' => $entityClass,
                    'entity_id' => $entityId,
                ]);

                return;
            }

            // Index the entity - SearchService handles everything automatically
            $this->searchService->index($this->entityManager, $entity);

            $this->logger->info('Entity indexed successfully', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
                'index' => $indexName,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to index entity', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
                'index' => $indexName,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }
}
