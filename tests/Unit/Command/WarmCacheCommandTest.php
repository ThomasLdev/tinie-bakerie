<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\WarmCacheCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(WarmCacheCommand::class)]
final class WarmCacheCommandTest extends KernelTestCase
{
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
}
