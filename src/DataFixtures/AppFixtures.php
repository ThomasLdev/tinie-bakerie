<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Post;
use App\Factory\CategoryFactory;
use App\Factory\CategoryMediaFactory;
use App\Factory\CategoryMediaTranslationFactory;
use App\Factory\CategoryTranslationFactory;
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
use App\Services\Locale\Locales;
use App\Tests\Fixtures\MediaLoader;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

use function Zenstruck\Foundry\Persistence\flush_after;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly MediaLoader $mediaLoader,
        private readonly Locales $locales,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        flush_after(function (): void {
            $categoryIndex = 0;
            $categories = CategoryFactory::createMany(5, function () use (&$categoryIndex): array {
                $isFeatured = $categoryIndex++ < 3;

                return [
                    'translations' => $this->createTranslations(CategoryTranslationFactory::new()),
                    'isFeatured' => $isFeatured,
                ];
            });

            foreach ($categories as $category) {
                CategoryMediaFactory::createRange(1, 3, fn (): array => ['category' => $category, 'translations' => $this->createTranslations(CategoryMediaTranslationFactory::new()), 'media' => $this->mediaLoader->getRandomMedia()]);
            }

            $tagIndex = 0;
            $tags = TagFactory::createMany(15, function () use (&$tagIndex): array {
                $isFeatured = $tagIndex++ < 5;

                return [
                    'translations' => $this->createTranslations(TagTranslationFactory::new()),
                    'isFeatured' => $isFeatured,
                    'image' => $this->mediaLoader->getRandomMedia(),
                ];
            });

            $postIndex = 0;

            /** @var Post[] $posts */
            $posts = PostFactory::createMany(30, function () use ($categories, $tags, &$postIndex): array {
                // Pick random category and tags from the already-created arrays
                $randomCategory = $categories[array_rand($categories)];
                $randomTagCount = random_int(1, 3);
                $randomTags = (array) array_rand(array_flip(array_keys($tags)), min($randomTagCount, \count($tags)));
                $selectedTags = array_map(static fn (int $index) => $tags[$index], $randomTags);

                $isFeatured = ($postIndex++ % 10) < 2;

                return [
                    'translations' => $this->createTranslations(PostTranslationFactory::new()),
                    'category' => $randomCategory,
                    'tags' => $selectedTags,
                    'media' => [],
                    'sections' => [],
                    'isFeatured' => $isFeatured,
                ];
            });

            foreach ($posts as $post) {
                PostMediaFactory::createRange(1, 3, fn (): array => ['post' => $post, 'translations' => $this->createTranslations(PostMediaTranslationFactory::new()), 'media' => $this->mediaLoader->getRandomMedia()]);
            }

            $sections = [];

            foreach ($posts as $post) {
                $postSections = PostSectionFactory::createRange(2, 5, fn (): array => [
                    'post' => $post,
                    'translations' => $this->createTranslations(PostSectionTranslationFactory::new()),
                    'media' => [],
                ]);
                $sections = array_merge($sections, $postSections);
            }

            foreach ($sections as $section) {
                PostSectionMediaFactory::createRange(1, 3, fn (): array => ['postSection' => $section, 'translations' => $this->createTranslations(PostSectionMediaTranslationFactory::new()), 'media' => $this->mediaLoader->getRandomMedia()]);
            }
        });
    }

    /**
     * Creates translations for all configured locales.
     *
     * @return array<array-key, mixed>
     *
     * @phpstan-ignore-next-line
     */
    private function createTranslations(PersistentObjectFactory $factory): array
    {
        $locales = $this->locales->get();

        $sequence = [];

        foreach ($locales as $locale) {
            $sequence[] = ['locale' => $locale];
        }

        return $factory::createSequence($sequence);
    }
}
