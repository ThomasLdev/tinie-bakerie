<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

/**
 * Marks an entity that has a numeric position used to order siblings inside
 * a parent collection.
 */
interface Positionable
{
    public function getPosition(): int;
}
