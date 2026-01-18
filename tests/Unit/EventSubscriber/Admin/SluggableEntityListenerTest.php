<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber\Admin;

use App\Entity\Contracts\Sluggable;
use App\Entity\Contracts\Translatable;
use App\Entity\Contracts\Translation;
use App\EventSubscriber\Admin\SluggableEntityListener;
use App\Services\Slug\Slugger;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SluggableEntityListener::class)]
#[AllowMockObjectsWithoutExpectations]
final class SluggableEntityListenerTest extends TestCase
{
    private SluggableEntityListener $listener;

    private Slugger&MockObject $slugger;

    protected function setUp(): void
    {
        $this->slugger = $this->createMock(Slugger::class);
        $this->listener = new SluggableEntityListener($this->slugger);
    }

    #[TestDox('Sets slug on Sluggable translations when entity is persisted')]
    public function testSetsSlugOnSluggablesWhenEntityIsPersisted(): void
    {
        $translation = $this->createMock(Sluggable::class);
        $translation->method('getTitle')->willReturn('Hello World');
        $translation->expects(self::once())->method('setSlug')->with('hello-world');

        $entity = $this->createMock(Translatable::class);
        $entity->method('getTranslations')->willReturn(new ArrayCollection([$translation]));

        $this->slugger->method('slugify')->with('Hello World')->willReturn('hello-world');

        $event = new BeforeEntityPersistedEvent($entity);

        $this->listener->setTranslationSlugOnCreate($event);
    }

    #[TestDox('Sets slug on Sluggable translations when entity is updated')]
    public function testSetsSlugOnSluggablesWhenEntityIsUpdated(): void
    {
        $translation = $this->createMock(Sluggable::class);
        $translation->method('getTitle')->willReturn('Test Post');
        $translation->expects(self::once())->method('setSlug')->with('test-post');

        $entity = $this->createMock(Translatable::class);
        $entity->method('getTranslations')->willReturn(new ArrayCollection([$translation]));

        $this->slugger->method('slugify')->with('Test Post')->willReturn('test-post');

        $event = new BeforeEntityUpdatedEvent($entity);

        $this->listener->setTranslationSlugOnUpdate($event);
    }

    #[TestDox('Ignores non-Translatable entities')]
    public function testIgnoresNonTranslatableEntities(): void
    {
        $entity = new \stdClass();

        $this->slugger->expects(self::never())->method('slugify');

        $event = new BeforeEntityPersistedEvent($entity);

        $this->listener->setTranslationSlugOnCreate($event);
    }

    #[TestDox('Ignores non-Sluggable translations')]
    public function testIgnoresNonSluggables(): void
    {
        $translation = $this->createMock(Translation::class);

        $entity = $this->createMock(Translatable::class);
        $entity->method('getTranslations')->willReturn(new ArrayCollection([$translation]));

        $this->slugger->expects(self::never())->method('slugify');

        $event = new BeforeEntityPersistedEvent($entity);

        $this->listener->setTranslationSlugOnCreate($event);
    }

    #[TestDox('Uses correct slugified title "$title" from Slugger')]
    #[DataProvider('provideTitles')]
    public function testUsesCorrectSlugifiedTitleFromSlugger(string $title, string $expectedSlug): void
    {
        $translation = $this->createMock(Sluggable::class);
        $translation->method('getTitle')->willReturn($title);
        $translation->expects(self::once())->method('setSlug')->with($expectedSlug);

        $entity = $this->createMock(Translatable::class);
        $entity->method('getTranslations')->willReturn(new ArrayCollection([$translation]));

        $this->slugger->expects(self::once())->method('slugify')->with($title)->willReturn($expectedSlug);

        $event = new BeforeEntityPersistedEvent($entity);

        $this->listener->setTranslationSlugOnCreate($event);
    }

    public static function provideTitles(): \Generator
    {
        yield 'Hello World' => ['Hello World', 'hello-world'];
        yield 'Test Post' => ['Test Post', 'test-post'];
        yield 'Café Résumé' => ['Café Résumé', 'cafe-resume'];
    }
}
