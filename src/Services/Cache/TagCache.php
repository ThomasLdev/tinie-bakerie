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

readonly class TagCache extends AbstractEntityCache
{
    private const string ENTITY_NAME = 'tag';

    public function __construct(
        #[Autowire(service: 'cache.app.taggable')]
        TagAwareCacheInterface $cache,
        CacheKeyGenerator $keyGenerator,
        LoggerInterface $logger,
        private TagRepository $repository,
        private EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($cache, $keyGenerator, $logger);
    }

    /**
     * @return array<array-key,mixed>
     */
    public function get(string $locale): array
    {
        $key = $this->keyGenerator->entityIndex($this->getEntityName(), $locale);

        try {
            return $this->cache->get($key, function (ItemInterface $item): array {
                $item->expiresAfter(self::CACHE_TTL);

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

    /**
     * Override resolveIdentifierToId because Tag doesn't support slug resolution.
     * Tags only work with numeric IDs.
     */
    protected function resolveIdentifierToId(string $locale, string $identifier): ?int
    {
        return is_numeric($identifier) ? (int) $identifier : null;
    }

    protected function loadEntityById(int $id): ?Tag
    {
        return $this->repository->findOne($id);
    }

    protected function loadEntityBySlug(string $slug): ?Tag
    {
        // Tags don't have slugs - this should never be called due to overridden resolveIdentifierToId
        return null;
    }

    protected function generateCacheTags(object $entity): array
    {
        \assert($entity instanceof Tag);

        return [
            'tags',
            'tag_' . $entity->getId(),
        ];
    }

    protected function extractEntityId(object $entity): ?int
    {
        \assert($entity instanceof Tag);

        return $entity->getId();
    }
}
