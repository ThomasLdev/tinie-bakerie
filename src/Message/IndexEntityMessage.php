<?php

declare(strict_types=1);

namespace App\Message;

final readonly class IndexEntityMessage
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
