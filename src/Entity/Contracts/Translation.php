<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

/**
 * @template T of Translatable
 */
interface Translation
{
    /**
     * @return T
     */
    public function getTranslatable(): ?Translatable;

    public function getLocale(): string;
}
