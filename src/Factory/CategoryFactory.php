<?php

declare(strict_types=1);

namespace App\Factory;

use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use App\Entity\Category;

/**
 * @extends PersistentObjectFactory<Category>
 */
final class CategoryFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<Category>
     */
    public static function class(): string
    {
        return Category::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
