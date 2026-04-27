<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\IngredientTranslation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<IngredientTranslation>
 */
final class IngredientTranslationFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<IngredientTranslation>
     */
    public static function class(): string
    {
        return IngredientTranslation::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'name' => self::faker()->word(),
            'unit' => self::faker()->randomElement(['g', 'ml', 'cl', 'cuillère à soupe', 'cuillère à café', 'pincée', '']),
            'quantityDisplay' => '',
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
