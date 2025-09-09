<?php

namespace App\Factory;

use App\Entity\PostMedia;
use App\Services\Media\Enum\MediaType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostMedia>
 */
final class PostMediaFactory extends PersistentProxyObjectFactory
{
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
            'mediaName' => '',
            'updatedAt' => self::faker()->dateTime(),
            'type' => MediaType::Image,
            'mediaFile' => null,
            'translations' => [],
            'position' => 0,
        ];
    }
}
