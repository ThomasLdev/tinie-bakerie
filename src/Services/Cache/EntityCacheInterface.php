<?php

declare(strict_types=1);

namespace App\Services\Cache;

/**
 * Full caching interface for entities with public pages.
 * Extends InvalidatableEntityCacheInterface and adds get/getOne methods
 * for entities that need to be cached and retrieved (e.g., Category, Post).
 *
 * Note: This interface no longer has the AutoconfigureTag attribute
 * because InvalidatableEntityCacheInterface already has it.
 */
interface EntityCacheInterface extends InvalidatableEntityCacheInterface
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
