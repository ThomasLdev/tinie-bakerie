<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;
use Zenstruck\Foundry\Story;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @internal
 */
abstract class BaseControllerTestCase extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    protected KernelBrowser $client;

    protected Container $container;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = self::createClient(
            options: [
                'environment' => 'test',
                'debug' => false,
            ],
            server: [
                'HTTP_HOST' => 'local.tinie-bakerie.com',
                'HTTPS' => 'on',
            ],
        );

        $this->container = self::getContainer();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * Load a Foundry Story and clear the EntityManager identity map.
     * This ensures subsequent queries return fresh entities respecting filters.
     *
     * @template T
     * @param callable(): T $storyLoader
     * @return Story
     */
    protected function loadStory(callable $storyLoader): Story
    {
        $result = $storyLoader();

        // Clear identity map so queries return fresh entities
        // This is necessary because Foundry persists entities with all translations,
        // but we want queries to hydrate fresh entities respecting the locale filter
        $this->entityManager->clear();

        return $result;
    }
}
