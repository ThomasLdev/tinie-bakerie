<?php

declare(strict_types=1);

namespace App\Factory;

use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use App\Entity\CategoryMediaTranslation;

/**
 * @extends PersistentObjectFactory<CategoryMediaTranslation>
 */
final class CategoryMediaTranslationFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<CategoryMediaTranslation>
     */
    public static function class(): string
    {
        return CategoryMediaTranslation::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'alt' => self::faker()->text(),
            'createdAt' => self::faker()->dateTime(),
            'title' => self::faker()->text(),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
