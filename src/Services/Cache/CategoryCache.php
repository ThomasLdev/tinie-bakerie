<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Repository\CategoryRepository;
use App\Services\Locale\Locales;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class CategoryCache implements EntityCacheInterface
{
    private const int CACHE_TTL = 3600; // 1 hour

    private const string ENTITY_NAME = 'category';

    public function __construct(
        private CacheInterface $cache,
        private CategoryRepository $repository,
        private CacheKeyGenerator $keyGenerator,
        private Locales $locales,
        private PostCache $postCache
    )
    {
    }

    /**
     * @return Category[]
     *
     * @throws InvalidArgumentException
     */
    public function get(string $locale): array
    {
        $key = $this->keyGenerator->entityIndex($this->getEntityName(), $locale);

        return $this->cache->get($key, function (ItemInterface $item) {
            $item->expiresAfter(self::CACHE_TTL);
            return $this->repository->findAll();
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getOne(string $locale, string $identifier): ?Category
    {
        $key = $this->keyGenerator->entityShow($this->getEntityName(), $locale, $identifier);

        return $this->cache->get($key, function (ItemInterface $item) use ($identifier) {
            $item->expiresAfter(self::CACHE_TTL);
            return $this->repository->findOne($identifier);
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function invalidate(object $entity): void
    {
        if (!$entity instanceof Category) {
            return;
        }

        $this->postCache->invalidateByCriteria(['category' => $entity]);

        foreach ($this->locales->get() as $locale) {
            $this->cache->delete($this->keyGenerator->entityIndex(self::ENTITY_NAME, $locale));

            $translation = $entity->getTranslationByLocale($locale);

            if (!$translation instanceof CategoryTranslation) {
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
        return $entity instanceof Category;
    }
}
