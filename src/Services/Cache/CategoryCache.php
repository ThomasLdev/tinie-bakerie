<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Event\CacheInvalidationEvent;
use App\Repository\CategoryRepository;
use App\Services\Locale\Locales;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

readonly class CategoryCache implements EntityCacheInterface
{
    private const int CACHE_TTL = 3600; // 1 hour

    private const string ENTITY_NAME = 'category';

    public function __construct(
        #[Autowire(service: 'cache.app.taggable')]
        private TagAwareCacheInterface $cache,
        private CategoryRepository $repository,
        private CacheKeyGenerator $keyGenerator,
        private Locales $locales,
        private HeaderCache $headerCache,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return array<array-key,mixed>
     */
    public function get(string $locale): array
    {
        $key = $this->keyGenerator->entityIndex($this->getEntityName(), $locale);

        try {
            return $this->cache->get($key, function (ItemInterface $item): array {
                $item->expiresAfter(self::CACHE_TTL);

                // Add cache tags
                $item->tag([
                    'categories',
                    'categories_index',
                ]);

                return $this->repository->findAll();
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Category cache read failed, using direct DB query', [
                'exception' => $e->getMessage(),
                'key' => $key,
            ]);

            return $this->repository->findAll();
        }
    }

    public function getOne(string $locale, string $identifier): ?Category
    {
        // Resolve slug to ID if needed
        $id = $this->resolveIdentifierToId($locale, $identifier);

        if (null === $id) {
            return null;
        }

        $key = $this->keyGenerator->entityShow($this->getEntityName(), $locale, $id);

        try {
            return $this->cache->get($key, function (ItemInterface $item) use ($id): ?Category {
                $item->expiresAfter(self::CACHE_TTL);

                // Query by ID to ensure we cache the correct entity with correct locale
                $category = $this->repository->findOneById($id);

                if ($category) {
                    // Add cache tags
                    $item->tag([
                        'categories',
                        'category_' . $category->getId(),
                    ]);
                }

                return $category;
            });
        } catch (InvalidArgumentException|\Psr\Cache\CacheException $e) {
            $this->logger->error('Category cache read failed, using direct DB query', [
                'exception' => $e->getMessage(),
                'key' => $key,
            ]);

            return $this->repository->findOneById($id);
        }
    }

    /**
     * Resolve a slug or ID to an entity ID.
     * Caches the slug-to-ID mapping to avoid repeated database lookups.
     * Also proactively caches the full entity to avoid a second query.
     */
    private function resolveIdentifierToId(string $locale, string $identifier): ?int
    {
        // If already numeric, return as is
        if (is_numeric($identifier)) {
            return (int) $identifier;
        }

        // Try to get ID from slug mapping cache
        $mappingKey = $this->keyGenerator->slugMapping($this->getEntityName(), $locale, $identifier);

        try {
            return $this->cache->get($mappingKey, function (ItemInterface $item) use ($locale, $identifier): ?int {
                $item->expiresAfter(self::CACHE_TTL);

                // Query database for entity
                $entity = $this->repository->findOne($identifier);

                if (!$entity) {
                    return null;
                }

                // Proactively cache the entity to avoid second query in getOne()
                $this->cacheEntity($entity, $locale);

                return $entity->getId();
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Slug mapping cache failed, using direct DB query', [
                'exception' => $e->getMessage(),
                'key' => $mappingKey,
            ]);

            return $this->repository->findOne($identifier)?->getId();
        }
    }

    /**
     * Proactively cache an entity to optimize cold cache performance.
     * This is called when we've already fetched the entity from DB during slug resolution.
     */
    private function cacheEntity(Category $entity, string $locale): void
    {
        $key = $this->keyGenerator->entityShow($this->getEntityName(), $locale, $entity->getId());

        try {
            // Use cache->get() to be idempotent - if already cached, does nothing
            $this->cache->get($key, function (ItemInterface $item) use ($entity): Category {
                $item->expiresAfter(self::CACHE_TTL);

                // Add cache tags
                $item->tag([
                    'categories',
                    'category_' . $entity->getId(),
                ]);

                return $entity;
            });
        } catch (InvalidArgumentException $e) {
            // Don't throw - this is an optimization, not critical path
            $this->logger->warning('Failed to proactively cache entity during slug resolution', [
                'exception' => $e->getMessage(),
                'entity' => 'Category',
                'id' => $entity->getId(),
            ]);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function invalidate(object $entity): void
    {
        if (!$entity instanceof Category) {
            return;
        }

        try {
            $this->logger->info('Invalidating category cache', [
                'entity' => 'Category',
                'id' => $entity->getId(),
            ]);

            // Invalidate using cache tags - much more efficient!
            $this->cache->invalidateTags([
                'category_' . $entity->getId(),
                'categories_index',
            ]);

            // Invalidate header cache since category list changed
            $locales = $this->locales->get();
            $this->headerCache->invalidate($locales);

            // Invalidate slug mapping caches for all locales
            foreach ($locales as $locale) {
                $translation = $entity->getTranslationByLocale($locale);

                if ($translation instanceof CategoryTranslation) {
                    $this->cache->delete(
                        $this->keyGenerator->slugMapping($this->getEntityName(), $locale, $translation->getSlug()),
                    );
                }
            }

            // Dispatch event to notify other caches (like PostCache) that need invalidation
            $this->eventDispatcher->dispatch(
                new CacheInvalidationEvent($entity, 'update'),
                CacheInvalidationEvent::CATEGORY_INVALIDATED,
            );
        } catch (InvalidArgumentException|\Psr\Cache\CacheException $e) {
            $this->logger->error('Category cache invalidation failed', [
                'exception' => $e->getMessage(),
                'entity' => 'Category',
                'id' => $entity->getId(),
            ]);
            // Don't throw - allow operation to continue
        }
    }

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public static function supports(object $entity): bool
    {
        return $entity instanceof Category;
    }
}
