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
        self::assertStringContainsString('Posts:', $output);
        self::assertStringContainsString('Categories:', $output);
        self::assertStringContainsString('Headers:', $output);
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

    public function testExecuteWithEntityFilterPost(): void
    {
        $this->commandTester->execute(['--entity' => 'post']);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Posts:', $output);
        self::assertStringContainsString('entities cached', $output);

        // Verify only posts were warmed (stats show 0 for others)
        self::assertMatchesRegularExpression('/Categories:\s+0\s+entities/', $output);
        self::assertMatchesRegularExpression('/Headers:\s+0\s+entities/', $output);
    }

    public function testExecuteWithEntityFilterCategory(): void
    {
        $this->commandTester->execute(['--entity' => 'category']);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Categories:', $output);
        self::assertStringContainsString('entities cached', $output);

        // Verify only categories were warmed
        self::assertMatchesRegularExpression('/Posts:\s+0\s+entities/', $output);
        self::assertMatchesRegularExpression('/Headers:\s+0\s+entities/', $output);
    }

    public function testExecuteWithEntityFilterHeader(): void
    {
        $this->commandTester->execute(['--entity' => 'header']);

        $this->commandTester->assertCommandIsSuccessful();

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Headers:', $output);
        self::assertStringContainsString('entities cached', $output);

        // Verify only headers were warmed
        self::assertMatchesRegularExpression('/Posts:\s+0\s+entities/', $output);
        self::assertMatchesRegularExpression('/Categories:\s+0\s+entities/', $output);
    }

    public function testExecuteWithInvalidEntityFilter(): void
    {
        $this->commandTester->execute(['--entity' => 'invalid']);

        self::assertSame(1, $this->commandTester->getStatusCode());

        $output = $this->commandTester->getDisplay();
        self::assertStringContainsString('Invalid entity type "invalid"', $output);
        self::assertStringContainsString('Valid options: post, category, header', $output);
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
