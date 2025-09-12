<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

interface HasSlugs
{
    public function getTitle(): string;

    public function setSlug(string $slug): self;
}
