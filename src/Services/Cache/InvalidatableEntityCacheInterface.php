<?php

declare(strict_types=1);

namespace App\Services\Cache;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Minimal interface for entity caches that only handle invalidation.
 * Used for entities that don't have public pages (e.g., Tag) but need
 * to notify other caches when they change.
 */
#[AutoconfigureTag('service.entity_cache')]
interface InvalidatableEntityCacheInterface
{
    /**
     * Invalidate cached data for the given entity.
     * Should dispatch events to notify dependent caches.
     */
    public function invalidate(object $entity): void;

    /**
     * Get the entity name (e.g., 'category', 'post', 'tag').
     */
    public function getEntityName(): string;

    /**
     * Check if this cache service supports the given entity.
     */
    public static function supports(object $entity): bool;
}
