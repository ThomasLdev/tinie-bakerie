<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Extension;

use App\Entity\Contracts\Positionable;
use App\Form\Extension\PositionOrderedCollectionExtension;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * @internal
 */
#[CoversClass(PositionOrderedCollectionExtension::class)]
#[AllowMockObjectsWithoutExpectations]
final class PositionOrderedCollectionExtensionTest extends TestCase
{
    private PositionOrderedCollectionExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new PositionOrderedCollectionExtension();
    }

    #[Test]
    public function itExtendsCollectionType(): void
    {
        self::assertSame([CollectionType::class], iterator_to_array($this->yieldExtendedTypes()));
    }

    #[Test]
    public function itDoesNotAttachAnyListenerWhenByReferenceIsTrue(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::never())->method('addEventListener');

        $this->extension->buildForm($builder, ['by_reference' => true]);
    }

    #[Test]
    public function itDefaultsToByReferenceWhenOptionIsMissing(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::never())->method('addEventListener');

        $this->extension->buildForm($builder, []);
    }

    #[Test]
    public function itAttachesPreSetDataListenerWhenByReferenceIsFalse(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())
            ->method('addEventListener')
            ->with(FormEvents::PRE_SET_DATA, self::isCallable());

        $this->extension->buildForm($builder, ['by_reference' => false]);
    }

    #[Test]
    public function itDoesNothingWhenEventDataIsNull(): void
    {
        $listener = $this->captureSortListener();
        $event = $this->createEvent(null);

        $event->expects(self::never())->method('setData');

        $listener($event);
    }

    #[Test]
    public function itDoesNothingWhenArrayIsEmpty(): void
    {
        $listener = $this->captureSortListener();
        $event = $this->createEvent([]);

        $event->expects(self::never())->method('setData');

        $listener($event);
    }

    #[Test]
    public function itDoesNothingWhenCollectionIsEmpty(): void
    {
        $listener = $this->captureSortListener();
        $event = $this->createEvent(new ArrayCollection());

        $event->expects(self::never())->method('setData');

        $listener($event);
    }

    #[Test]
    public function itLeavesDataUntouchedWhenAnyItemIsNotPositionable(): void
    {
        $listener = $this->captureSortListener();
        $event = $this->createEvent([
            $this->positionable(2),
            new \stdClass(),
            $this->positionable(1),
        ]);

        $event->expects(self::never())->method('setData');

        $listener($event);
    }

    #[Test]
    public function itLeavesDataUntouchedWhenScalarIsPassed(): void
    {
        $listener = $this->captureSortListener();
        $event = $this->createEvent('not-a-collection');

        $event->expects(self::never())->method('setData');

        $listener($event);
    }

    #[Test]
    public function itSortsArrayOfPositionablesAscendingByPosition(): void
    {
        $listener = $this->captureSortListener();

        $first = $this->positionable(0);
        $second = $this->positionable(1);
        $third = $this->positionable(5);
        $event = $this->createEvent([$third, $first, $second]);

        $event->expects(self::once())
            ->method('setData')
            ->with([$first, $second, $third]);

        $listener($event);
    }

    #[Test]
    public function itSortsCollectionAndReturnsAnArrayCollection(): void
    {
        $listener = $this->captureSortListener();

        $a = $this->positionable(3);
        $b = $this->positionable(1);
        $c = $this->positionable(2);

        $event = $this->createEvent(new ArrayCollection([$a, $b, $c]));

        $event->expects(self::once())
            ->method('setData')
            ->with(self::callback(static function (mixed $value) use ($a, $b, $c): bool {
                return $value instanceof ArrayCollection
                    && [$b, $c, $a] === array_values($value->toArray());
            }));

        $listener($event);
    }

    #[Test]
    public function itHandlesNegativePositions(): void
    {
        $listener = $this->captureSortListener();

        $negative = $this->positionable(-3);
        $zero = $this->positionable(0);
        $positive = $this->positionable(2);
        $event = $this->createEvent([$positive, $negative, $zero]);

        $event->expects(self::once())
            ->method('setData')
            ->with([$negative, $zero, $positive]);

        $listener($event);
    }

    #[Test]
    public function itPreservesAlreadySortedInput(): void
    {
        $listener = $this->captureSortListener();

        $a = $this->positionable(0);
        $b = $this->positionable(1);
        $c = $this->positionable(2);
        $event = $this->createEvent([$a, $b, $c]);

        $event->expects(self::once())
            ->method('setData')
            ->with([$a, $b, $c]);

        $listener($event);
    }

    /**
     * @return \Generator<int, class-string>
     */
    private function yieldExtendedTypes(): \Generator
    {
        yield from PositionOrderedCollectionExtension::getExtendedTypes();
    }

    /**
     * Triggers buildForm with by_reference=false and returns the captured listener.
     */
    private function captureSortListener(): callable
    {
        $captured = null;

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('addEventListener')
            ->willReturnCallback(static function (string $event, callable $listener) use (&$captured, $builder): FormBuilderInterface {
                if (FormEvents::PRE_SET_DATA === $event) {
                    $captured = $listener;
                }

                return $builder;
            });

        $this->extension->buildForm($builder, ['by_reference' => false]);

        self::assertIsCallable($captured, 'Listener should have been captured');

        return $captured;
    }

    /**
     * @return FormEvent&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createEvent(mixed $data): FormEvent
    {
        $form = $this->createStub(FormInterface::class);
        $event = $this->createMock(FormEvent::class);
        $event->method('getData')->willReturn($data);

        return $event;
    }

    private function positionable(int $position): Positionable
    {
        return new class($position) implements Positionable {
            public function __construct(private readonly int $position)
            {
            }

            public function getPosition(): int
            {
                return $this->position;
            }
        };
    }
}
