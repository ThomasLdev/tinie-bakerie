<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\IndexEntityMessage;
use App\Services\Search\EntityIndexer;
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
        private EntityIndexer $entityIndexer,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(IndexEntityMessage $message): void
    {
        /** @var class-string $entityClass */
        $entityClass = $message->getEntityClass();
        $entityId = $message->getEntityId();

        if (!$this->searchService->isSearchable($entityClass)) {
            $this->logger->debug('Entity type not searchable, skipping indexing', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
            ]);

            return;
        }

        $entity = $this->entityManager->find($entityClass, $entityId);

        if ($entity === null) {
            $this->logger->warning('Entity not found for indexing', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
            ]);

            return;
        }

        $this->entityIndexer->index($entity, 'posts');
    }
}
