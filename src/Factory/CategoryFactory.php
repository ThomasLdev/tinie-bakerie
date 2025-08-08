<?php

namespace App\Factory;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @extends PersistentProxyObjectFactory<Category>
 */
final class CategoryFactory extends PersistentProxyObjectFactory{
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
        return Category::class;
    }

    protected function defaults(): array|callable    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'description' => self::faker()->text(),
            'title' => self::faker()->sentence(4),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }

        /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(Category $category) {
                $slugger = new AsciiSlugger();
                $category->setSlug($slugger->slug($category->getTitle())->lower());

                $this->entityManager->persist($category);
                $this->entityManager->flush();

                foreach(explode('|', $this->supportedLocales) as $locale) {
                    if ($locale === $this->defaultLocale) {
                        continue;
                    }

                    $category
                        ->setLocale($locale)
                        ->setTitle(self::faker()->sentence(4) . ' ' . $locale)
                        ->setSlug($slugger->slug($category->getTitle())->lower())
                    ;

                    $this->entityManager->persist($category);
                    $this->entityManager->flush();
                }
            });
    }
}
