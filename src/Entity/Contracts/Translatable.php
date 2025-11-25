<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

use Doctrine\Common\Collections\Collection;

/**
 * Interface for entities that have translations.
 *
 * Entities implementing this interface should use the TranslationAccessorTrait
 * to provide efficient indexed access to translations.
 *
 * @template T of Translation
 * @extends TranslationAccessor<T>
 */
interface Translatable extends TranslationAccessor
{
    /**
     * @return Collection<int,T>
     */
    public function getTranslations(): Collection;
}
