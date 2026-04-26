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
    public Post $post;

    public function __construct(
        private readonly DurationExtension $durationFormatter,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getCoverAttachment(): ?PostMedia
    {
        $first = $this->post->getMedia()->first();

        return $first instanceof PostMedia ? $first : null;
    }

    public function getPreparationTimeFormatted(): ?string
    {
        return $this->durationFormatter->formatDuration($this->post->getPreparationTime());
    }

    public function getCookingTimeFormatted(): ?string
    {
        return $this->durationFormatter->formatDuration($this->post->getCookingTime());
    }

    public function getTotalTimeFormatted(): ?string
    {
        return $this->durationFormatter->formatDuration(
            $this->post->getPreparationTime() + $this->post->getCookingTime(),
        );
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
}
