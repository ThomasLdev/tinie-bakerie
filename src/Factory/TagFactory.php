<?php

namespace App\Factory;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Tag>
 */
final class TagFactory extends PersistentProxyObjectFactory{
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
        return Tag::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'color' => self::faker()->hexColor(),
            'createdAt' => self::faker()->dateTime(),
            'updatedAt' => self::faker()->dateTime(),
            'title' => self::faker()->word(),
            'activatedAt' => self::faker()->boolean(90) ? self::faker()->dateTime() : null,
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(Tag $tag) {
                $this->entityManager->persist($tag);
                $this->entityManager->flush();

                foreach(explode('|', $this->supportedLocales) as $locale) {
                    if ($locale === $this->defaultLocale) {
                        continue;
                    }

                    $tag
                        ->setLocale($locale)
                        ->setTitle(self::faker()->word() . ' ' . $locale)
                    ;

                    $this->entityManager->persist($tag);
                    $this->entityManager->flush();
                }
            })
        ;
    }
}
