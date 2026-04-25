<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Post;
use App\Entity\PostMedia;
use App\Twig\Extension\DurationExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Hero
{
    /** @var list<string> */
    private const array IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'];

    /** @var list<string> */
    private const array VIDEO_EXTS = ['mp4', 'webm', 'ogg', 'mov'];

    /** @var array<string,string> */
    private const array VIDEO_MIME_TYPES = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'mov' => 'video/quicktime',
    ];

    public Post $post;

    public function __construct(
        private readonly DurationExtension $durationFormatter,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getCoverPath(): ?string
    {
        return $this->getCoverMedia()?->getMedia()?->getPath();
    }

    public function getCoverAlt(): string
    {
        $alt = $this->getCoverMedia()?->getCurrentTranslation()?->getAlt();

        return $alt !== null && $alt !== '' ? $alt : $this->post->getTitle();
    }

    public function getCoverIsImage(): bool
    {
        return in_array($this->getCoverExtension(), self::IMAGE_EXTS, true);
    }

    public function getCoverIsVideo(): bool
    {
        return in_array($this->getCoverExtension(), self::VIDEO_EXTS, true);
    }

    public function getCoverMimeType(): ?string
    {
        $ext = $this->getCoverExtension();

        return $ext !== null ? (self::VIDEO_MIME_TYPES[$ext] ?? null) : null;
    }

    public function getCookingTimeFormatted(): ?string
    {
        return $this->durationFormatter->formatDuration($this->post->getCookingTime());
    }

    /**
     * @return list<string>
     */
    public function getTagTitles(): array
    {
        $titles = [];
        foreach ($this->post->getTags() as $tag) {
            $title = $tag->getTitle();
            if ($title !== '') {
                $titles[] = $title;
            }
        }

        return $titles;
    }

    public function getCtaHref(): string
    {
        return $this->urlGenerator->generate('app_post_show', [
            'categorySlug' => $this->post->getCategory()?->getSlug() ?? '',
            'postSlug' => $this->post->getSlug(),
        ]);
    }

    private function getCoverMedia(): ?PostMedia
    {
        $first = $this->post->getMedia()->first();

        return $first instanceof PostMedia ? $first : null;
    }

    private function getCoverExtension(): ?string
    {
        $path = $this->getCoverPath();
        if ($path === null) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return $ext !== '' ? $ext : null;
    }
}
