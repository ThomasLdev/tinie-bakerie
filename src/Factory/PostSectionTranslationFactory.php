<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\PostSectionTranslation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<PostSectionTranslation>
 */
final class PostSectionTranslationFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<PostSectionTranslation>
     */
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
            'title' => self::faker()->text(10),
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
