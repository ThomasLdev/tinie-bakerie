<?php

namespace App\Factory;

use App\Entity\Post;
use App\Factory\Trait\SluggableEntityFactory;
use App\Services\Translations\TranslatableEntityPropertySetter;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Post>
 */
final class PostFactory extends PersistentProxyObjectFactory
{
    use SluggableEntityFactory;

    public function __construct(
        private readonly TranslatableEntityPropertySetter $propertySetter,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return Post::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'title' => self::faker()->unique()->text(15),
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'publishedAt' => self::faker()->boolean() ? self::faker()->dateTime() : null,
            'tags' => [],
            'media' => [],
            'sections' => [],
        ];
    }

    /**
     * Flush the default locale, then set the locale and update the translatable properties.
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Post $post) {
                $post->setSlug($this->createSlug($post->getTitle()));

                $this->propertySetter->processTranslations(
                    $post,
                    [
                        'title' => fn ($locale) => $post->getTitle().' '.$locale,
                        'slug' => fn ($locale, $post) => $this->createSlug($post->getTitle().' '.$locale),
                    ]
                );
            });
    }
}
