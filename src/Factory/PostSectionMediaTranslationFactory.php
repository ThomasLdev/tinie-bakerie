<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\PostSectionMediaTranslation;
use App\Factory\Contracts\LocaleAwareFactory;
use Faker\Generator;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostSectionMediaTranslation>
 */
final class PostSectionMediaTranslationFactory extends PersistentProxyObjectFactory implements LocaleAwareFactory
{
    /**
     * @return class-string<PostSectionMediaTranslation>
     */
    public static function class(): string
    {
        return PostSectionMediaTranslation::class;
    }

    public static function defaultsForLocale(Generator $faker): array
    {
        return [
            'alt' => $faker->realText(20),
            'title' => $faker->realText(15),
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
            'alt' => self::faker()->text(),
            'title' => self::faker()->text(),
        ];
    }
}
