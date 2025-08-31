<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Services\Post\Cache\PostCache;
use App\Services\Slug\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class AdminPostUpdateSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(param: 'app.non_default_locale')] private array $nonDefaultLocale,
        #[Autowire(param: 'default_locale')] private string $defaultLocale,
        private Slugger $slugger,
        private EntityManagerInterface $entityManager,
        private PostCache $postCache,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => ['updatePostSlug'],
            AfterEntityPersistedEvent::class => ['createPostSlug'],
        ];
    }

    /**
     * @throws InvalidArgumentException
     * @throws ORMException
     */
    public function updatePostSlug(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof Post) {
            return;
        }

        $slugs = [];
        $slugs[$this->defaultLocale] = $entity->getSlug();

        $entity->setSlug($this->slugger->slugify($entity->getTitle()));

        foreach ($this->nonDefaultLocale as $locale) {
            $entity->setTranslatableLocale($locale);

            $this->entityManager->refresh($entity);

            $localizedSlug = $this->slugger->slugify($entity->getTitle());
            $slugs[$locale] = $localizedSlug;

            $entity->setSlug($localizedSlug);
        }

        $this->postCache->removeItem($slugs);
    }

    /**
     * @throws ORMException
     */
    public function createPostSlug(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof Post) {
            return;
        }

        $slug = $this->slugger->slugify($entity->getTitle());
        $entity->setSlug($slug);

        $this->entityManager->flush();

        foreach ($this->nonDefaultLocale as $locale) {
            $entity->setTranslatableLocale($locale);

            $this->entityManager->refresh($entity);

            $localizedSlug = $this->slugger->slugify($entity->getTitle());
            $slugTranslation = new PostTranslation($locale, 'slug', $localizedSlug);
            $slugTranslation->setObject($entity);
            $this->entityManager->persist($slugTranslation);

            $entity->addTranslation($slugTranslation);
        }
    }
}
