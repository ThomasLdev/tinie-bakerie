<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Post;
use App\Factory\CategoryFactory;
use App\Factory\CategoryMediaFactory;
use App\Factory\CategoryMediaTranslationFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\Contracts\LocaleAwareFactory;
use App\Factory\PostFactory;
use App\Factory\PostMediaFactory;
use App\Factory\PostMediaTranslationFactory;
use App\Factory\PostSectionFactory;
use App\Factory\PostSectionMediaFactory;
use App\Factory\PostSectionMediaTranslationFactory;
use App\Factory\PostSectionTranslationFactory;
use App\Factory\PostTranslationFactory;
use App\Factory\TagFactory;
use App\Factory\TagTranslationFactory;
use App\Services\Fixtures\Media\MediaLoader;
use App\Services\Locale\Locales;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Faker\Generator;

use function Zenstruck\Foundry\Persistence\flush_after;

class AppFixtures extends Fixture
{
    /** @var array<string, Generator> */
    private array $fakerByLocale = [];

    public function __construct(
        private readonly MediaLoader $mediaLoader,
        private readonly Locales $locales,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        flush_after(function (): void {
            $categories = CategoryFactory::createMany(5, fn (): array => [
                'translations' => $this->createTranslations(CategoryTranslationFactory::class),
            ]);

            foreach ($categories as $category) {
                CategoryMediaFactory::createRange(1, 3, fn (): array => array_merge(
                    [
                        'category' => $category,
                        'translations' => $this->createTranslations(CategoryMediaTranslationFactory::class),
                    ],
                    $this->mediaLoader->getRandomMediaFactoryFields(),
                ));
            }

            $tags = TagFactory::createMany(15, fn (): array => [
                'translations' => $this->createTranslations(TagTranslationFactory::class),
            ]);

            /** @var Post[] $posts */
            $posts = PostFactory::createMany(30, function () use ($categories, $tags): array {
                $randomCategory = $categories[array_rand($categories)];
                $randomTagCount = random_int(1, 3);
                $randomTags = (array) array_rand(array_flip(array_keys($tags)), min($randomTagCount, \count($tags)));
                $selectedTags = array_map(static fn (int $index) => $tags[$index], $randomTags);

                return [
                    'translations' => $this->createTranslations(PostTranslationFactory::class),
                    'category' => $randomCategory,
                    'tags' => $selectedTags,
                    'media' => [],
                    'sections' => [],
                ];
            });

            foreach ($posts as $post) {
                PostMediaFactory::createRange(1, 3, fn (): array => array_merge(
                    [
                        'post' => $post,
                        'translations' => $this->createTranslations(PostMediaTranslationFactory::class),
                    ],
                    $this->mediaLoader->getRandomMediaFactoryFields(),
                ));
            }

            $sections = [];

            foreach ($posts as $post) {
                $postSections = PostSectionFactory::createRange(2, 5, fn (): array => [
                    'post' => $post,
                    'translations' => $this->createTranslations(PostSectionTranslationFactory::class),
                    'media' => [],
                ]);
                $sections = array_merge($sections, $postSections);
            }

            foreach ($sections as $section) {
                PostSectionMediaFactory::createRange(1, 3, fn (): array => array_merge(
                    [
                        'postSection' => $section,
                        'translations' => $this->createTranslations(PostSectionMediaTranslationFactory::class),
                    ],
                    $this->mediaLoader->getRandomMediaFactoryFields(),
                ));
            }
        });
    }

    /**
     * Creates translations for all configured locales using locale-specific Faker data.
     *
     * @param class-string<LocaleAwareFactory> $factoryClass
     *
     * @return array<int, object>
     */
    private function createTranslations(string $factoryClass): array
    {
        $sequence = [];

        foreach ($this->locales->get() as $locale) {
            $faker = $this->getFaker($locale);
            $sequence[] = array_merge(
                ['locale' => $locale],
                $factoryClass::defaultsForLocale($faker),
            );
        }

        return $factoryClass::createSequence($sequence);
    }

    /**
     * Get or create a Faker instance for the given locale.
     */
    private function getFaker(string $locale): Generator
    {
        $fakerLocale = $this->mapLocaleToFakerLocale($locale);

        if (!isset($this->fakerByLocale[$fakerLocale])) {
            $this->fakerByLocale[$fakerLocale] = FakerFactory::create($fakerLocale);
        }

        return $this->fakerByLocale[$fakerLocale];
    }

    private function mapLocaleToFakerLocale(string $locale): string
    {
        return match ($locale) {
            'en' => 'en_US',
            default => 'fr_FR',
        };
    }
}
