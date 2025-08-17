<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services\Media\Enum;

use App\Services\Media\Enum\MediaType;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(MediaType::class)]
class MediaTypeTest extends MockeryTestCase
{
    public static function getFromExtensionData(): array
    {
        return [
            'jpg should be an image type' => ['jpg', 'image', true],
            'jpeg should be an image type' => ['jpeg', 'image', true],
            'png should be an image type' => ['png', 'image', true],
            'gif should be an image type' => ['gif', 'image', true],
            'mp4 should be an video type' => ['mp4', 'video', true],
            'webm should be an video type' => ['webm', 'video', false],
        ];
    }

    #[DataProvider('getFromExtensionData')]
    public function testFromExtension(string $extension, string $expectedType, bool $isSupported): void
    {
        if (!$isSupported) {
            $this->expectException(InvalidArgumentException::class);
        }

        $result = MediaType::fromExtension($extension)->value;

        if ($isSupported) {
            $this->assertSame($expectedType, $result);
        }
    }
}
