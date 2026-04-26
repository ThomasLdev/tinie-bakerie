<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Contracts\MediaAttachment;
use JoliCode\MediaBundle\Model\Media;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsTwigComponent]
final class Cover
{
    public ?MediaAttachment $attachment = null;

    public ?Media $media = null;

    public ?string $variation = null;

    public ?string $alt = null;

    public ?string $title = null;

    public bool $eager = false;

    public bool $autoplay = false;

    #[PostMount]
    public function postMount(): void
    {
        if (!$this->media instanceof Media && $this->attachment instanceof MediaAttachment) {
            $this->media = $this->attachment->getMedia();
        }
    }

    public function isImage(): bool
    {
        return $this->media?->getFileType() === 'image';
    }

    public function isVideo(): bool
    {
        return $this->media?->getFileType() === 'video';
    }

    public function getResolvedAlt(): string
    {
        if ($this->alt !== null) {
            return $this->alt;
        }

        return $this->attachment?->getAlt() ?? '';
    }

    public function getResolvedTitle(): string
    {
        if ($this->title !== null) {
            return $this->title;
        }

        return $this->attachment?->getTitle() ?? '';
    }
}
