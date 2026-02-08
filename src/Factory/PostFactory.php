<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Post;
use App\Services\Post\Enum\Difficulty;
use Doctrine\Common\Collections\ArrayCollection;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Post>
 */
final class PostFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<Post>
     */
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
            'cookingTime' => self::faker()->numberBetween(5, 120),
            'difficulty' => Difficulty::Easy,
        ];
    }
}
