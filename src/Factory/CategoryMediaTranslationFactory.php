<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\CategoryMediaTranslation;
use App\Factory\Contracts\LocaleAwareFactory;
use Faker\Generator;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<CategoryMediaTranslation>
 */
final class CategoryMediaTranslationFactory extends PersistentProxyObjectFactory implements LocaleAwareFactory
{
    /**
     * @return class-string<CategoryMediaTranslation>
     */
    public static function class(): string
    {
        return CategoryMediaTranslation::class;
    }

    public static function defaultsForLocale(Generator $faker): array
    {
        return [
            'alt' => $faker->realText(15),
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
