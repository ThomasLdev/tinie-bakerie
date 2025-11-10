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

        // Use unified caching method with DB loader for critical path
        return $this->cacheCategory(
            locale: $locale,
            id: $id,
            loader: fn () => $this->repository->findOneById($id),
            throwOnError: true,
        );
    }

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
     * Configures a cache item with TTL and tags.
     * Only applies tags if we have a valid category to prevent orphaned cache entries.
     *
     * @param ItemInterface $item The cache item to configure
     * @param Category|null $category The category to cache (null for negative caching)
     */
    private function configureCacheItem(ItemInterface $item, ?Category $category): void
    {
        $item->expiresAfter(self::CACHE_TTL);

        // Only apply tags if we have a valid category
        // This prevents orphaned cache entries and ensures consistent invalidation
        if ($category) {
            $item->tag([
                'categories',
                'category_' . $category->getId(),
            ]);
        }
    }

    /**
     * Unified method to cache a category with consistent configuration.
     * Handles both "fetch from DB" and "use provided entity" scenarios.
     *
     * @param string $locale The locale for cache key generation
     * @param int $id The category ID
     * @param callable|null $loader Optional loader function that fetches the category from DB
     * @param Category|null $entity Pre-loaded entity (for proactive caching optimization)
     * @param bool $throwOnError Whether to throw/fallback on cache errors (true for critical path)
     *
     * @return Category|null The cached category
     */
    private function cacheCategory(
        string $locale,
        int $id,
        ?callable $loader = null,
        ?Category $entity = null,
        bool $throwOnError = true,
    ): ?Category {
        $key = $this->keyGenerator->entityShow($this->getEntityName(), $locale, $id);

        try {
            return $this->cache->get($key, function (ItemInterface $item) use ($loader, $entity): ?Category {
                // Use loader if provided, otherwise return pre-loaded entity
                $category = $loader ? $loader() : $entity;

                // Configure cache item with unified logic
                $this->configureCacheItem($item, $category);

                return $category;
            });
        } catch (InvalidArgumentException $e) {
            $context = [
                'exception' => $e->getMessage(),
                'key' => $key,
                'entity' => 'Category',
                'id' => $id,
            ];

            if ($throwOnError) {
                // Critical path - log as error and provide fallback
                $this->logger->error('Category cache read failed, using direct DB query', $context);

                return $loader ? $loader() : $entity;
            }
            // Optimization path - log as warning, no fallback needed
            $this->logger->warning('Failed to proactively cache category', $context);

            return null;
        }
    }

    /**
     * Proactively cache an entity to optimize cold cache performance.
     * This is called when we've already fetched the entity from DB during slug resolution.
     */
    private function cacheEntity(Category $entity, string $locale): void
    {
        $id = $entity->getId();

        // Entity must have an ID (should always be true for DB-fetched entities)
        if (null === $id) {
            return;
        }

        // Use unified caching method with pre-loaded entity for optimization
        $this->cacheCategory(
            locale: $locale,
            id: $id,
            entity: $entity,
            throwOnError: false,
        );
    }
}
