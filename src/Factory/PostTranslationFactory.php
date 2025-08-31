<?php

namespace App\Factory;

use App\Entity\PostTranslation;
use App\Services\Slug\Slugger;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostTranslation>
 */
final class PostTranslationFactory extends PersistentProxyObjectFactory
{
    public function __construct(
        private readonly Slugger $slugger,
    )
    {
        parent::__construct();
    }

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
        ];
    }

    protected function initialize(): static
    {
        return $this
             ->afterInstantiate(function(PostTranslation $postTranslation): void {
                    $postTranslation->setSlug($this->slugger->slugify($postTranslation->getTitle()));
             })
        ;
    }
}
