<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\TagTranslation;
use App\Factory\Contracts\LocaleAwareFactory;
use Faker\Generator;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<TagTranslation>
 */
final class TagTranslationFactory extends PersistentProxyObjectFactory implements LocaleAwareFactory
{
    /**
     * @return class-string<TagTranslation>
     */
    public static function class(): string
    {
        return TagTranslation::class;
    }

    public static function defaultsForLocale(Generator $faker): array
    {
        return [
            'title' => $faker->unique()->realText(15),
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
            'title' => self::faker()->unique()->word(),
        ];
    }
}
