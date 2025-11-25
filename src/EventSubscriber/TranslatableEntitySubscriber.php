<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Contracts\Translatable;
use App\Services\Locale\LocaleProvider;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;

/**
 * Automatically indexes translations and injects the current locale into translatable entities.
 *
 * This subscriber listens to the postLoad event and:
 * 1. Checks if the loaded entity implements Translatable
 * 2. Calls indexTranslations() to build an efficient locale-indexed map
 * 3. Injects the current locale from the LocaleProvider
 *
 * This allows entities to use getCurrentTranslation() for O(1) access to the right translation,
 * regardless of whether the Doctrine locale filter is enabled or not.
 */
#[AsDoctrineListener(event: Events::postLoad)]
readonly class TranslatableEntitySubscriber
{
    public function __construct(
        private LocaleProvider $localeProvider,
    ) {
    }

    public function postLoad(PostLoadEventArgs $args): void
    {
        $entity = $args->getObject();

        // Only process entities that implement Translatable
        if (!$entity instanceof Translatable) {
            return;
        }

        // Build the indexed map of translations by locale for O(1) access
        $entity->indexTranslations();

        // Inject the current locale from the provider
        $entity->setCurrentLocale($this->localeProvider->getCurrentLocale());
    }
}
