<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Repository\PostRepository;
use App\Services\Locale\Locales;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class PostCache implements EntityCacheInterface
{
    private const int CACHE_TTL = 3600; // 1 hour

    private const string ENTITY_NAME = 'post';

    public function __construct(
        private CacheInterface $cache,
        private PostRepository $repository,
        private CacheKeyGenerator $keyGenerator,
        private Locales $locales,
    ) {
    }

    /**
     * @return array<array-key,mixed>
     *
     * @throws InvalidArgumentException
     */
    public function get(string $locale): array
    {
        $key = $this->keyGenerator->entityIndex($this->getEntityName(), $locale);

        return $this->cache->get($key, function (ItemInterface $item) {
            $item->expiresAfter(self::CACHE_TTL);

            return $this->repository->findAllActive();
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getOne(string $locale, string $identifier): ?Post
    {
        $key = $this->keyGenerator->entityShow($this->getEntityName(), $locale, $identifier);

        return $this->cache->get($key, function (ItemInterface $item) use ($identifier) {
            $item->expiresAfter(self::CACHE_TTL);

            return $this->repository->findOneActive($identifier);
        });
    }

    /**
     * @param array<string,mixed> $criteria
     *
     * @throws InvalidArgumentException
     */
    public function invalidateByCriteria(array $criteria): void
    {
        $posts = $this->repository->findBy($criteria);

        foreach ($posts as $post) {
            $this->invalidate($post);
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function invalidate(object $entity): void
    {
        if (!$entity instanceof Post) {
            return;
        }

        foreach ($this->locales->get() as $locale) {
            $this->cache->delete($this->keyGenerator->entityIndex(self::ENTITY_NAME, $locale));

            $translation = $entity->getTranslationByLocale($locale);

            if (!$translation instanceof PostTranslation) {
                continue;
            }

            $this->cache->delete(
                $this->keyGenerator->entityShow($this->getEntityName(), $locale, $translation->getSlug())
            );
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
