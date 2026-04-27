<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

/**
 * @template T of Translatable
 */
interface Translation extends Localized
{
    /**
     * @return T
     */
    public function getTranslatable(): ?Translatable;
}
