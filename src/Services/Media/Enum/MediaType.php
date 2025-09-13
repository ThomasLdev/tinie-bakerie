<?php

declare(strict_types=1);

namespace App\Services\Media\Enum;

enum MediaType: string
{
    case Image = 'image';
    case Video = 'video';

    public static function fromExtension(string $extension): self
    {
        return match (strtolower($extension)) {
            'jpg', 'jpeg', 'png', 'gif', 'webp' => self::Image,
            'mp4', 'webm' => self::Video,
            default => throw new \InvalidArgumentException("Unsupported media type: {$extension}"),
        };
    }
}
