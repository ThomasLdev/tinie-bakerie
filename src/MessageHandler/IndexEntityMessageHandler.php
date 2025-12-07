<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Contracts\Translatable;
use App\Message\IndexEntityMessage;
use App\Services\Locale\Locales;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Meilisearch\Bundle\SearchService;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsMessageHandler]
final readonly class IndexEntityMessageHandler
{
    private const PRIMARY_KEY = 'id';

    public function __construct(
        private SearchService $searchService,
        private EntityManagerInterface $entityManager,
        private Client $client,
        private NormalizerInterface $normalizer,
        private LoggerInterface $logger,
        private Locales $locales,
        #[Autowire(param: 'meilisearch.prefix')]
        private string $prefix,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function __invoke(IndexEntityMessage $message): void
    {
        /** @var class-string $entityClass */
        $entityClass = $message->getEntityClass();
        $entityId = $message->getEntityId();
        $operation = $message->getOperation();

        if (!$this->searchService->isSearchable($entityClass)) {
            $this->logger->debug('Entity type not searchable, skipping indexing', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
            ]);

            return;
        }

        $this->logger->info('Processing index message', [
            'entity_class' => $entityClass,
            'entity_id' => $entityId,
            'operation' => $operation,
        ]);

        try {
            $entity = $this->entityManager->find($entityClass, $entityId);

            if ($entity === null) {
                $this->logger->warning('Entity not found for indexing', [
                    'entity_class' => $entityClass,
                    'entity_id' => $entityId,
                ]);

                return;
            }

            $this->indexEntityForAllLocales($entity, $entityClass, $entityId);
        } catch (\Exception $e) {
            $this->logger->error('Failed to index entity', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    private function indexEntityForAllLocales(object $entity, string $entityClass, int|string $entityId): void
    {
        $locales = $this->locales->get();

        foreach ($locales as $locale) {
            $this->indexEntityForLocale($entity, $entityClass, $entityId, $locale);
        }
    }

    private function indexEntityForLocale(object $entity, string $entityClass, int|string $entityId, string $locale): void
    {
        $indexName = \sprintf('%sposts_%s', $this->prefix, $locale);

        try {
            // Check if entity has translation for this locale (if it's translatable)
            if ($entity instanceof Translatable && !$this->hasTranslationForLocale($entity, $locale)) {
                $this->logger->debug('Entity has no translation for locale, skipping', [
                    'entity_class' => $entityClass,
                    'entity_id' => $entityId,
                    'locale' => $locale,
                ]);

                return;
            }

            // Normalize with locale context
            $normalized = $this->normalizer->normalize($entity, null, [
                'meilisearch_locale' => $locale,
            ]);

            if (empty($normalized) || !\is_array($normalized)) {
                $this->logger->debug('Normalization returned empty result, skipping', [
                    'entity_class' => $entityClass,
                    'entity_id' => $entityId,
                    'locale' => $locale,
                ]);

                return;
            }

            // Index the document with retry on primary key mismatch
            $this->addDocumentWithRetry($indexName, $normalized, $entityClass, $entityId, $locale);

            $this->logger->info('Entity indexed successfully', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
                'index' => $indexName,
                'locale' => $locale,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to index entity for locale', [
                'entity_class' => $entityClass,
                'entity_id' => $entityId,
                'index' => $indexName,
                'locale' => $locale,
                'exception' => $e->getMessage(),
            ]);
            // Continue to next locale instead of throwing
        }
    }

    /**
     * Add a document to the index, with retry on primary key mismatch.
     *
     * @param array<string, mixed> $document
     */
    private function addDocumentWithRetry(string $indexName, array $document, string $entityClass, int|string $entityId, string $locale): void
    {
        try {
            $this->client->index($indexName)->addDocuments([$document], self::PRIMARY_KEY);
        } catch (ApiException $e) {
            // Handle primary key mismatch - delete index and retry
            if ($e->httpStatus === 400 && str_contains($e->getMessage(), 'primary key')) {
                $this->logger->warning('Index has incompatible primary key, recreating index', [
                    'index' => $indexName,
                    'entity_class' => $entityClass,
                    'entity_id' => $entityId,
                    'locale' => $locale,
                ]);

                $this->deleteIndex($indexName);
                $this->client->index($indexName)->addDocuments([$document], self::PRIMARY_KEY);

                $this->logger->info('Index recreated and document added successfully', [
                    'index' => $indexName,
                ]);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Delete an index entirely to ensure it's recreated with the correct primary key.
     */
    private function deleteIndex(string $indexName): void
    {
        try {
            $this->client->deleteIndex($indexName);
        } catch (ApiException $e) {
            // Index doesn't exist, which is fine
            if ($e->httpStatus !== 404) {
                throw $e;
            }
        }
    }

    /**
     * @phpstan-ignore-next-line Generic type complexity
     */
    private function hasTranslationForLocale(Translatable $entity, string $locale): bool
    {
        foreach ($entity->getTranslations() as $translation) {
            if ($translation->getLocale() === $locale) {
                return true;
            }
        }

        return false;
    }
}
