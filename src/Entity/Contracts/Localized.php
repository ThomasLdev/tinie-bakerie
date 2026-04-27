<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

/**
 * Marks an entity that carries a locale (typically a translation row).
 */
interface Localized
{
    public function getLocale(): string;

    public function setLocale(string $locale): static;
}
