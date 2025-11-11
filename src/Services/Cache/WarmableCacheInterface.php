<?php

declare(strict_types=1);

namespace App\Services\Cache;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Interface for cache services that support warming up (pre-populating cache).
 * Cache services implementing this interface can be automatically discovered
 * and warmed by the WarmCacheCommand using #[AutowireIterator('service.warmable_cache')].
 */
#[AutoconfigureTag('service.warmable_cache')]
interface WarmableCacheInterface
{
    /**
     * Warm up the cache for the given locale.
     * This should pre-populate the cache with frequently accessed data.
     *
     * @return int Number of items cached
     */
    public function warmUp(string $locale): int;

    /**
     * Get the entity/cache name for display purposes (e.g., 'category', 'post', 'header').
     * For entity caches, this should match getEntityName() from InvalidatableEntityCacheInterface.
     */
    public function getEntityName(): string;
}
