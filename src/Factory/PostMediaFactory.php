<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\PostMedia;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<PostMedia>
 */
final class PostMediaFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<PostMedia>
     */
    public static function class(): string
    {
        return PostMedia::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'translations' => [],
            'position' => 0,
        ];
    }
}
