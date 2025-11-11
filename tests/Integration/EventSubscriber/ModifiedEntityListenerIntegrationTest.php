<?php

declare(strict_types=1);

namespace App\Tests\Integration\EventSubscriber;

use App\EventSubscriber\Admin\ModifiedEntityListener;
use App\Factory\TagFactory;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Integration test for ModifiedEntityListener to ensure it works correctly
 * with all cache types including TagCache (which implements InvalidatableEntityCacheInterface).
 *
 * @internal
 */
#[CoversClass(ModifiedEntityListener::class)]
final class ModifiedEntityListenerIntegrationTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
    }

    public function testModifiedEntityListenerHandlesTagUpdatesWithoutTypeError(): void
    {
        // Create a Tag entity
        $tag = TagFactory::createOne()->_real();

        // Create the event that would be dispatched by EasyAdmin
        $event = new AfterEntityUpdatedEvent($tag);

        // Dispatch the event - this should trigger ModifiedEntityListener::invalidateCacheOnUpdate()
        // If there's a type error (TagCache not matching EntityCacheInterface), this will fail
        $this->eventDispatcher->dispatch($event, AfterEntityUpdatedEvent::class);

        self::assertTrue(true);
    }
}
