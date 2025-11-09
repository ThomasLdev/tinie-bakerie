<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Repository\CategoryRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class HeaderCache
{
    private const int CACHE_TTL = 3600; // 1 hour

    private const string CACHE_KEY_PREFIX = 'header_categories';

    public function __construct(
        private CacheInterface $cache,
        private CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * Get cached categories for header navigation.
     *
     * @throws InvalidArgumentException
     *
     * @return array<array-key,mixed>
     */
    public function getCategories(string $locale): array
    {
        $key = $this->generateKey($locale);

        return $this->cache->get($key, function (ItemInterface $item): array {
            $item->expiresAfter(self::CACHE_TTL);

            return $this->categoryRepository->findAllSlugs();
        });
    }

    /**
     * Invalidate header cache for all locales.
     *
     * This should be called when categories are created, updated, or deleted.
     *
     * @param array<string> $locales
     *
     * @throws InvalidArgumentException
     */
    public function invalidate(array $locales): void
    {
        foreach ($locales as $locale) {
            $this->cache->delete($this->generateKey($locale));
        }
    }

    private function generateKey(string $locale): string
    {
        return \sprintf('%s_%s', self::CACHE_KEY_PREFIX, $locale);
    }
}
