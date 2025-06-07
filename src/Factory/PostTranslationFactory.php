<?php

namespace App\Factory;

use App\Entity\PostTranslation;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<PostTranslation>
 */
final class PostTranslationFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return PostTranslation::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'localeCode' => self::faker()->text(255),
            'slug' => self::faker()->text(255),
            'title' => self::faker()->text(255),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(PostTranslation $postTranslation): void {})
        ;
    }
}
