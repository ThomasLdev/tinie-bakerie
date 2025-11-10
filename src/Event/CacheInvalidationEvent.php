<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched when cache needs to be invalidated for an entity.
 */
class CacheInvalidationEvent extends Event
{
    public const string CATEGORY_INVALIDATED = 'cache.invalidation.category';
    public const string TAG_INVALIDATED = 'cache.invalidation.tag';

    public function __construct(
        private readonly object $entity,
        private readonly string $operation, // 'create', 'update', 'delete'
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }
}
