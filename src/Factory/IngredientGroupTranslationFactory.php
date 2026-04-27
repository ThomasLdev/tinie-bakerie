<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\IngredientGroupTranslation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<IngredientGroupTranslation>
 */
final class IngredientGroupTranslationFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<IngredientGroupTranslation>
     */
    public static function class(): string
    {
        return IngredientGroupTranslation::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'label' => self::faker()->sentence(3),
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
