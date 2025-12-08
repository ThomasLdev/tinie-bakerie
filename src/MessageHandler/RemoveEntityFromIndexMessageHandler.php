<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\RemoveEntityFromIndexMessage;
use App\Services\Locale\Locales;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Meilisearch\Bundle\SearchService;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RemoveEntityFromIndexMessageHandler
{
    public function __construct(
        private SearchService $searchService,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private Locales $locales,
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

        $this->logger->info('Processing remove from index message', [
            'entity_class' => $entityClass,
            'entity_id' => $entityId,
        ]);

        try {
            // For deletion, we need to create a stub entity with the ID
            // since the entity is already deleted from the database
            $entity = $this->createStubEntity($entityClass, $entityId);
            $locales = $this->locales->get();

            foreach ($locales as $locale) {
                $indexName = "posts_{$locale}";

                try {
                    $this->searchService->remove($this->entityManager, $entity);

                    $this->logger->info('Entity removed from index successfully', [
                        'entity_class' => $entityClass,
                        'entity_id' => $entityId,
                        'index' => $indexName,
                        'locale' => $locale,
                    ]);
                } catch (Exception $e) {
                    $this->logger->error('Failed to remove entity from index', [
                        'entity_class' => $entityClass,
                        'entity_id' => $entityId,
                        'index' => $indexName,
                        'locale' => $locale,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }
        } catch (Exception $e) {
            $this->logger->error('Failed to remove entity', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create a stub entity with just the ID for deletion from index.
     *
     * @param class-string<object> $entityClass
     *
     * @throws \ReflectionException
     */
    private function createStubEntity(string $entityClass, int $entityId): object
    {
        $reflectionClass = new ReflectionClass($entityClass);
        $entity = $reflectionClass->newInstanceWithoutConstructor();

        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setValue($entity, $entityId);

        return $entity;
    }
}
