<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\RecipeStepTranslation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<RecipeStepTranslation>
 */
final class RecipeStepTranslationFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<RecipeStepTranslation>
     */
    public static function class(): string
    {
        return RecipeStepTranslation::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'content' => self::faker()->paragraph(3),
            'title' => self::faker()->sentence(4),
            'tipText' => self::faker()->boolean(33) ? self::faker()->sentence(8) : '',
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
