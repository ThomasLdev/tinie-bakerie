<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Category;
use App\Entity\Contracts\SluggableEntityInterface;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Exception\ORMException;
use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[AsEntityListener(event: Events::postPersist, entity: Post::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Post::class)]
#[AsEntityListener(event: Events::postPersist, entity: Category::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Category::class)]
readonly class SluggableEntityListener {
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatableListener $translatableListener,
        #[Autowire(param: 'app.supported_locales')] private string $supportedLocales,
    )
    {
    }

    /**
     * @throws ORMException
     */
    public function postPersist(SluggableEntityInterface $entity): void
    {
        $this->updateSlugsForAllLocales($entity);
    }

    /**
     * @throws ORMException
     */
    public function preUpdate(SluggableEntityInterface $entity, PreUpdateEventArgs $event): void
    {
        if (!$event->hasChangedField('title')) {
            return;
        }

        $this->updateSlugsForAllLocales($entity);
    }

    /**
     * @throws ORMException
     */
    private function updateSlugsForAllLocales(SluggableEntityInterface $entity): void
    {
        $currentLocale = $this->translatableListener->getListenerLocale();
        $slugger = new AsciiSlugger();

        foreach (explode('|', $this->supportedLocales) as $locale) {
            $this->translatableListener->setTranslatableLocale($locale);

            if ($entity->getId()) {
                $this->entityManager->refresh($entity);
            }

            $entity->setSlug($slugger->slug($entity->getTitle())->lower()->toString());
        }

        $this->translatableListener->setTranslatableLocale($currentLocale);

        // Refresh to get back to the original locale state
        if ($entity->getId()) {
            $this->entityManager->refresh($entity);
        }

        $this->entityManager->flush();
    }
}
