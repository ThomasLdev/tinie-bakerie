<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

/**
 * Represents an entity that can have a slug generated from its title.
 */
interface Sluggable
{
    public function getTitle(): string;

    public function setSlug(string $slug): self;
}
