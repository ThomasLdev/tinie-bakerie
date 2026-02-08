<?php

declare(strict_types=1);

namespace App\Factory;

use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use App\Entity\CategoryMedia;

/**
 * @extends PersistentObjectFactory<CategoryMedia>
 */
final class CategoryMediaFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<CategoryMedia>
     */
    public static function class(): string
    {
        return CategoryMedia::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'position' => 0,
        ];
    }
}
