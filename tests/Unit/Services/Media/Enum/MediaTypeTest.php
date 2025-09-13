<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\Media\Enum;

use App\Services\Media\Enum\MediaType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(MediaType::class)]
final class MediaTypeTest extends MockeryTestCase
{
    public static function getValidExtensionData(): array
    {
        return [
            'jpg should be an image type' => ['jpg', 'image'],
            'jpeg should be an image type' => ['jpeg', 'image'],
            'png should be an image type' => ['png', 'image'],
            'gif should be an image type' => ['gif', 'image'],
            'mp4 should be an video type' => ['mp4', 'video'],
            'webm should be an video type' => ['webm', 'video'],
        ];
    }

    #[DataProvider('getValidExtensionData')]
    public function testFromExtension(string $extension, string $expectedType): void
    {
        self::assertSame($expectedType, MediaType::fromExtension($extension)->value);
    }
}
