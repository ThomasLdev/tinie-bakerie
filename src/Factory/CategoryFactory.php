<?php

namespace App\Factory;

use App\Entity\Category;
use App\Factory\Trait\SluggableEntityFactory;
use App\Factory\Utils\TranslatableEntityPropertySetter;
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
                    [
                        'title' => fn ($locale) => $category->getTitle().' '.$locale,
                        'slug' => fn ($locale, $category) => $this->createSlug($category->getTitle().' '.$locale),
                        'description' => fn ($locale) => $category->getDescription().' '.$locale,
                    ]
                );
            });
    }
}
