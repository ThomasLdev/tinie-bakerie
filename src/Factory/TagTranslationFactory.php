<?php

namespace App\Factory;

use App\Entity\TagTranslation;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<TagTranslation>
 */
final class TagTranslationFactory extends PersistentProxyObjectFactory{
    public static function class(): string
    {
        return TagTranslation::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'title' => self::faker()->unique()->word(),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
