<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\CategoryTranslation;
use App\Factory\Contracts\LocaleAwareFactory;
use App\Services\Slug\Slugger;
use Faker\Generator;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<CategoryTranslation>
 */
final class CategoryTranslationFactory extends PersistentProxyObjectFactory implements LocaleAwareFactory
{
    public function __construct(
        private readonly Slugger $slugger,
    ) {
        parent::__construct();
    }

    /**
     * @return class-string<CategoryTranslation>
     */
    public static function class(): string
    {
        return CategoryTranslation::class;
    }

    public static function defaultsForLocale(Generator $faker): array
    {
        return [
            'title' => $faker->unique()->realText(15),
            'excerpt' => $faker->realText(20),
            'description' => $faker->realText(50),
            'metaTitle' => $faker->realText(50),
            'metaDescription' => $faker->realText(50),
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
            'description' => self::faker()->text(),
            'excerpt' => self::faker()->text(20),
            'metaDescription' => self::faker()->text(60),
            'metaTitle' => self::faker()->text(60),
            'title' => self::faker()->text(25),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (CategoryTranslation $categoryTranslation): void {
                $categoryTranslation->setSlug($this->slugger->slugify($categoryTranslation->getTitle()));
            });
    }
}
