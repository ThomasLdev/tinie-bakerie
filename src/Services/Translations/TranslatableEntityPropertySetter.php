<?php

declare(strict_types=1);

namespace App\Services\Translations;

use App\Entity\Contracts\LocalizedEntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class TranslatableEntityPropertySetter
{
    public function __construct(
        #[Autowire(param: 'app.supported_locales')] private string $supportedLocales,
        #[Autowire(param: 'default_locale')] private string $defaultLocale,
        private EntityManagerInterface $entityManager, )
    {
    }

    /**
     * @param array<string, callable> $translatableFields
     */
    public function processTranslations(LocalizedEntityInterface $entity, array $translatableFields): void
    {
        // The default locale data has been set by the factory, just persist it
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        foreach (explode('|', $this->supportedLocales) as $locale) {
            if ($locale === $this->defaultLocale) {
                continue;
            }

            $entity->setLocale($locale);

            foreach ($translatableFields as $field => $callback) {
                $setter = 'set'.ucfirst($field);
                $value = $callback($locale, $entity);
                $entity->$setter($value);
            }

            $this->entityManager->flush();
        }
    }
}
