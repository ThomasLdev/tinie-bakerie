<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\RemoveEntityFromIndexMessage;
use App\Services\Search\EntityIndexer;
use Meilisearch\Bundle\SearchService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RemoveEntityFromIndexMessageHandler
{
    public function __construct(
        private SearchService $searchService,
        private EntityIndexer $entityIndexer,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(RemoveEntityFromIndexMessage $message): void
    {
        $entityClass = $message->getEntityClass();
        $entityId = $message->getEntityId();

        if (!class_exists($entityClass)) {
            $this->logger->warning('Entity class does not exist, skipping removal', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
            ]);

            return;
        }

        if (!$this->searchService->isSearchable($entityClass)) {
            $this->logger->debug('Entity type not searchable, skipping removal', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
            ]);

            return;
        }

        $this->entityIndexer->remove('posts', $entityId);
    }
}
