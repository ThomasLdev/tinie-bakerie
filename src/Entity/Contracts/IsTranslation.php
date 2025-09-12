<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

/**
 * @template T of HasTranslations
 */
interface IsTranslation
{
    /**
     * @return T
     */
    public function getTranslatable(): HasTranslations;

    public function getLocale(): string;
}
