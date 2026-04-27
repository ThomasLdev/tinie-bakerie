<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Recipe;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Ingredients
{
    public Recipe $recipe;

    public function getTotalCount(): int
    {
        return $this->recipe->getIngredientCount();
    }

    public function getBaseServings(): int
    {
        return $this->recipe->getServings();
    }
}
