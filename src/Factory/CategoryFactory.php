<?php

namespace App\Factory;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Factory\Trait\SluggableEntityFactory;
use App\Services\Fixtures\Translations\TranslatableEntityPropertySetter;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Category>
 */
final class CategoryFactory extends PersistentProxyObjectFactory
{
    use SluggableEntityFactory;

    public function __construct(
        private readonly TranslatableEntityPropertySetter $propertySetter,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return Category::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'description' => self::faker()->text(100),
            'title' => self::faker()->word(),
            'updatedAt' => self::faker()->dateTime(),
            'media' => [],
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Category $category) {
                $category->setSlug($this->createSlug($category->getTitle()));

                $this->propertySetter->processTranslations(
                    $category,
                    CategoryTranslation::class,
                    [
                        'title' => fn ($locale) => sprintf('%s %s', $category->getTitle(), $locale),
                        'slug' => fn ($locale) => $this->createSlug(
                            sprintf('%s %s', $category->getTitle(), $locale)
                        ),
                        'description' => fn ($locale) => sprintf('%s %s', $category->getDescription(), $locale),
                    ]
                );
            });
    }
}
