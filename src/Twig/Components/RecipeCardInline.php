<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\PostMedia;
use App\Entity\Recipe;
use App\Twig\Extension\DurationExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class RecipeCardInline
{
    public Recipe $recipe;

    public ?string $kicker = null;

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

    public function getTotalTimeFormatted(): ?string
    {
        return $this->durationFormatter->formatDuration($this->recipe->getTotalRecipeTime());
    }

    public function getHref(): string
    {
        return $this->urlGenerator->generate('app_recipe_show', [
            'categorySlug' => $this->recipe->getCategory()?->getSlug() ?? '',
            'recipeSlug' => $this->recipe->getSlug(),
        ]);
    }
}
