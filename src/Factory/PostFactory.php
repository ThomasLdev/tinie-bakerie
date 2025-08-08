<?php

namespace App\Factory;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Post>
 */
final class PostFactory extends PersistentProxyObjectFactory{
    public function __construct(
        #[Autowire(param: 'app.supported_locales')] private readonly string $supportedLocales,
        #[Autowire(param: 'default_locale')] private readonly string $defaultLocale,
        private readonly EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    public static function class(): string
    {
        return Post::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->sentence(4),
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'publishedAt' => self::faker()->boolean() ? self::faker()->dateTime() : null,
            'locale' => $this->defaultLocale,
            'category' => null,
        ];
    }

    /**
     * Flush the default locale, then set the locale and update the translatable properties.
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(Post $post) {
                $slugger = new AsciiSlugger();
                $post->setSlug($slugger->slug($post->getTitle())->lower());

                $this->entityManager->persist($post);
                $this->entityManager->flush();

                foreach(explode('|', $this->supportedLocales) as $locale) {
                    if ($locale === $this->defaultLocale) {
                        continue;
                    }

                    $post
                        ->setLocale($locale)
                        ->setTitle(self::faker()->sentence(4) . ' ' . $locale)
                        ->setSlug($slugger->slug($post->getTitle())->lower())
                    ;

                    $this->entityManager->persist($post);
                    $this->entityManager->flush();
                }
            });
    }
}
