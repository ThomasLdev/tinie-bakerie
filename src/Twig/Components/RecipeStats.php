<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Recipe;
use App\Twig\Extension\DurationExtension;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class RecipeStats
{
    public Recipe $recipe;

    public function __construct(
        private readonly DurationExtension $durationFormatter,
    ) {
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

    public function getDifficultyLabel(): string
    {
        return 'recipe.difficulty.' . $this->recipe->getDifficulty()->value;
    }
}
