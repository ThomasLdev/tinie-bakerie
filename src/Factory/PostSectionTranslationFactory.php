<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\PostSectionTranslation;
use App\Factory\Contracts\LocaleAwareFactory;
use Faker\Generator;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostSectionTranslation>
 */
final class PostSectionTranslationFactory extends PersistentProxyObjectFactory implements LocaleAwareFactory
{
    /**
     * @return class-string<PostSectionTranslation>
     */
    public static function class(): string
    {
        return PostSectionTranslation::class;
    }

    public static function defaultsForLocale(Generator $faker): array
    {
        return [
            'title' => $faker->realText(15),
            'content' => $faker->realText(50),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'content' => self::faker()->text(),
            'title' => self::faker()->text(10),
        ];
    }
}
