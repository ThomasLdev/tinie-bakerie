<?php

declare(strict_types=1);

namespace App\EventSubscriber\Admin;

use App\Message\IndexEntityMessage;
use App\Message\RemoveEntityFromIndexMessage;
use App\Services\Cache\InvalidatableEntityCacheInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class ModifiedEntityListener implements EventSubscriberInterface
{
    public function __construct(
        /** @var InvalidatableEntityCacheInterface[] $entityCaches */
        #[AutowireIterator('service.entity_cache')]
        private iterable $entityCaches,
        private MessageBusInterface $messageBus,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityUpdatedEvent::class => 'onEntityUpdated',
            AfterEntityPersistedEvent::class => 'onEntityCreated',
            AfterEntityDeletedEvent::class => 'onEntityDeleted',
        ];
    }

    /**
     * @param AfterEntityUpdatedEvent<object> $event
     */
    public function onEntityUpdated(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        $this->invalidateCache($entity);
        $this->dispatchIndexMessage($entity);
    }

    /**
     * @param AfterEntityPersistedEvent<object> $event
     */
    public function onEntityCreated(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        $this->invalidateCache($entity);
        $this->dispatchIndexMessage($entity);
    }

    /**
     * @param AfterEntityDeletedEvent<object> $event
     */
    public function onEntityDeleted(AfterEntityDeletedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        $this->invalidateCache($entity);
        $this->dispatchRemoveMessage($entity);
    }

    private function invalidateCache(object $entity): void
    {
        $this->getCache($entity)->invalidate($entity);
    }

    private function dispatchIndexMessage(object $entity): void
    {
        $entityId = $this->extractEntityId($entity);

        if ($entityId === null) {
            return;
        }

        $this->messageBus->dispatch(new IndexEntityMessage($entity::class, $entityId));
    }

    private function dispatchRemoveMessage(object $entity): void
    {
        $entityId = $this->extractEntityId($entity);

        if ($entityId === null) {
            return;
        }

        $message = new RemoveEntityFromIndexMessage(
            entityClass: $entity::class,
            entityId: $entityId,
        );

        $this->messageBus->dispatch($message);
    }

    private function extractEntityId(object $entity): ?int
    {
        if (method_exists($entity, 'getId')) {
            return $entity->getId();
        }

        return null;
    }

    private function getCache(object $entity): InvalidatableEntityCacheInterface
    {
        foreach ($this->entityCaches as $cache) {
            if ($cache::supports($entity)) {
                return $cache;
            }
        }

        throw new \RuntimeException('No cache service found for entity ' . $entity::class);
    }
}
