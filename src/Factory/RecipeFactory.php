<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Recipe;
use App\Services\Post\Enum\Difficulty;
use Doctrine\Common\Collections\ArrayCollection;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Recipe>
 */
final class RecipeFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<Recipe>
     */
    public static function class(): string
    {
        return Recipe::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'active' => self::faker()->boolean(80),
            'tags' => [],
            'media' => [],
            'sections' => [],
            'ingredientGroups' => [],
            'translations' => new ArrayCollection(),
            'cookingTime' => self::faker()->numberBetween(5, 120),
            'preparationTime' => self::faker()->numberBetween(5, 60),
            'difficulty' => self::faker()->randomElement(Difficulty::cases()),
            'servings' => self::faker()->numberBetween(2, 8),
        ];
    }
}
