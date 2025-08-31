<?php

namespace App\Factory;

use App\Entity\CategoryTranslation;
use App\Services\Slug\Slugger;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<CategoryTranslation>
 */
final class CategoryTranslationFactory extends PersistentProxyObjectFactory{
    public function __construct(
        private readonly Slugger $slugger,
    )
    {
        parent::__construct();
    }

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

    protected function initialize(): static
    {
        return $this
             ->afterInstantiate(function(CategoryTranslation $categoryTranslation): void {
                    $categoryTranslation->setSlug($this->slugger->slugify($categoryTranslation->getTitle()));
             })
        ;
    }
}
