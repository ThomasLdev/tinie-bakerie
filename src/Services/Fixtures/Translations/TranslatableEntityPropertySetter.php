<?php

declare(strict_types=1);

namespace App\Services\Fixtures\Translations;

use App\Entity\Contracts\LocalizedEntityInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class TranslatableEntityPropertySetter
{
    public function __construct(
        #[Autowire(param: 'app.supported_locales')] private string $supportedLocales,
        #[Autowire(param: 'default_locale')] private string $defaultLocale,
    )
    {
    }

    /**
     * @param array<string, callable> $translatableFields
     */
    public function processTranslations(
        LocalizedEntityInterface $entity,
        string $translationClass,
        array $translatableFields
    ): void
    {
        foreach (explode('|', $this->supportedLocales) as $locale) {
            if ($locale === $this->defaultLocale) {
                continue;
            }

            foreach ($translatableFields as $field => $callback) {
                $entity->addTranslation(new $translationClass($locale, $field, $callback($locale, $entity)));
            }
        }
    }
}
