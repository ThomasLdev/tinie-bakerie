<?php

declare(strict_types=1);

namespace App\Services\Cache;

/**
 * Full caching interface for entities with public pages.
 * Extends InvalidatableEntityCacheInterface and WarmableCacheInterface,
 * adding get/getOne methods for entities that need to be cached and retrieved (e.g., Category, Post).
 */
interface EntityCacheInterface extends InvalidatableEntityCacheInterface, WarmableCacheInterface
{
    /**
     * Get all entities for a given locale.
     *
     * @return array<array-key,mixed>
     */
    public function get(string $locale): array;

    /**
     * Get one entity by locale and identifier (slug or ID).
     */
    public function getOne(string $locale, string $identifier): ?object;
}
