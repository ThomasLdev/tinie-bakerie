<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\Component\Mime\MimeTypes;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * Reusable component for displaying media thumbnails (images or videos).
 * Uses Symfony's MimeTypes to determine media type from file extension.
 */
#[AsTwigComponent]
final class MediaThumbnail
{
    public ?string $path = null;

    public string $alt = '';

    public string $title = '';

    public string $class = '';

    public string $variation = 'thumbnail-2x-webp';

    public function isImage(): bool
    {
        return str_starts_with($this->getMimeType(), 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->getMimeType(), 'video/');
    }

    public function getVideoMimeType(): string
    {
        $mimeType = $this->getMimeType();

        return str_starts_with($mimeType, 'video/') ? $mimeType : 'video/mp4';
    }

    private function getMimeType(): string
    {
        if ($this->path === null) {
            return '';
        }

        $extension = strtolower(pathinfo($this->path, \PATHINFO_EXTENSION));

        if ($extension === '') {
            return '';
        }

        $mimeTypes = MimeTypes::getDefault()->getMimeTypes($extension);

        return $mimeTypes[0] ?? '';
    }
}
