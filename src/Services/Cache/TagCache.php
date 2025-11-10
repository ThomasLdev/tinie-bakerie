<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Entity\Tag;
use App\Event\CacheInvalidationEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 *
 * Tags don't have public pages, so this service only handles event dispatching.
 * When tags change, this notifies PostCache (via TAG_INVALIDATED event) to
 * invalidate posts that use those tags.
 *
 * Unlike CategoryCache and PostCache, this doesn't extend AbstractEntityCache
 * because tags are never fetched individually or cached for display.
 */
readonly class TagCache implements InvalidatableEntityCacheInterface
{
    private const string ENTITY_NAME = 'tag';

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function invalidate(object $entity): void
    {
        if (!$entity instanceof Tag) {
            return;
        }

        try {
            $this->logger->info('Tag changed, notifying PostCache', [
                'entity' => 'Tag',
                'id' => $entity->getId(),
            ]);

            $this->eventDispatcher->dispatch(
                new CacheInvalidationEvent($entity, 'update'),
                CacheInvalidationEvent::TAG_INVALIDATED,
            );
        } catch (\Exception $e) {
            $this->logger->error('Tag invalidation event dispatch failed', [
                'exception' => $e->getMessage(),
                'entity' => 'Tag',
                'id' => $entity->getId(),
            ]);
        }
    }

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public static function supports(object $entity): bool
    {
        return $entity instanceof Tag;
    }
}
