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

/**
 * @method Post|null getOne(string $locale, string $identifier)
 */
readonly class PostCache extends AbstractEntityCache
{
    private const string ENTITY_NAME = 'post';

    public function __construct(
        #[Autowire(service: 'cache.app.taggable')]
        TagAwareCacheInterface $cache,
        CacheKeyGenerator $keyGenerator,
        LoggerInterface $logger,
        private PostRepository $repository,
        private Locales $locales,
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

    protected function loadEntityById(int $id): ?Post
    {
        return $this->repository->findOneActiveById($id);
    }

    protected function loadEntityBySlug(string $slug): ?Post
    {
        return $this->repository->findOneActive($slug);
    }

    protected function generateCacheTags(object $entity): array
    {
        \assert($entity instanceof Post);

        $tags = [
            'posts',
            'post_' . $entity->getId(),
        ];

        if ($entity->getCategory()) {
            $tags[] = 'category_' . $entity->getCategory()->getId();
        }

        foreach ($entity->getTags() as $tag) {
            $tags[] = 'tag_' . $tag->getId();
        }

        return $tags;
    }

    protected function extractEntityId(object $entity): ?int
    {
        \assert($entity instanceof Post);

        return $entity->getId();
    }

    /**
     * Warm up the cache for the given locale by pre-loading all active posts.
     * This caches both the post index AND each individual post detail page.
     *
     * @return int Number of posts cached
     */
    public function warmUp(string $locale): int
    {
        // First, warm the index (list of all posts)
        $posts = $this->get($locale);

        // Then, warm each individual post detail page
        // This caches both the entity and the slug-to-ID mapping
        foreach ($posts as $post) {
            $this->getOne($locale, (string) $post->getId());
        }

        return \count($posts);
    }
}
