<?php

declare(strict_types=1);

namespace App\EventSubscriber\Admin;

use App\Services\Cache\EntityCacheInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class ModifiedEntityListener implements EventSubscriberInterface
{
    public function __construct(
        /** @var EntityCacheInterface[] $entityCaches */
        #[AutowireIterator('service.entity_cache')]
        private iterable $entityCaches,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityUpdatedEvent::class => 'invalidateCacheOnUpdate',
            AfterEntityPersistedEvent::class => 'invalidateCacheOnCreate',
            AfterEntityDeletedEvent::class => 'invalidateCacheOnDelete',
        ];
    }

    /**
     * @param AfterEntityUpdatedEvent<object> $event
     */
    public function invalidateCacheOnUpdate(AfterEntityUpdatedEvent $event): void
    {
        $this->invalidateCache($event->getEntityInstance());
    }

    /**
     * @param AfterEntityPersistedEvent<object> $event
     */
    public function invalidateCacheOnCreate(AfterEntityPersistedEvent $event): void
    {
        $this->invalidateCache($event->getEntityInstance());
    }

    /**
     * @param AfterEntityDeletedEvent<object> $event
     */
    public function invalidateCacheOnDelete(AfterEntityDeletedEvent $event): void
    {
        $this->invalidateCache($event->getEntityInstance());
    }

    private function invalidateCache(object $entity): void
    {
        $this->getCache($entity)->invalidate($entity);
    }

    private function getCache(object $entity): EntityCacheInterface
    {
        foreach ($this->entityCaches as $cache) {
            if ($cache::supports($entity)) {
                return $cache;
            }
        }

        throw new \RuntimeException('No cache service found for entity ' . $entity::class);
    }
}
