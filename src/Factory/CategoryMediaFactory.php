<?php

namespace App\Factory;

use App\Entity\CategoryMedia;
use App\Services\Media\Enum\MediaType;
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
            'mediaName' => '',
            'updatedAt' => self::faker()->dateTime(),
            'type' => MediaType::Image,
            'mediaFile' => null,
            'position' => 0,
        ];
    }
}
