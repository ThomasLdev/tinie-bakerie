<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\PostMedia;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostMedia>
 */
final class PostMediaFactory extends PersistentProxyObjectFactory
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
            'mediaPath' => '',
            'updatedAt' => self::faker()->dateTime(),
            'translations' => [],
            'position' => 0,
        ];
    }
}
