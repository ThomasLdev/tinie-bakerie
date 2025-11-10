<?php

declare(strict_types=1);

namespace App\Services\Cache;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Event\CacheInvalidationEvent;
use App\Repository\CategoryRepository;
use App\Services\Locale\Locales;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

readonly class CategoryCache extends AbstractEntityCache
{
    private const string ENTITY_NAME = 'category';

    public function __construct(
        #[Autowire(service: 'cache.app.taggable')]
        TagAwareCacheInterface $cache,
        CacheKeyGenerator $keyGenerator,
        LoggerInterface $logger,
        private CategoryRepository $repository,
        private Locales $locales,
        private HeaderCache $headerCache,
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

                // Add cache tags
                $item->tag([
                    'categories',
                    'categories_index',
                ]);

                return $this->repository->findAll();
            });
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Category cache read failed, using direct DB query', [
                'exception' => $e->getMessage(),
                'key' => $key,
            ]);

            return $this->repository->findAll();
        }
    }

    public function invalidate(object $entity): void
    {
        if (!$entity instanceof Category) {
            return;
        }

        try {
            $this->logger->info('Invalidating category cache', [
                'entity' => 'Category',
                'id' => $entity->getId(),
            ]);

            $this->cache->invalidateTags([
                'category_' . $entity->getId(),
                'categories_index',
            ]);

            // Invalidate header cache since category list changed
            $locales = $this->locales->get();
            $this->headerCache->invalidate($locales);

            // Invalidate slug mapping caches for all locales
            foreach ($locales as $locale) {
                $translation = $entity->getTranslationByLocale($locale);

                if ($translation instanceof CategoryTranslation) {
                    $this->cache->delete(
                        $this->keyGenerator->slugMapping($this->getEntityName(), $locale, $translation->getSlug()),
                    );
                }
            }

            // Dispatch event to notify other caches (like PostCache) that need invalidation
            $this->eventDispatcher->dispatch(
                new CacheInvalidationEvent($entity, 'update'),
                CacheInvalidationEvent::CATEGORY_INVALIDATED,
            );
        } catch (InvalidArgumentException $e) {
            $this->logger->error('Category cache invalidation failed', [
                'exception' => $e->getMessage(),
                'entity' => 'Category',
                'id' => $entity->getId(),
            ]);
            // Don't throw - allow operation to continue
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

    protected function loadEntityById(int $id): ?Category
    {
        return $this->repository->findOneById($id);
    }

    protected function loadEntityBySlug(string $slug): ?Category
    {
        return $this->repository->findOne($slug);
    }

    protected function generateCacheTags(object $entity): array
    {
        \assert($entity instanceof Category);

        return [
            'categories',
            'category_' . $entity->getId(),
        ];
    }

    protected function extractEntityId(object $entity): ?int
    {
        \assert($entity instanceof Category);

        return $entity->getId();
    }
}
