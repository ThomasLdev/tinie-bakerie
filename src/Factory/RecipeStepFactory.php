<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\RecipeStep;
use App\Services\PostSection\Enum\PostSectionType;
use App\Services\Recipe\Enum\StepTipType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<RecipeStep>
 */
final class RecipeStepFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<RecipeStep>
     */
    public static function class(): string
    {
        return RecipeStep::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        $hasTip = self::faker()->boolean(33);

        return [
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'position' => 0,
            'type' => PostSectionType::Default,
            'tipType' => $hasTip ? self::faker()->randomElement(StepTipType::cases()) : null,
        ];
    }
}
