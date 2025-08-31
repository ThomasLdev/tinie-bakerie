<?php

namespace App\Factory;

use App\Entity\PostSectionTranslation;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostSectionTranslation>
 */
final class PostSectionTranslationFactory extends PersistentProxyObjectFactory{
    public static function class(): string
    {
        return PostSectionTranslation::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'content' => self::faker()->text(),
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
