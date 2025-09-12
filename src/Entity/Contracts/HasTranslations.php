<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

use Doctrine\Common\Collections\Collection;

/**
 * @template T of IsTranslation
 */
interface HasTranslations
{
    /**
     * @return Collection<int,T>
     */
    public function getTranslations(): Collection;
}
