<?php

declare(strict_types=1);

namespace App\Services\Cache;

use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Base class for entity caching with common patterns.
 * Implements Template Method pattern - child classes only implement entity-specific logic.
 * All boilerplate (error handling, logging, cache operations) is handled here.
 */
abstract readonly class AbstractEntityCache implements EntityCacheInterface
{
    protected const int CACHE_TTL = 86400; // 24 hours

    public function __construct(
        protected TagAwareCacheInterface $cache,
        protected CacheKeyGenerator $keyGenerator,
        protected LoggerInterface $logger,
    ) {
    }

    /**
     * Get one entity by locale and identifier.
     * Final implementation - child classes don't override this.
     * Template method that orchestrates the caching flow.
     */
    final public function getOne(string $locale, string $identifier): ?object
    {
        $result = $this->resolveIdentifierToId($locale, $identifier);

        if (null === $result) {
            return null;
        }

        [$id, $preloadedEntity] = $result;

        // If entity was already loaded during slug resolution, return it directly
        // This avoids an extra cache fetch after the proactive cache save
        return $preloadedEntity ?? $this->fetchAndCacheEntity($locale, $id);
    }

    /**
     * Template method for fetching and caching an entity.
     * Handles all boilerplate - child classes just provide specific logic via abstract methods.
     *
     * @param string $locale The locale for cache key generation
     * @param int $id The entity ID
     * @param object|null $preloadedEntity Optional pre-loaded entity (for proactive caching optimization)
     *
     * @return object|null The cached entity
     */
    final protected function fetchAndCacheEntity(string $locale, int $id, ?object $preloadedEntity = null): ?object
    {
        $key = $this->keyGenerator->entityShow($this->getEntityName(), $locale, $id);

        try {
            return $this->cache->get($key, function (ItemInterface $item) use ($id, $preloadedEntity): ?object {
                $item->expiresAfter(static::CACHE_TTL);

                // Load entity using child class implementation
                $entity = $preloadedEntity ?? $this->loadEntityById($id);

                // Apply tags using child class implementation
                // Only apply tags if entity exists (prevents orphaned cache entries)
                if ($entity) {
                    $tags = $this->generateCacheTags($entity);
                    $item->tag($tags);
                }

                return $entity;
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->warning($this->getEntityName() . ' cache operation failed, falling back to DB', [
                'exception' => $e->getMessage(),
                'key' => $key,
            ]);

            return $preloadedEntity ?? $this->loadEntityById($id);
        }
    }

    /**
     * Resolve identifier (slug or ID) to an entity ID and optionally the entity itself.
     * Can be overridden by child classes if needed (e.g., Tag doesn't need slug resolution).
     *
     * @param string $locale The locale for slug resolution
     * @param string $identifier The identifier (slug or numeric ID)
     *
     * @return array{int, object|null}|null Array of [id, entity] where entity may be null if not preloaded, or null if not found
     */
    protected function resolveIdentifierToId(string $locale, string $identifier): ?array
    {
        // If already numeric, return ID with no preloaded entity
        if (is_numeric($identifier)) {
            return [(int) $identifier, null];
        }

        $mappingKey = $this->keyGenerator->slugMapping($this->getEntityName(), $locale, $identifier);

        try {
            // Cache the mapping result which includes both ID and entity
            return $this->cache->get($mappingKey, function (ItemInterface $item) use ($locale, $identifier): ?array {
                $item->expiresAfter(static::CACHE_TTL);

                // Load entity by slug using child class implementation
                $entity = $this->loadEntityBySlug($identifier);

                if (!$entity) {
                    return null;
                }

                $id = $this->extractEntityId($entity);

                if (null === $id) {
                    return null;
                }

                // Proactively cache the full entity to avoid second DB query in getOne()
                $this->fetchAndCacheEntity($locale, $id, $entity);

                // Return array for internal use: [id, entity]
                // The entity will be used by getOne() to avoid redundant cache fetch
                return [$id, $entity];
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Slug mapping cache failed, using direct DB query', [
                'exception' => $e->getMessage(),
                'key' => $mappingKey,
            ]);

            $entity = $this->loadEntityBySlug($identifier);

            if (!$entity) {
                return null;
            }

            $id = $this->extractEntityId($entity);

            return $id ? [$id, $entity] : null;
        }
    }

    /**
     * Load entity from database by ID.
     * Called by template method when entity needs to be fetched.
     *
     * @param int $id The entity ID
     *
     * @return object|null The loaded entity, or null if not found
     */
    abstract protected function loadEntityById(int $id): ?object;

    /**
     * Load entity from database by slug.
     * Called by slug resolution when identifier is not numeric.
     *
     * @param string $slug The entity slug
     *
     * @return object|null The loaded entity, or null if not found
     */
    abstract protected function loadEntityBySlug(string $slug): ?object;

    /**
     * Generate cache tags for an entity.
     * Tags are used for efficient cache invalidation.
     *
     * @param object $entity The entity to generate tags for
     *
     * @return array<string> Cache tags
     */
    abstract protected function generateCacheTags(object $entity): array;

    /**
     * Extract entity ID.
     * Handles potential null IDs (e.g., unpersisted entities).
     *
     * @param object $entity The entity to extract ID from
     *
     * @return int|null The entity ID, or null if not available
     */
    abstract protected function extractEntityId(object $entity): ?int;
}
