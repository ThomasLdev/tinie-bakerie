<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\Cache;

use App\Services\Cache\CategoryCache;
use App\Tests\Story\CategoryControllerTestStory;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @internal
 *
 * Test that verifies the cache optimization that prevents double queries on cold cache.
 */
#[CoversClass(CategoryCache::class)]
final class CategoryCacheOptimizationTest extends KernelTestCase
{
    use ResetDatabase;

    private CategoryCache $cache;
    private TagAwareCacheInterface $cacheBackend;
    private EntityManagerInterface $entityManager;
    private DebugStack $sqlLogger;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->cache = $container->get(CategoryCache::class);
        $this->cacheBackend = $container->get('cache.app.taggable');
        $this->entityManager = $container->get(EntityManagerInterface::class);

        // Set up SQL query logger
        $this->sqlLogger = new DebugStack();
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger($this->sqlLogger);
    }

    /**
     * This is the main test that proves the optimization works:
     * - On cold cache, accessing by slug executes only 1 query
     * - The entity is proactively cached during slug resolution
     * - Subsequent accesses (by slug or ID) are cache hits with 0 queries
     */
    public function testColdCacheOptimizationReducesQueriesToOne(): void
    {
        // Given: Load test data and ensure cold cache
        CategoryControllerTestStory::load();
        $this->cacheBackend->clear();
        $this->entityManager->clear();
        $this->sqlLogger->queries = [];
        
        // When: First access by slug (cold cache)
        $categoryBySlug = $this->cache->getOne('fr', 'categorie-test-1-fr');

        // Then: Should execute exactly 1 query
        self::assertNotNull($categoryBySlug, 'Category should be found');
        self::assertSame('categorie-test-1-fr', $categoryBySlug->getSlug());

        $selectQueries = $this->getEntitySelectQueries();
        self::assertCount(
            1,
            $selectQueries,
            sprintf(
                "Expected exactly 1 SELECT query on cold cache, got %d.\nQueries:\n%s",
                count($selectQueries),
                $this->formatQueries($selectQueries),
            ),
        );

        $categoryId = $categoryBySlug->getId();
        $queriesAfterFirstAccess = count($this->sqlLogger->queries);

        // Clear entity manager to ensure we're not using identity map
        $this->entityManager->clear();

        // When: Second access by same slug (warm cache)
        $categoryBySlugAgain = $this->cache->getOne('fr', 'categorie-test-1-fr');
        
        // Then: Should be cache hit with no additional queries
        self::assertNotNull($categoryBySlugAgain);
        self::assertSame($categoryId, $categoryBySlugAgain->getId());
        self::assertSame(
            $queriesAfterFirstAccess,
            count($this->sqlLogger->queries),
            'Second slug access should not execute any queries (cache hit)',
        );

        // Clear entity manager again
        $this->entityManager->clear();

        // When: Access by ID (warm cache - entity was proactively cached)
        $categoryById = $this->cache->getOne('fr', (string) $categoryId);
        
        // Then: Should be cache hit with no additional queries
        self::assertNotNull($categoryById);
        self::assertSame($categoryId, $categoryById->getId());
        self::assertSame(
            $queriesAfterFirstAccess,
            count($this->sqlLogger->queries),
            'Access by ID should not execute any queries (entity was proactively cached)',
        );
    }

    /**
     * Filter and return only SELECT queries from category tables.
     *
     * @return array<array-key, array<string, mixed>>
     */
    private function getEntitySelectQueries(): array
    {
        return array_filter(
            $this->sqlLogger->queries,
            fn (array $query): bool => str_starts_with(strtoupper($query['sql']), 'SELECT')
                && str_contains($query['sql'], 'FROM category')
                && !str_contains($query['sql'], 'NEXTVAL'),
        );
    }

    /**
     * Format queries for debugging output.
     *
     * @param array<array-key, array<string, mixed>> $queries
     */
    private function formatQueries(array $queries): string
    {
        $output = [];
        foreach ($queries as $index => $query) {
            $output[] = sprintf("#%d: %s", $index + 1, $query['sql']);
        }

        return implode("\n", $output);
    }

    protected function tearDown(): void
    {
        // Clean up SQL logger
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        parent::tearDown();
    }
}
