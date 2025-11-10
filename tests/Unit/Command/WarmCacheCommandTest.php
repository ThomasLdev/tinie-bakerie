<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\WarmCacheCommand;
use App\Services\Cache\PostCache;
use App\Services\Cache\CategoryCache;
use App\Tests\Story\PostControllerTestStory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @internal
 */
#[CoversClass(WarmCacheCommand::class)]
final class WarmCacheCommandTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;
    private CommandTester $commandTester;

    private AdapterInterface $cache;

    protected function setUp(): void
    {
        self::bootKernel();

        $kernel = self::$kernel;

        if (null === $kernel) {
            throw new \RuntimeException('Kernel is not available');
        }

        $application = new Application($kernel);

        $command = $application->find('app:cache:warm');
        $this->commandTester = new CommandTester($command);

        // Get cache service for clearing before tests
        $container = self::getContainer();
        /** @var AdapterInterface $cache */
        $cache = $container->get('cache.app.taggable');
        $this->cache = $cache;
    }

    public function testExecuteWarmsAllCaches(): void
    {
        // Clear cache before test
        $this->cache->clear();

        $this->commandTester->execute([]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Cache Warming', $output);
        self::assertStringContainsString('Locales: fr, en', $output);
        self::assertStringContainsString('Cache Statistics:', $output);
        self::assertStringContainsString('Post:', $output);
        self::assertStringContainsString('Category:', $output);
        self::assertStringContainsString('Header:', $output);
        self::assertStringContainsString('Cache warmed successfully', $output);
    }

    public function testExecuteWithClearFirstOption(): void
    {
        $this->commandTester->execute(['--clear-first' => true]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Clearing cache pool', $output);
        self::assertStringContainsString('Cache cleared', $output);
        self::assertStringContainsString('Cache warmed successfully', $output);
    }

    public function testExecuteWithCacheFilterPost(): void
    {
        $this->commandTester->execute(['--cache' => 'post']);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Post:', $output);
        self::assertStringContainsString('entities cached', $output);

        // Verify only post cache was warmed (others not mentioned)
        self::assertStringNotContainsString('Category:', $output);
        self::assertStringNotContainsString('Header:', $output);
    }

    public function testExecuteWithCacheFilterCategory(): void
    {
        $this->commandTester->execute(['--cache' => 'category']);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Category:', $output);
        self::assertStringContainsString('entities cached', $output);

        // Verify only category cache was warmed
        self::assertStringNotContainsString('Post:', $output);
        self::assertStringNotContainsString('Header:', $output);
    }

    public function testExecuteWithCacheFilterHeader(): void
    {
        $this->commandTester->execute(['--cache' => 'header']);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Header:', $output);
        self::assertStringContainsString('entities cached', $output);

        // Verify only header cache was warmed
        self::assertStringNotContainsString('Post:', $output);
        self::assertStringNotContainsString('Category:', $output);
    }

    public function testExecuteWithInvalidCacheFilter(): void
    {
        $this->commandTester->execute(['--cache' => 'invalid']);

        self::assertSame(1, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Invalid cache type "invalid"', $output);
        self::assertStringContainsString('Available caches:', $output);
    }

    public function testExecuteDisplaysDuration(): void
    {
        $this->commandTester->execute([]);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        // Check that duration is displayed (format: "in X.XXs")
        self::assertMatchesRegularExpression('/Cache warmed successfully in \d+\.\d+s/', $output);
    }

    /**
     * CRITICAL TEST: Verify warmup caches only French translations for French locale
     * This test would have caught the locale filter bug.
     */
    public function testWarmupCachesCorrectLocaleDataForFrench(): void
    {
        // Arrange: Load test story with posts in both locales
        PostControllerTestStory::load();

        // Act: Clear and warm cache
        $this->cache->clear();
        $this->commandTester->execute(['--clear-first' => true]);

        // Assert: Command succeeded
        $this->commandTester->assertCommandIsSuccessful();

        // Assert: Get French cache and verify ONLY French translations
        $container = self::getContainer();
        /** @var PostCache $postCache */
        $postCache = $container->get(PostCache::class);
        $frenchPosts = $postCache->get('fr');

        self::assertNotEmpty($frenchPosts, 'French cache should contain posts');

        foreach ($frenchPosts as $post) {
            $title = $post->getTitle();
            self::assertStringContainsString(
                'FR',
                $title,
                \sprintf('French cache should only contain French titles, but found: "%s"', $title)
            );
            self::assertStringNotContainsString(
                'EN',
                $title,
                \sprintf('French cache should NOT contain English titles, but found: "%s"', $title)
            );
        }
    }

    /**
     * CRITICAL TEST: Verify warmup caches only English translations for English locale.
     */
    public function testWarmupCachesCorrectLocaleDataForEnglish(): void
    {
        PostControllerTestStory::load();

        $this->cache->clear();
        $this->commandTester->execute(['--clear-first' => true]);

        $this->commandTester->assertCommandIsSuccessful();

        $container = self::getContainer();
        /** @var PostCache $postCache */
        $postCache = $container->get(PostCache::class);
        $englishPosts = $postCache->get('en');

        self::assertNotEmpty($englishPosts, 'English cache should contain posts');

        foreach ($englishPosts as $post) {
            $title = $post->getTitle();
            self::assertStringContainsString(
                'EN',
                $title,
                \sprintf('English cache should only contain English titles, but found: "%s"', $title)
            );
            self::assertStringNotContainsString(
                'FR',
                $title,
                \sprintf('English cache should NOT contain French titles, but found: "%s"', $title)
            );
        }
    }

    /**
     * CRITICAL TEST: Verify individual post detail pages are warmed with correct locale
     * This ensures slug-to-ID mappings are locale-specific.
     */
    public function testWarmupCachesIndividualPostsWithCorrectLocale(): void
    {
        $story = PostControllerTestStory::load();
        $post = $story->getActivePost(0);

        // Get slugs for both locales
        $frenchSlug = $story->getPostSlug($post, 'fr');
        $englishSlug = $story->getPostSlug($post, 'en');

        $this->cache->clear();
        $this->commandTester->execute(['--clear-first' => true]);

        $this->commandTester->assertCommandIsSuccessful();

        $container = self::getContainer();
        /** @var PostCache $postCache */
        $postCache = $container->get(PostCache::class);

        // Fetch by French slug in French locale
        $frenchPost = $postCache->getOne('fr', $frenchSlug);
        self::assertNotNull($frenchPost, 'Should find post by French slug in French locale');
        self::assertStringContainsString('FR', $frenchPost->getTitle(), 'French post should have French title');

        // Fetch by English slug in English locale
        $englishPost = $postCache->getOne('en', $englishSlug);
        self::assertNotNull($englishPost, 'Should find post by English slug in English locale');
        self::assertStringContainsString('EN', $englishPost->getTitle(), 'English post should have English title');
    }

    /**
     * CRITICAL TEST: Verify slug-to-ID mappings are locale-specific
     * Ensures French slug doesn't map to English content and vice versa.
     * THIS TEST CATCHES THE BUG YOU DISCOVERED!
     */
    public function testWarmupDoesNotMixLocalesInSlugMappings(): void
    {
        $story = PostControllerTestStory::load();
        $post = $story->getActivePost(0);

        $frenchSlug = $story->getPostSlug($post, 'fr');
        $englishSlug = $story->getPostSlug($post, 'en');

        $this->cache->clear();
        $this->commandTester->execute([]);

        $this->commandTester->assertCommandIsSuccessful();

        $container = self::getContainer();
        /** @var PostCache $postCache */
        $postCache = $container->get(PostCache::class);

        // Try to fetch ENGLISH slug in FRENCH locale - should return null
        $wrongLocalePost = $postCache->getOne('fr', $englishSlug);
        self::assertNull(
            $wrongLocalePost,
            \sprintf('Should NOT find English slug "%s" in French locale context', $englishSlug)
        );

        // Try to fetch FRENCH slug in ENGLISH locale - should return null
        $wrongLocalePost2 = $postCache->getOne('en', $frenchSlug);
        self::assertNull(
            $wrongLocalePost2,
            \sprintf('Should NOT find French slug "%s" in English locale context', $frenchSlug)
        );
    }
}
