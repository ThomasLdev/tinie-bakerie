<?php

namespace App\DataFixtures;

use App\Factory\CategoryMediaTranslationFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\PostMediaTranslationFactory;
use App\Factory\PostSectionMediaTranslationFactory;
use App\Factory\PostSectionTranslationFactory;
use App\Factory\PostTranslationFactory;
use App\Factory\TagTranslationFactory;
use App\Services\Fixtures\Media\MediaLoader;
use App\Factory\CategoryFactory;
use App\Factory\CategoryMediaFactory;
use App\Factory\PostFactory;
use App\Factory\PostMediaFactory;
use App\Factory\PostSectionFactory;
use App\Factory\PostSectionMediaFactory;
use App\Factory\TagFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

class AppFixtures extends Fixture
{
    private array $locales;

    public function __construct(
        private readonly MediaLoader $mediaLoader,
        #[Autowire(param: 'app.supported_locales')] private readonly string $supportedLocales,
    )
    {
        $this->locales = explode('|', $this->supportedLocales);
    }

    public function load(ObjectManager $manager): void
    {
        CategoryFactory::createMany(5, function () {
            return [
                'media' => CategoryMediaFactory::createRange(1, 3, function () {
                    return array_merge(
                        [
                            'translations' => $this->createTranslations(CategoryMediaTranslationFactory::new()),
                        ],
                        $this->mediaLoader->getRandomMediaFactoryFields()
                    );
                }),
                'translations' => $this->createTranslations(CategoryTranslationFactory::new()),
            ];
        });

        TagFactory::createMany(15, function () {
            return [
                'translations' => $this->createTranslations(TagTranslationFactory::new()),
            ];
        });

        PostFactory::createMany(30, function () {
            return [
                'translations' => $this->createTranslations(PostTranslationFactory::new()),
                'category' => CategoryFactory::random(),
                'tags' => TagFactory::randomRange(1, 3),
                'media' => PostMediaFactory::createRange(1, 3, function () {
                    return array_merge(
                        [
                            'translations' => $this->createTranslations(PostMediaTranslationFactory::new()),
                        ],
                        $this->mediaLoader->getRandomMediaFactoryFields()
                    );
                }),
            ];
        });

        PostSectionFactory::createMany(40, function () {
            return [
                'media' => PostSectionMediaFactory::createRange(1, 3, function () {
                    return array_merge(
                        [
                            'translations' => $this->createTranslations(PostSectionMediaTranslationFactory::new()),
                        ],
                        $this->mediaLoader->getRandomMediaFactoryFields()
                    );
                }),
                'post' => PostFactory::random(),
                'translations' => $this->createTranslations(PostSectionTranslationFactory::new()),
            ];
        });
    }

    private function createTranslations(PersistentProxyObjectFactory $factory): array
    {
        $translations = [];

        foreach ($this->locales as $locale) {
            $translations[] = $factory::createOne([
                'locale' => $locale,
            ]);
        }

        return $translations;
    }
}
