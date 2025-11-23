<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Message to index an entity in Meilisearch.
 * Sent asynchronously when entities are created/updated in EasyAdmin.
 */
final readonly class IndexEntityMessage
{
    public function __construct(
        private string $entityClass,
        private int $entityId,
        private string $operation, // 'create', 'update'
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

    public function getOperation(): string
    {
        return $this->operation;
    }
}
