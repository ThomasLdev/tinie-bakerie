<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Ingredient;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Ingredient>
 */
final class IngredientFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<Ingredient>
     */
    public static function class(): string
    {
        return Ingredient::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'position' => 0,
            'baseQuantity' => self::faker()->boolean(80) ? self::faker()->randomFloat(1, 1, 500) : null,
        ];
    }
}
