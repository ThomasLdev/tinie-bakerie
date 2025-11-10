<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Repository\PostRepository;
use App\Services\Locale\Locales;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

readonly class PostCache implements EntityCacheInterface
{
    private const int CACHE_TTL = 3600; // 1 hour

    private const string ENTITY_NAME = 'post';

    public function __construct(
        #[Autowire(service: 'cache.app.taggable')]
        private TagAwareCacheInterface $cache,
        private PostRepository $repository,
        private CacheKeyGenerator $keyGenerator,
        private Locales $locales,
        private LoggerInterface $logger,
    ) {
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
                    'posts',
                    'posts_index',
                ]);

                return $this->repository->findAllActive();
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Post cache read failed, using direct DB query', [
                'exception' => $e->getMessage(),
                'key' => $key,
            ]);

            return $this->repository->findAllActive();
        }
    }

    public function getOne(string $locale, string $identifier): ?Post
    {
        $id = $this->resolveIdentifierToId($locale, $identifier);

        if (null === $id) {
            return null;
        }

        $key = $this->keyGenerator->entityShow($this->getEntityName(), $locale, $id);

        try {
            return $this->cache->get($key, function (ItemInterface $item) use ($id): ?Post {
                $item->expiresAfter(self::CACHE_TTL);

                // Query by ID to ensure we cache the correct entity with correct locale
                $post = $this->repository->findOneActiveById($id);

                if ($post) {
                    $tags = [
                        'posts',
                        'post_' . $post->getId(),
                    ];

                    // Add category tag if post has a category
                    if ($post->getCategory()) {
                        $tags[] = 'category_' . $post->getCategory()->getId();
                    }

                    // Add tag tags for each tag
                    foreach ($post->getTags() as $tag) {
                        $tags[] = 'tag_' . $tag->getId();
                    }

                    $item->tag($tags);
                }

                return $post;
            });
        } catch (InvalidArgumentException|\Psr\Cache\CacheException $e) {
            $this->logger->error('Post cache read failed, using direct DB query', [
                'exception' => $e->getMessage(),
                'key' => $key,
            ]);

            return $this->repository->findOneActiveById($id);
        }
    }

    /**
     * Resolve a slug or ID to an entity ID.
     * Caches the slug-to-ID mapping to avoid repeated database lookups.
     * Also proactively caches the full entity to avoid a second query.
     */
    private function resolveIdentifierToId(string $locale, string $identifier): ?int
    {
        // If already numeric, return as is
        if (is_numeric($identifier)) {
            return (int) $identifier;
        }

        // Try to get ID from slug mapping cache
        $mappingKey = $this->keyGenerator->slugMapping($this->getEntityName(), $locale, $identifier);

        try {
            return $this->cache->get($mappingKey, function (ItemInterface $item) use ($locale, $identifier): ?int {
                $item->expiresAfter(self::CACHE_TTL);

                // Query database for entity
                $entity = $this->repository->findOneActive($identifier);

                if (!$entity) {
                    return null;
                }

                // Proactively cache the entity to avoid second query in getOne()
                $this->cacheEntity($entity, $locale);

                return $entity->getId();
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Slug mapping cache failed, using direct DB query', [
                'exception' => $e->getMessage(),
                'key' => $mappingKey,
            ]);

            return $this->repository->findOneActive($identifier)?->getId();
        }
    }

    /**
     * Proactively cache an entity to optimize cold cache performance.
     * This is called when we've already fetched the entity from DB during slug resolution.
     */
    private function cacheEntity(Post $entity, string $locale): void
    {
        $key = $this->keyGenerator->entityShow($this->getEntityName(), $locale, $entity->getId());

        try {
            // Use cache->get() to be idempotent - if already cached, does nothing
            $this->cache->get($key, function (ItemInterface $item) use ($entity): Post {
                $item->expiresAfter(self::CACHE_TTL);

                $tags = [
                    'posts',
                    'post_' . $entity->getId(),
                ];

                // Add category tag if post has a category
                if ($entity->getCategory()) {
                    $tags[] = 'category_' . $entity->getCategory()->getId();
                }

                // Add tag tags for each tag
                foreach ($entity->getTags() as $tag) {
                    $tags[] = 'tag_' . $tag->getId();
                }

                $item->tag($tags);

                return $entity;
            });
        } catch (InvalidArgumentException|\Psr\Cache\CacheException $e) {
            // Don't throw - this is an optimization, not critical path
            $this->logger->warning('Failed to proactively cache entity during slug resolution', [
                'exception' => $e->getMessage(),
                'entity' => 'Post',
                'id' => $entity->getId(),
            ]);
        }
    }

    public function invalidate(object $entity): void
    {
        if (!$entity instanceof Post) {
            return;
        }

        try {
            $this->logger->info('Invalidating post cache', [
                'entity' => 'Post',
                'id' => $entity->getId(),
            ]);

            $this->cache->invalidateTags([
                'post_' . $entity->getId(),
                'posts_index',
            ]);

            // Also invalidate slug mapping caches for all locales
            foreach ($this->locales->get() as $locale) {
                $translation = $entity->getTranslationByLocale($locale);

                if ($translation instanceof PostTranslation) {
                    $this->cache->delete(
                        $this->keyGenerator->slugMapping($this->getEntityName(), $locale, $translation->getSlug()),
                    );
                }
            }
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Post cache invalidation failed', [
                'exception' => $e->getMessage(),
                'entity' => 'Post',
                'id' => $entity->getId(),
            ]);
        }
    }

    /**
     * Invalidate all posts in a given category using cache tags.
     */
    public function invalidateByCategory(int $categoryId): void
    {
        try {
            $this->logger->info('Invalidating posts by category tag', [
                'category_id' => $categoryId,
            ]);

            $this->cache->invalidateTags([
                'category_' . $categoryId,
                'posts_index',
            ]);
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Post cache invalidation by category failed', [
                'exception' => $e->getMessage(),
                'category_id' => $categoryId,
            ]);
        }
    }

    /**
     * Invalidate all posts with a given tag using cache tags.
     */
    public function invalidateByTag(int $tagId): void
    {
        try {
            $this->logger->info('Invalidating posts by tag', [
                'tag_id' => $tagId,
            ]);

            $this->cache->invalidateTags([
                'tag_' . $tagId,
                'posts_index',
            ]);
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Post cache invalidation by tag failed', [
                'exception' => $e->getMessage(),
                'tag_id' => $tagId,
            ]);
        }
    }

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public static function supports(object $entity): bool
    {
        return $entity instanceof Post;
    }
}
