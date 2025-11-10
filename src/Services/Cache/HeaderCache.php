<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Repository\CategoryRepository;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

readonly class HeaderCache
{
    private const int CACHE_TTL = 604800; // 7 days

    private const string CACHE_KEY_PREFIX = 'header_categories';

    public function __construct(
        private CacheInterface $cache,
        private CategoryRepository $categoryRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Get cached categories for header navigation.
     *
     * @return array<array-key,mixed>
     */
    public function getCategories(string $locale): array
    {
        $key = $this->generateKey($locale);

        try {
            return $this->cache->get($key, function (ItemInterface $item): array {
                $item->expiresAfter(self::CACHE_TTL);

                return $this->categoryRepository->findAllSlugs();
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Header cache read failed, using direct DB query', [
                'exception' => $e->getMessage(),
                'key' => $key,
            ]);

            return $this->categoryRepository->findAllSlugs();
        }
    }

    /**
     * Invalidate header cache for all locales.
     *
     * This should be called when categories are created, updated, or deleted.
     *
     * @param array<string> $locales
     */
    public function invalidate(array $locales): void
    {
        try {
            $this->logger->info('Invalidating header cache', [
                'locales' => $locales,
            ]);

            foreach ($locales as $locale) {
                $this->cache->delete($this->generateKey($locale));
            }
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Header cache invalidation failed', [
                'exception' => $e->getMessage(),
                'locales' => $locales,
            ]);
        }
    }

    private function generateKey(string $locale): string
    {
        return \sprintf('%s_%s', self::CACHE_KEY_PREFIX, $locale);
    }
}
