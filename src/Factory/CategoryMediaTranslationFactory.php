<?php

namespace App\Factory;

use App\Entity\CategoryMediaTranslation;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<CategoryMediaTranslation>
 */
final class CategoryMediaTranslationFactory extends PersistentProxyObjectFactory{
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
