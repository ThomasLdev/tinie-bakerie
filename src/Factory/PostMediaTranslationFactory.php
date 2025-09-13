<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\PostMediaTranslation;
use App\Entity\PostTranslation;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostTranslation>
 */
final class PostMediaTranslationFactory extends PersistentProxyObjectFactory
{
    /**
     * @return class-string<PostMediaTranslation>
     */
    public static function class(): string
    {
        return PostMediaTranslation::class;
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
