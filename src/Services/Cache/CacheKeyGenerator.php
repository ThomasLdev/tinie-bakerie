<?php

declare(strict_types=1);

namespace App\Services\Cache;

final readonly class CacheKeyGenerator
{
    /**
     * Cache version to invalidate old cache entries after breaking changes.
     * Increment this when the cache data structure changes.
     */
    private const string CACHE_VERSION = 'v2';

    public function entityIndex(string $entityName, string $locale): string
    {
        return \sprintf('%s_index_%s_%s', strtolower($entityName), $locale, self::CACHE_VERSION);
    }

    public function entityShow(string $entityName, string $locale, int $id): string
    {
        return \sprintf('%s_show_%s_%d_%s', strtolower($entityName), $locale, $id, self::CACHE_VERSION);
    }

    /**
     * Generate cache key for slug-to-ID mapping.
     * This allows quick resolution of slugs to entity IDs without database queries.
     * v2: Changed return type from int to array{int, object|null}.
     */
    public function slugMapping(string $entityName, string $locale, string $slug): string
    {
        return \sprintf('%s_slug_map_%s_%s_%s', strtolower($entityName), $locale, $slug, self::CACHE_VERSION);
    }
}
