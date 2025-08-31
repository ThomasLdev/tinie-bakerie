<?php

namespace App\Factory;

use App\Entity\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Post>
 */
final class PostFactory extends PersistentProxyObjectFactory
{
    public function __construct(

    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return Post::class;
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'active' => self::faker()->boolean(80),
            'tags' => [],
            'media' => [],
            'sections' => [],
            'translations' => new ArrayCollection(),
        ];
    }

    private function createTranslations(): ArrayCollection
    {
        $translations = new ArrayCollection();

        foreach (explode('|', $this->locales) as $locale) {
            $translations->add();
        }

        return $translations;
    }
}
