<?php

declare(strict_types=1);

namespace App\EventSubscriber\Admin;

use App\Services\Cache\EntityCacheInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
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
            BeforeEntityUpdatedEvent::class => 'invalidateCacheOnUpdate',
            BeforeEntityPersistedEvent::class => 'invalidateCacheOnCreate',
        ];
    }

    public function invalidateCacheOnUpdate(BeforeEntityUpdatedEvent $event): void
    {
        $this->invalidateCache($event->getEntityInstance());
    }

    public function invalidateCacheOnCreate(BeforeEntityPersistedEvent $event): void
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
