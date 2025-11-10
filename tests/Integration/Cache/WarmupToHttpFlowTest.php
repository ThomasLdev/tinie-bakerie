<?php

declare(strict_types=1);

namespace App\Tests\Integration\Cache;

use App\Command\WarmCacheCommand;
use App\Services\Cache\PostCache;
use App\Tests\Story\PostControllerTestStory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * INTEGRATION TEST: Verifies the full warmup → HTTP request flow.
 * This tests the entire system: CLI warmup, cache storage, HTTP retrieval.
 * This simulates real deployment scenarios.
 *
 * @internal
 */
#[CoversClass(WarmCacheCommand::class)]
final class WarmupToHttpFlowTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    /**
     * Test that cache warmed via CLI (deployment scenario) is correctly used during HTTP requests.
     */
    public function testWarmupThenHttpRequestUsesCorrectCache(): void
    {
        // Arrange
        $client = static::createClient();
        $kernel = self::$kernel;

        if (null === $kernel) {
            throw new \RuntimeException('Kernel is not available');
        }

        PostControllerTestStory::load();

        // Act 1: Warm cache via CLI (simulates deployment)
        $application = new Application($kernel);
        $command = $application->find('app:cache:warm');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--clear-first' => true]);

        self::assertSame(0, $commandTester->getStatusCode(), 'Warmup should succeed');

        // Act 2: Make HTTP request to French post index
        $crawler = $client->request('GET', '/fr/articles');

        // Assert: Page should load successfully with French content
        self::assertResponseIsSuccessful();
        $postCards = $crawler->filter('[data-test-id^="post-card-"]');
        self::assertGreaterThan(0, $postCards->count(), 'Should display post cards');

        // Assert: Content should be in French
        $html = $crawler->html();
        self::assertStringContainsString('FR', $html, 'Page should contain French content');

        // Assert: Cache was populated correctly (verify cache service directly)
        /** @var PostCache $postCache */
        $postCache = self::getContainer()->get(PostCache::class);
        $cachedPosts = $postCache->get('fr');

        self::assertNotEmpty($cachedPosts, 'Cache should contain French posts after warmup');

        // Verify all cached posts are French
        foreach ($cachedPosts as $post) {
            self::assertStringContainsString(
                'FR',
                $post->getTitle(),
                \sprintf('Cached post should have French title, but got: "%s"', $post->getTitle()),
            );
        }
    }

    /**
     * Test that individual post pages work correctly after warmup.
     */
    public function testWarmupThenHttpRequestToIndividualPostWorks(): void
    {
        $client = static::createClient();
        $kernel = self::$kernel;

        if (null === $kernel) {
            throw new \RuntimeException('Kernel is not available');
        }

        $story = PostControllerTestStory::load();
        $post = $story->getActivePost(0);
        $category = $post->getCategory();
        self::assertNotNull($category, 'Post should have a category');

        // Warm cache
        $application = new Application($kernel);
        $command = $application->find('app:cache:warm');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--clear-first' => true]);

        self::assertSame(0, $commandTester->getStatusCode());

        // After cache warmup, disable the locale filter to access all translations in test
        // The warmup command enables the locale filter during execution, which persists in the test context
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $entityManager->getFilters()->disable('locale_filter');

        // Clear and refresh entities to load all translations
        $entityManager->clear();
        $post = $story->getActivePost(0);
        $category = $post->getCategory();

        self::assertNotNull($category, 'Post should have a category');

        // Get French slugs
        $categorySlug = $story->getCategorySlug($category, 'fr');
        $postSlug = $story->getPostSlug($post, 'fr');

        // Request French post page
        $client->request('GET', \sprintf('/fr/articles/%s/%s', $categorySlug, $postSlug));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('[data-test-id="post-show-title"]', 'FR');

        // Verify cache was used
        $postCache = self::getContainer()->get(PostCache::class);
        $cachedPost = $postCache->getOne('fr', $postSlug);

        self::assertNotNull($cachedPost, 'Post should be in cache after warmup');
        self::assertStringContainsString('FR', $cachedPost->getTitle());
    }
}
