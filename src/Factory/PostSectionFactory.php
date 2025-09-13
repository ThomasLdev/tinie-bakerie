<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\PostSection;
use App\Services\PostSection\Enum\PostSectionType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostSection>
 */
final class PostSectionFactory extends PersistentProxyObjectFactory
{
    /**
     * @return class-string<PostSection>
     */
    public static function class(): string
    {
        return PostSection::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'position' => self::faker()->randomNumber(),
            'post' => null,
            'type' => self::faker()->randomElement(PostSectionType::cases()),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }
}
