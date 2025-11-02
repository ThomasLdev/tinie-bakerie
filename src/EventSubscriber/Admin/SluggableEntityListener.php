<?php

declare(strict_types=1);

namespace App\EventSubscriber\Admin;

use App\Entity\Contracts\HasSlugs;
use App\Entity\Contracts\HasTranslations;
use App\Services\Slug\Slugger;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class SluggableEntityListener implements EventSubscriberInterface
{
    public function __construct(private Slugger $slugger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => 'setTranslationSlugOnUpdate',
            BeforeEntityPersistedEvent::class => 'setTranslationSlugOnCreate',
        ];
    }

    /**
     * @param BeforeEntityUpdatedEvent<object> $event
     */
    public function setTranslationSlugOnUpdate(BeforeEntityUpdatedEvent $event): void
    {
        $this->setTranslationSlug($event->getEntityInstance());
    }

    /**
     * @param BeforeEntityPersistedEvent<object> $event
     */
    public function setTranslationSlugOnCreate(BeforeEntityPersistedEvent $event): void
    {
        $this->setTranslationSlug($event->getEntityInstance());
    }

    private function setTranslationSlug(object $entity): void
    {
        if (!$entity instanceof HasTranslations) {
            return;
        }

        foreach ($entity->getTranslations() as $translation) {
            if (!$translation instanceof HasSlugs) {
                continue;
            }

            $translation->setSlug($this->slugger->slugify($translation->getTitle()));
        }
    }
}
