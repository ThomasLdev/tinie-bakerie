<?php

declare(strict_types=1);

namespace App\Services\Post\Cache;

use App\Entity\Post;
use App\Repository\PostRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Throwable;

readonly class PostCache
{
    private const int CACHE_TTL = 3600; // 1 hour

    public function __construct(
        private CacheInterface $cache,
        private PostRepository $repository,
    ) {
    }

    /**
     * @return array<array-key,Post>
     */
    public function getLocalizedCachedPosts(string $locale): array
    {
        try {
            return $this->cache->get('posts_index_'.$locale, function (ItemInterface $item) {
                $item->expiresAfter(self::CACHE_TTL);

                return $this->repository->findAllPublished();
            });
        } catch (Throwable) {
            return $this->repository->findAllPublished();
        }
    }

    public function getLocalizedCachedPost(string $locale, string $postSlug): ?Post
    {
        try {
            return $this->cache->get(
                sprintf('posts_show_%s_%s', $locale, $postSlug),
                function (ItemInterface $item) use ($postSlug) {
                    $item->expiresAfter(self::CACHE_TTL);

                    return $this->repository->findOnePublishedBySlug($postSlug);
                });
        } catch (Throwable) {
            return $this->repository->findOnePublishedBySlug($postSlug);
        }
    }
}
