<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\PostMedia;
use App\Entity\Recipe;
use App\Twig\Extension\DurationExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Hero
{
    public Recipe $recipe;

    public function __construct(
        private readonly DurationExtension $durationFormatter,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getCoverAttachment(): ?PostMedia
    {
        $first = $this->recipe->getMedia()->first();

        return $first instanceof PostMedia ? $first : null;
    }

    public function getPreparationTimeFormatted(): ?string
    {
        return $this->durationFormatter->formatDuration($this->recipe->getPreparationTime());
    }

    public function getCookingTimeFormatted(): ?string
    {
        return $this->durationFormatter->formatDuration($this->recipe->getCookingTime());
    }

    public function getTotalTimeFormatted(): ?string
    {
        return $this->durationFormatter->formatDuration($this->recipe->getTotalRecipeTime());
    }

    /**
     * @return list<string>
     */
    public function getTagTitles(): array
    {
        $titles = [];

        foreach ($this->recipe->getTags() as $tag) {
            $title = $tag->getTitle();

            if ($title !== '') {
                $titles[] = $title;
            }
        }

        return $titles;
    }

    public function getCtaHref(): string
    {
        return $this->urlGenerator->generate('app_recipe_show', [
            'categorySlug' => $this->recipe->getCategory()?->getSlug() ?? '',
            'recipeSlug' => $this->recipe->getSlug(),
        ]);
    }
}
