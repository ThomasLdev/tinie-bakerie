<?php

namespace App\Factory;

use App\Entity\PostSectionMediaTranslation;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostSectionMediaTranslation>
 */
final class PostSectionMediaTranslationFactory extends PersistentProxyObjectFactory{
    public static function class(): string
    {
        return PostSectionMediaTranslation::class;
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
