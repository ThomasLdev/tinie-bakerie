<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Message to remove an entity from Meilisearch index.
 * Sent asynchronously when entities are deleted in EasyAdmin.
 */
final readonly class RemoveEntityFromIndexMessage
{
    public function __construct(
        private string $entityClass,
        private int $entityId,
    ) {
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }
}
