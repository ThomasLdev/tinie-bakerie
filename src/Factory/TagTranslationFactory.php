<?php

declare(strict_types=1);

namespace App\Factory;

use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use App\Entity\TagTranslation;

/**
 * @extends PersistentObjectFactory<TagTranslation>
 */
final class TagTranslationFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<TagTranslation>
     */
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
