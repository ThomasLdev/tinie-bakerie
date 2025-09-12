<?php

namespace App\Factory;

use App\Entity\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Post>
 */
final class PostFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Post::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'active' => self::faker()->boolean(80),
            'tags' => [],
            'media' => [],
            'sections' => [],
            'translations' => new ArrayCollection(),
        ];
    }
}
