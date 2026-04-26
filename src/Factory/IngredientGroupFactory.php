<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\IngredientGroup;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<IngredientGroup>
 */
final class IngredientGroupFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<IngredientGroup>
     */
    public static function class(): string
    {
        return IngredientGroup::class;
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
        ];
    }
}
