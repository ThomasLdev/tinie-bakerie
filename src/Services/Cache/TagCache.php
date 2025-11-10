<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Entity\Tag;
use App\Event\CacheInvalidationEvent;
use App\Repository\TagRepository;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

readonly class TagCache implements EntityCacheInterface
{
    private const int CACHE_TTL = 3600; // 1 hour

    private const string ENTITY_NAME = 'tag';

    public function __construct(
        #[Autowire(service: 'cache.app.taggable')]
        private TagAwareCacheInterface $cache,
        private TagRepository $repository,
        private CacheKeyGenerator $keyGenerator,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return array<array-key,mixed>
     */
    public function get(string $locale): array
    {
        $key = $this->keyGenerator->entityIndex($this->getEntityName(), $locale);

        try {
            return $this->cache->get($key, function (ItemInterface $item): array {
                $item->expiresAfter(self::CACHE_TTL);

                // Add cache tags
                $item->tag([
                    'tags',
                    'tags_index',
                ]);

                return $this->repository->findAll();
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Tag cache read failed, using direct DB query', [
                'exception' => $e->getMessage(),
                'key' => $key,
            ]);

            return $this->repository->findAll();
        }
    }

    public function getOne(string $locale, string $identifier): ?Tag
    {
        $id = is_numeric($identifier) ? (int) $identifier : null;

        if (null === $id) {
            return null;
        }

        $key = $this->keyGenerator->entityShow($this->getEntityName(), $locale, $id);

        try {
            return $this->cache->get($key, function (ItemInterface $item) use ($id): ?Tag {
                $item->expiresAfter(self::CACHE_TTL);

                $tag = $this->repository->findOne($id);

                if ($tag) {
                    // Add cache tags
                    $item->tag([
                        'tags',
                        'tag_' . $tag->getId(),
                    ]);
                }

                return $tag;
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Tag cache read failed, using direct DB query', [
                'exception' => $e->getMessage(),
                'key' => $key,
            ]);

            return $this->repository->findOne($id);
        }
    }

    public function invalidate(object $entity): void
    {
        if (!$entity instanceof Tag) {
            return;
        }

        try {
            $this->logger->info('Invalidating tag cache', [
                'entity' => 'Tag',
                'id' => $entity->getId(),
            ]);

            $this->cache->invalidateTags([
                'tag_' . $entity->getId(),
                'tags_index',
            ]);

            $this->eventDispatcher->dispatch(
                new CacheInvalidationEvent($entity, 'update'),
                CacheInvalidationEvent::TAG_INVALIDATED,
            );
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Tag cache invalidation failed', [
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
