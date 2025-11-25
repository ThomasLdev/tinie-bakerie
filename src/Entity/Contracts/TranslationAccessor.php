<?php

declare(strict_types=1);

namespace App\Entity\Contracts;

/**
 * Interface for entities that provide indexed translation access.
 *
 * This interface is implemented via the TranslationAccessorTrait and provides
 * the contract for the TranslatableEntitySubscriber to set up translations.
 *
 * @template T of Translation
 */
interface TranslationAccessor
{
    /**
     * Builds an indexed map of translations by locale for efficient O(1) access.
     */
    public function indexTranslations(): void;

    /**
     * Sets the current locale for this entity.
     */
    public function setCurrentLocale(string $locale): void;

    /**
     * Returns the translation for a specific locale, or null if not found.
     *
     * @return T|null
     */
    public function getTranslationByLocale(string $locale): ?Translation;
}
