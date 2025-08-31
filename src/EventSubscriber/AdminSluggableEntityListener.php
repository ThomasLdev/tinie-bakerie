<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Contracts\HasSluggableTranslation;
use App\Entity\Contracts\SluggableEntityInterface;
use App\Services\Slug\Slugger;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class AdminSluggableEntityListener implements EventSubscriberInterface
{
    public function __construct(
        private Slugger $slugger,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => 'setTranslationSlug',
        ];
    }

    public function setTranslationSlug(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof HasSluggableTranslation) {
            return;
        }

        foreach ($entity->getTranslations() as $translation) {
            if (!$translation instanceof SluggableEntityInterface) {
                continue;
            }

            $translation->setSlug($this->slugger->slugify($translation->getTitle()));
        }
    }
}
