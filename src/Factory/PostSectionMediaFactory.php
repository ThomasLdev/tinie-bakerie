<?php

declare(strict_types=1);

namespace App\Factory;

use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use App\Entity\PostSectionMedia;

/**
 * @extends PersistentObjectFactory<PostSectionMedia>
 */
final class PostSectionMediaFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<PostSectionMedia>
     */
    public static function class(): string
    {
        return PostSectionMedia::class;
    }

    /**
     * @return array<string, mixed>
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
