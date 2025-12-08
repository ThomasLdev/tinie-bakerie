<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Services\Locale\Locales;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class EntityIndexer
{
    public function __construct(
        private MeilisearchIndexer $indexer,
        private IndexNameResolver $indexNameResolver,
        private NormalizerInterface $normalizer,
        private Locales $locales,
        private LoggerInterface $logger,
    ) {
    }

    public function index(object $entity, string $entityShortName): void
    {
        foreach ($this->locales->get() as $locale) {
            $this->indexForLocale($entity, $entityShortName, $locale);
        }
    }

    public function remove(string $entityShortName, int|string $entityId): void
    {
        foreach ($this->locales->get() as $locale) {
            $indexName = $this->indexNameResolver->resolve($entityShortName, $locale);
            $this->indexer->removeDocument($indexName, $entityId);

            $this->logger->info('Entity removed from index', [
                'entity_id' => $entityId,
                'index' => $indexName,
                'locale' => $locale,
            ]);
        }
    }

    private function indexForLocale(object $entity, string $entityShortName, string $locale): void
    {
        $indexName = $this->indexNameResolver->resolve($entityShortName, $locale);

        $normalized = $this->normalizer->normalize($entity, null, [
            'meilisearch_locale' => $locale,
        ]);

        if (!\is_array($normalized) || $normalized === []) {
            $this->logger->debug('Normalization returned empty result, skipping', [
                'entity' => $entity::class,
                'locale' => $locale,
            ]);

            return;
        }

        /** @phpstan-var array<string, mixed> $normalized */
        $this->indexer->addDocument($indexName, $normalized);

        $this->logger->info('Entity indexed successfully', [
            'entity' => $entity::class,
            'index' => $indexName,
            'locale' => $locale,
        ]);
    }
}
