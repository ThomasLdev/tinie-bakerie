<?php

namespace App\Factory;

use App\Entity\Post;
use App\Entity\PostTranslation;
use App\Factory\Trait\SluggableEntityFactory;
use App\Services\Fixtures\Translations\TranslatableEntityPropertySetter;
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
            'metaTitle' => self::faker()->unique()->text(10),
            'metaDescription' => self::faker()->unique()->text(30),
            'excerpt' => self::faker()->unique()->text(10),
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'active' => self::faker()->boolean(80),
            'tags' => [],
            'media' => [],
            'sections' => [],
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Post $post) {
                $post->setSlug($this->createSlug($post->getTitle()));

                $this->propertySetter->processTranslations(
                    $post,
                    PostTranslation::class,
                    [
                        'title' => fn ($locale) => sprintf('%s %s', $post->getTitle(), $locale),
                        'metaTitle' => fn ($locale) => sprintf('%s %s', $post->getMetaTitle(), $locale),
                        'metaDescription' => fn ($locale) => sprintf('%s %s', $post->getMetaDescription(), $locale),
                        'excerpt' => fn ($locale) => sprintf('%s %s', $post->getExcerpt(), $locale),
                        'slug' => fn ($locale) => $this->createSlug(sprintf('%s %s', $post->getTitle(), $locale)),
                    ]
                );
            });
    }
}
