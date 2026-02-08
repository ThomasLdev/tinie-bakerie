<?php

declare(strict_types=1);

namespace App\Factory;

use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use App\Entity\PostMediaTranslation;
use App\Entity\PostTranslation;

/**
 * @extends PersistentObjectFactory<PostTranslation>
 */
final class PostMediaTranslationFactory extends PersistentObjectFactory
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
