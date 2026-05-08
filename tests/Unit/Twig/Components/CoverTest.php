<?php

declare(strict_types=1);

namespace App\Tests\Unit\Twig\Components;

use App\Entity\Contracts\MediaAttachment;
use App\Twig\Components\Cover;
use JoliCode\MediaBundle\Model\Media;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Cover::class)]
final class CoverTest extends TestCase
{
    #[TestDox('postMount() keeps an explicit media even when an attachment is provided')]
    public function testPostMountKeepsExplicitMediaOverAttachment(): void
    {
        $explicitMedia = self::createStub(Media::class);
        $attachmentMedia = self::createStub(Media::class);

        $attachment = self::createStub(MediaAttachment::class);
        $attachment->method('getMedia')->willReturn($attachmentMedia);

        $component = new Cover();
        $component->media = $explicitMedia;
        $component->attachment = $attachment;

        $component->postMount();

        self::assertSame($explicitMedia, $component->media);
    }

    #[TestDox('postMount() resolves media from attachment when media is null')]
    public function testPostMountResolvesMediaFromAttachment(): void
    {
        $attachmentMedia = self::createStub(Media::class);

        $attachment = self::createStub(MediaAttachment::class);
        $attachment->method('getMedia')->willReturn($attachmentMedia);

        $component = new Cover();
        $component->media = null;
        $component->attachment = $attachment;

        $component->postMount();

        self::assertSame($attachmentMedia, $component->media);
    }

    #[TestDox('postMount() leaves explicit media untouched when no attachment is provided')]
    public function testPostMountLeavesMediaWhenNoAttachment(): void
    {
        $explicitMedia = self::createStub(Media::class);

        $component = new Cover();
        $component->media = $explicitMedia;
        $component->attachment = null;

        $component->postMount();

        self::assertSame($explicitMedia, $component->media);
    }

    #[TestDox('postMount() stays null without error when neither media nor attachment is provided')]
    public function testPostMountWithBothNullDoesNothing(): void
    {
        $component = new Cover();
        $component->media = null;
        $component->attachment = null;

        $component->postMount();

        self::assertNull($component->media);
    }

    #[TestDox('isImage() / isVideo() reflect the media file type')]
    #[DataProvider('provideFileTypes')]
    public function testFileTypeFlags(?string $fileType, bool $expectedIsImage, bool $expectedIsVideo): void
    {
        $component = new Cover();

        if ($fileType !== null) {
            $media = self::createStub(Media::class);
            $media->method('getFileType')->willReturn($fileType);
            $component->media = $media;
        }

        self::assertSame($expectedIsImage, $component->isImage());
        self::assertSame($expectedIsVideo, $component->isVideo());
    }

    public static function provideFileTypes(): \Generator
    {
        yield 'image is image and not video' => ['image', true, false];
        yield 'video is video and not image' => ['video', false, true];
        yield 'audio is neither image nor video' => ['audio', false, false];
        yield 'no media is neither image nor video' => [null, false, false];
    }

    #[TestDox('getResolvedAlt() prefers the explicit alt prop')]
    public function testGetResolvedAltPrefersExplicitProp(): void
    {
        $attachment = $this->createMock(MediaAttachment::class);
        $attachment->expects(self::never())->method('getAlt');

        $component = new Cover();
        $component->alt = 'Explicit alt';
        $component->attachment = $attachment;

        self::assertSame('Explicit alt', $component->getResolvedAlt());
    }

    #[TestDox('getResolvedAlt() falls back to the attachment alt when no prop is set')]
    public function testGetResolvedAltFallsBackToAttachment(): void
    {
        $attachment = self::createStub(MediaAttachment::class);
        $attachment->method('getAlt')->willReturn('Attachment alt');

        $component = new Cover();
        $component->alt = null;
        $component->attachment = $attachment;

        self::assertSame('Attachment alt', $component->getResolvedAlt());
    }

    #[TestDox('getResolvedAlt() returns an empty string when nothing is provided')]
    public function testGetResolvedAltReturnsEmptyStringByDefault(): void
    {
        $component = new Cover();
        $component->alt = null;
        $component->attachment = null;

        self::assertSame('', $component->getResolvedAlt());
    }

    #[TestDox('getResolvedTitle() prefers the explicit title prop')]
    public function testGetResolvedTitlePrefersExplicitProp(): void
    {
        $attachment = $this->createMock(MediaAttachment::class);
        $attachment->expects(self::never())->method('getTitle');

        $component = new Cover();
        $component->title = 'Explicit title';
        $component->attachment = $attachment;

        self::assertSame('Explicit title', $component->getResolvedTitle());
    }

    #[TestDox('getResolvedTitle() falls back to the attachment title when no prop is set')]
    public function testGetResolvedTitleFallsBackToAttachment(): void
    {
        $attachment = self::createStub(MediaAttachment::class);
        $attachment->method('getTitle')->willReturn('Attachment title');

        $component = new Cover();
        $component->title = null;
        $component->attachment = $attachment;

        self::assertSame('Attachment title', $component->getResolvedTitle());
    }

    #[TestDox('getResolvedTitle() returns an empty string when nothing is provided')]
    public function testGetResolvedTitleReturnsEmptyStringByDefault(): void
    {
        $component = new Cover();
        $component->title = null;
        $component->attachment = null;

        self::assertSame('', $component->getResolvedTitle());
    }
}
