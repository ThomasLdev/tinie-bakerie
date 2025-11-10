<?php

declare(strict_types=1);

namespace App\Services\Cache;

final readonly class CacheKeyGenerator
{
    public function entityIndex(string $entityName, string $locale): string
    {
        return \sprintf('%s_index_%s', strtolower($entityName), $locale);
    }

    public function entityShow(string $entityName, string $locale, int $id): string
    {
        return \sprintf('%s_show_%s_%d', strtolower($entityName), $locale, $id);
    }

    /**
     * Generate cache key for slug-to-ID mapping.
     * This allows quick resolution of slugs to entity IDs without database queries.
     */
    public function slugMapping(string $entityName, string $locale, string $slug): string
    {
        return \sprintf('%s_slug_map_%s_%s', strtolower($entityName), $locale, $slug);
    }
}
