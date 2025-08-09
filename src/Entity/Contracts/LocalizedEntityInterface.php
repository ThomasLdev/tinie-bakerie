<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

interface LocalizedEntityInterface
{
    public function getLocale(): ?string;
    public function setLocale(string $locale): self;
}
