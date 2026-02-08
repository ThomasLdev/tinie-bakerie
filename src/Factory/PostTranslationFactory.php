<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\PostTranslation;
use App\Services\Slug\Slugger;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<PostTranslation>
 */
final class PostTranslationFactory extends PersistentObjectFactory
{
    public function __construct(
        private readonly Slugger $slugger,
    ) {
        parent::__construct();
    }

    /**
     * @return class-string<PostTranslation>
     */
    public static function class(): string
    {
        return PostTranslation::class;
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
            'notes' => 'ingredient1|ingredient2|ingredient3|ingredient4|ingredient5',
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (PostTranslation $postTranslation): void {
                $postTranslation->setSlug($this->slugger->slugify($postTranslation->getTitle()));
            });
    }
}
