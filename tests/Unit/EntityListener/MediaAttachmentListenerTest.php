<?php

declare(strict_types=1);

namespace App\Tests\Unit\EntityListener;

use App\Entity\CategoryMedia;
use App\Entity\Contracts\MediaAttachment;
use App\Entity\PostMedia;
use App\Entity\PostSectionMedia;
use App\EntityListener\MediaAttachmentListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use JoliCode\MediaBundle\Model\Media;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MediaAttachmentListener.
 *
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
#[CoversClass(MediaAttachmentListener::class)]
final class MediaAttachmentListenerTest extends TestCase
{
    private MediaAttachmentListener $listener;

    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->listener = new MediaAttachmentListener();
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    #[TestDox('Deletes stored media when $entityClass is removed')]
    #[DataProvider('provideMediaAttachmentClasses')]
    public function testDeletesStoredMediaOnRemove(string $entityClass): void
    {
        $media = $this->createMock(Media::class);
        $media->expects(self::once())->method('isStored')->willReturn(true);
        $media->expects(self::once())->method('delete');

        /** @var MediaAttachment&MockObject $entity */
        $entity = $this->createMock($entityClass);
        $entity->method('getMedia')->willReturn($media);

        $event = new PreRemoveEventArgs($entity, $this->entityManager);

        $this->listener->preRemove($entity, $event);
    }

    public static function provideMediaAttachmentClasses(): \Generator
    {
        yield 'PostMedia' => [PostMedia::class];
        yield 'CategoryMedia' => [CategoryMedia::class];
        yield 'PostSectionMedia' => [PostSectionMedia::class];
    }

    #[TestDox('Does not delete media when media is not stored')]
    public function testDoesNotDeleteWhenMediaNotStored(): void
    {
        $media = $this->createMock(Media::class);
        $media->expects(self::once())->method('isStored')->willReturn(false);
        $media->expects(self::never())->method('delete');

        $entity = $this->createMock(PostMedia::class);
        $entity->method('getMedia')->willReturn($media);

        $event = new PreRemoveEventArgs($entity, $this->entityManager);

        $this->listener->preRemove($entity, $event);
    }

    #[TestDox('Does nothing when entity has no media')]
    public function testDoesNothingWhenNoMedia(): void
    {
        $entity = $this->createMock(PostMedia::class);
        $entity->method('getMedia')->willReturn(null);

        $event = new PreRemoveEventArgs($entity, $this->entityManager);

        // Should not throw any exception
        $this->listener->preRemove($entity, $event);

        // If we reach here without exception, the test passes
        self::assertTrue(true);
    }
}
