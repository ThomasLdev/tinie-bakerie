<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\RecipeTranslation;
use App\Services\Slug\Slugger;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<RecipeTranslation>
 */
final class RecipeTranslationFactory extends PersistentObjectFactory
{
    public function __construct(
        private readonly Slugger $slugger,
    ) {
        parent::__construct();
    }

    /**
     * @return class-string<RecipeTranslation>
     */
    public static function class(): string
    {
        return RecipeTranslation::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'excerpt' => self::faker()->text(60),
            'metaDescription' => self::faker()->text(),
            'metaTitle' => self::faker()->text(60),
            'title' => self::faker()->text(25),
            'notes' => self::faker()->paragraph(3),
            'chefNoteTitle' => self::faker()->sentence(6),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (RecipeTranslation $translation): void {
                $translation->setSlug($this->slugger->slugify($translation->getTitle()));
            });
    }
}
