<?php

namespace App\Factory;

use App\Entity\PostSectionMedia;
use App\Services\Media\Enum\MediaType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostSectionMedia>
 */
final class PostSectionMediaFactory extends PersistentProxyObjectFactory
{
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
            'type' => self::faker()->randomElement(MediaType::cases()),
            'updatedAt' => self::faker()->dateTime(),
            'position' => 0,
        ];
    }
}
