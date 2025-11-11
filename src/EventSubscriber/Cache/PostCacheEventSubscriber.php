<?php

declare(strict_types=1);

namespace App\EventSubscriber\Cache;

use App\Entity\Category;
use App\Entity\Tag;
use App\Event\CacheInvalidationEvent;
use App\Services\Cache\PostCache;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listens to cache invalidation events and invalidates related post caches.
 */
readonly class PostCacheEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PostCache $postCache,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CacheInvalidationEvent::CATEGORY_INVALIDATED => 'onCategoryInvalidated',
            CacheInvalidationEvent::TAG_INVALIDATED => 'onTagInvalidated',
        ];
    }

    /**
     * When a category is invalidated, invalidate all posts in that category.
     */
    public function onCategoryInvalidated(CacheInvalidationEvent $event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof Category) {
            return;
        }

        $categoryId = $entity->getId();

        if (null === $categoryId) {
            $this->logger->warning('PostCache: Cannot invalidate posts for category without ID');

            return;
        }

        $this->logger->info('PostCache: Invalidating posts due to category change', [
            'category_id' => $categoryId,
            'operation' => $event->getOperation(),
        ]);

        try {
            $this->postCache->invalidateByCategory($categoryId);
        } catch (\Exception $e) {
            $this->logger->error('PostCache: Failed to invalidate posts for category', [
                'category_id' => $categoryId,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * When a tag is invalidated, invalidate all posts with that tag.
     */
    public function onTagInvalidated(CacheInvalidationEvent $event): void
    {
        $entity = $event->getEntity();

        if (!$entity instanceof Tag) {
            return;
        }

        $tagId = $entity->getId();

        if (null === $tagId) {
            $this->logger->warning('PostCache: Cannot invalidate posts for tag without ID');

            return;
        }

        $this->logger->info('PostCache: Invalidating posts due to tag change', [
            'tag_id' => $tagId,
            'operation' => $event->getOperation(),
        ]);

        try {
            $this->postCache->invalidateByTag($tagId);
        } catch (\Exception $e) {
            $this->logger->error('PostCache: Failed to invalidate posts for tag', [
                'tag_id' => $tagId,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
