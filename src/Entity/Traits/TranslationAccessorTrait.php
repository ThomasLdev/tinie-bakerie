<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use App\Entity\Contracts\Translation;

/**
 * Provides efficient O(1) access to translations by locale.
 *
 * This trait automatically indexes translations by locale when the entity is loaded
 * and provides a getCurrentTranslation() method that returns the translation
 * matching the current locale.
 *
 * @template T of Translation
 */
trait TranslationAccessorTrait
{
    /**
     * Indexed map of translations by locale for O(1) access.
     *
     * @var array<string, T>
     */
    private array $translationsByLocale = [];

    /** The current locale to use for translation access. */
    private ?string $currentLocale = null;

    /**
     * Sets the current locale for this entity.
     * This is typically called by the TranslatableEntitySubscriber after entity load.
     */
    public function setCurrentLocale(string $locale): void
    {
        $this->currentLocale = $locale;
    }

    /**
     * Builds an indexed map of translations by locale for efficient O(1) access.
     * This is typically called by the TranslatableEntitySubscriber after entity load.
     */
    public function indexTranslations(): void
    {
        $this->translationsByLocale = [];

        foreach ($this->getTranslations() as $translation) {
            $this->translationsByLocale[$translation->getLocale()] = $translation;
        }
    }

    /**
     * Returns the translation for a specific locale, or null if not found.
     * This method uses the indexed map for O(1) access.
     *
     * @return T|null
     */
    public function getTranslationByLocale(string $locale): ?Translation
    {
        return $this->translationsByLocale[$locale] ?? null;
    }

    /**
     * Returns the translation matching the current locale, or null if not found.
     * This is protected so that each entity can override it with a covariant return type.
     *
     * @return T|null
     */
    protected function getTranslationForCurrentLocale(): ?Translation
    {
        if (null === $this->currentLocale) {
            return null;
        }

        return $this->translationsByLocale[$this->currentLocale] ?? null;
    }
}
