<?php

declare(strict_types=1);

namespace App\Services\Post\Cache;

use App\Entity\Post;
use App\Repository\PostRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class PostCache
{
    private const int CACHE_TTL = 3600; // 1 hour

    public function __construct(private CacheInterface $cache, private PostRepository $repository)
    {
    }

    /**
     * @return array<array-key,mixed>
     *
     * @throws InvalidArgumentException
     */
    public function getLocalizedCachedPosts(string $locale): array
    {
        return $this->cache->get('posts_index_'.$locale, function (ItemInterface $item) {
            $item->expiresAfter(self::CACHE_TTL);
            return $this->repository->findAllActive();
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getLocalizedCachedPost(string $locale, string $postSlug): ?Post
    {
        return $this->cache->get(
            sprintf('posts_show_%s_%s', $locale, $postSlug),
            function (ItemInterface $item) use ($postSlug) {
                $item->expiresAfter(self::CACHE_TTL);
                return $this->repository->findOneActive($postSlug);
            });
    }

//    /**
//     * @throws InvalidArgumentException
//     */
//    public function removeItem(array $slugs): void
//    {
//        foreach (explode('|', $this->supportedLocales) as $locale) {
//            $keys = [
//                'posts_index_'.$locale,
//                sprintf('posts_show_%s_%s', $locale, $slugs[$locale])
//            ];
//
//            foreach ($keys as $key) {
//                $this->cache->delete($key);
//            }
//        }
//    }
}
