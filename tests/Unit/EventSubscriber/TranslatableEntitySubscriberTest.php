<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\Entity\Contracts\Translatable;
use App\EventSubscriber\TranslatableEntitySubscriber;
use App\Services\Locale\LocaleProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostLoadEventArgs;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TranslatableEntitySubscriber::class)]
#[AllowMockObjectsWithoutExpectations]
final class TranslatableEntitySubscriberTest extends TestCase
{
    private TranslatableEntitySubscriber $subscriber;

    private LocaleProvider&MockObject $localeProvider;

    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->localeProvider = $this->createMock(LocaleProvider::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->subscriber = new TranslatableEntitySubscriber($this->localeProvider);
    }

    #[TestDox('Indexes translations and sets locale for Translatable entities')]
    public function testIndexesTranslationsAndSetsLocaleForTranslatableEntities(): void
    {
        $entity = $this->createMock(Translatable::class);
        $entity->expects(self::once())->method('indexTranslations');
        $entity->expects(self::once())->method('setCurrentLocale')->with('fr');

        $this->localeProvider->method('getCurrentLocale')->willReturn('fr');

        $event = new PostLoadEventArgs($entity, $this->entityManager);

        $this->subscriber->postLoad($event);
    }

    #[TestDox('Ignores non-Translatable entities')]
    public function testIgnoresNonTranslatableEntities(): void
    {
        $entity = new \stdClass();

        $this->localeProvider->expects(self::never())->method('getCurrentLocale');

        $event = new PostLoadEventArgs($entity, $this->entityManager);

        $this->subscriber->postLoad($event);
    }

    #[TestDox('Uses locale "$locale" from LocaleProvider')]
    #[DataProvider('provideLocales')]
    public function testUsesCorrectLocaleFromLocaleProvider(string $locale): void
    {
        $entity = $this->createMock(Translatable::class);
        $entity->expects(self::once())->method('setCurrentLocale')->with($locale);

        $this->localeProvider->method('getCurrentLocale')->willReturn($locale);

        $event = new PostLoadEventArgs($entity, $this->entityManager);

        $this->subscriber->postLoad($event);
    }

    public static function provideLocales(): \Generator
    {
        yield 'French' => ['fr'];
        yield 'English' => ['en'];
        yield 'German' => ['de'];
    }
}
