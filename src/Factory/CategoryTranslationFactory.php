<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\CategoryTranslation;
use App\Services\Slug\Slugger;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<CategoryTranslation>
 */
final class CategoryTranslationFactory extends PersistentObjectFactory
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

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'description' => self::faker()->text(),
            'excerpt' => self::faker()->text(20),
            'metaDescription' => self::faker()->text(60),
            'metaTitle' => self::faker()->text(60),
            'title' => self::faker()->text(25),
            'updatedAt' => self::faker()->dateTime(),
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
