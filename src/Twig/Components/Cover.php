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
    private const int RESPONSIVE_BREAKPOINT_PX = 900;

    public ?MediaAttachment $attachment = null;

    public ?Media $media = null;

    public ?string $variation = null;

    public ?string $desktop = null;

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

    public function isResponsive(): bool
    {
        return $this->desktop !== null && $this->variation !== null && $this->isImage();
    }

    public function getDesktopMediaQuery(): string
    {
        return \sprintf('(min-width: %dpx)', self::RESPONSIVE_BREAKPOINT_PX);
    }

    public function getMobileMediaQuery(): string
    {
        return \sprintf('(max-width: %dpx)', self::RESPONSIVE_BREAKPOINT_PX - 1);
    }

    /**
     * Builds the descriptor=>variation map for a `<twig:joli:Source>` srcset prop.
     *
     * Joli generates `<base>-2x` automatically when `pixel_ratios: [1, 2]` is set
     * at the library level (cf. config/packages/joli_media.yaml).
     *
     * @return array<string, string>
     */
    public function getSrcset(string $variation): array
    {
        return [
            '1x' => $variation,
            '2x' => $variation . '-2x',
        ];
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
