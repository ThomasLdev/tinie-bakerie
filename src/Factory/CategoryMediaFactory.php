<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\CategoryMedia;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<CategoryMedia>
 */
final class CategoryMediaFactory extends PersistentProxyObjectFactory
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
