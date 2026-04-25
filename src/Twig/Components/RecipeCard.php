<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Post;
use App\Entity\PostMedia;
use App\Twig\Extension\DurationExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class RecipeCard
{
    public Post $post;

    public bool $featured = false;

    public ?string $badge = null;

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

    public function getDurationFormatted(): ?string
    {
        return $this->durationFormatter->formatDuration(
            $this->post->getPreparationTime() + $this->post->getCookingTime(),
        );
    }

    public function getHref(): string
    {
        return $this->urlGenerator->generate('app_post_show', [
            'categorySlug' => $this->post->getCategory()?->getSlug() ?? '',
            'postSlug' => $this->post->getSlug(),
        ]);
    }
}
