<?php

declare(strict_types=1);

namespace App\Tests\Story;

use App\Entity\Post;
use App\Factory\CategoryFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\PostFactory;
use App\Factory\PostMediaFactory;
use App\Factory\PostMediaTranslationFactory;
use App\Factory\PostSectionFactory;
use App\Factory\PostSectionTranslationFactory;
use App\Factory\PostTranslationFactory;
use App\Factory\TagFactory;
use App\Factory\TagTranslationFactory;
use App\Services\Post\Enum\Difficulty;
use App\Services\PostSection\Enum\PostSectionType;
use Zenstruck\Foundry\Story;

/**
 * Story for PostSearch functional tests.
 * Creates posts with specific searchable content to test FTS functionality.
 *
 * Test data structure:
 * - 3 active posts with different searchable content
 * - 1 inactive post (should never appear in results)
 * - 2 tags (Vegan, Chocolate)
 * - 1 category (Desserts)
 *
 * Search scenarios covered:
 * - Title matches (weight A)
 * - Excerpt matches (weight B)
 * - Section content matches (weight C)
 * - Category matches (weight D)
 * - Tag matches (weight D)
 */
final class PostSearchTestStory extends Story
{
    // State keys for easy access
    public const string CATEGORY_DESSERTS = 'categoryDesserts';

    public const string TAG_VEGAN = 'tagVegan';

    public const string TAG_CHOCOLATE = 'tagChocolate';

    public const string POST_CHOCOLATE_CAKE = 'chocolateCake';

    public const string POST_VEGAN_COOKIES = 'veganCookies';

    public const string POST_TIRAMISU = 'tiramisu';

    public const string POST_INACTIVE = 'inactivePost';

    public function build(): void
    {
        $this->createTags();
        $this->createCategory();
        $this->createPosts();
    }

    // ========== Accessor Methods ==========

    public function getChocolateCake(): Post
    {
        // @var Post $proxy
        return self::get(self::POST_CHOCOLATE_CAKE);
    }

    public function getVeganCookies(): Post
    {
        // @var Post $proxy
        return self::get(self::POST_VEGAN_COOKIES);
    }

    public function getTiramisu(): Post
    {
        // @var Post $proxy
        return self::get(self::POST_TIRAMISU);
    }

    public function getInactivePost(): Post
    {
        // @var Post $proxy
        return self::get(self::POST_INACTIVE);
    }

    /**
     * @return Post[]
     */
    public function getActivePosts(): array
    {
        return [
            $this->getChocolateCake(),
            $this->getVeganCookies(),
            $this->getTiramisu(),
        ];
    }

    public function getActivePostCount(): int
    {
        return 3;
    }

    // ========== Expected Title Helpers ==========

    /**
     * @return array<string, array{fr: string, en: string}>
     */
    public function getExpectedTitles(): array
    {
        return [
            self::POST_CHOCOLATE_CAKE => [
                'fr' => 'Gâteau au Chocolat Fondant',
                'en' => 'Molten Chocolate Cake',
            ],
            self::POST_VEGAN_COOKIES => [
                'fr' => 'Cookies aux Pépites',
                'en' => 'Chip Cookies',
            ],
            self::POST_TIRAMISU => [
                'fr' => 'Tiramisu Classique',
                'en' => 'Classic Tiramisu',
            ],
            self::POST_INACTIVE => [
                'fr' => 'Recette Secrète Chocolat',
                'en' => 'Secret Chocolate Recipe',
            ],
        ];
    }

    public function getExpectedTitle(string $postKey, string $locale): string
    {
        return $this->getExpectedTitles()[$postKey][$locale];
    }

    private function createTags(): void
    {
        $this->addState(self::TAG_VEGAN, TagFactory::createOne([
            'backgroundColor' => '#22c55e',
            'textColor' => '#ffffff',
            'translations' => [
                TagTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Végétalien',
                ]),
                TagTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Vegan',
                ]),
            ],
        ]));

        $this->addState(self::TAG_CHOCOLATE, TagFactory::createOne([
            'backgroundColor' => '#78350f',
            'textColor' => '#ffffff',
            'translations' => [
                TagTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Chocolat',
                ]),
                TagTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Chocolate',
                ]),
            ],
        ]));
    }

    private function createCategory(): void
    {
        $this->addState(self::CATEGORY_DESSERTS, CategoryFactory::createOne([
            'translations' => [
                CategoryTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Desserts Gourmands',
                    'slug' => 'desserts-gourmands',
                    'metaDescription' => str_repeat('A', 120),
                    'excerpt' => 'Nos meilleurs desserts',
                ]),
                CategoryTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Gourmet Desserts',
                    'slug' => 'gourmet-desserts',
                    'metaDescription' => str_repeat('B', 120),
                    'excerpt' => 'Our best desserts',
                ]),
            ],
        ]));
    }

    private function createPosts(): void
    {
        // Post 1: Chocolate cake - searchable by title, section content
        $this->addState(self::POST_CHOCOLATE_CAKE, PostFactory::createOne([
            'active' => true,
            'category' => self::get(self::CATEGORY_DESSERTS),
            'cookingTime' => 45,
            'difficulty' => Difficulty::Medium,
            'tags' => [self::get(self::TAG_CHOCOLATE)],
            'translations' => [
                PostTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Gâteau au Chocolat Fondant',
                    'slug' => 'gateau-chocolat-fondant',
                    'metaDescription' => str_repeat('C', 120),
                    'excerpt' => 'Un délicieux gâteau moelleux au cacao',
                ]),
                PostTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Molten Chocolate Cake',
                    'slug' => 'molten-chocolate-cake',
                    'metaDescription' => str_repeat('D', 120),
                    'excerpt' => 'A delicious moist cocoa cake',
                ]),
            ],
            'sections' => [
                PostSectionFactory::new([
                    'position' => 0,
                    'type' => PostSectionType::Default,
                    'translations' => [
                        PostSectionTranslationFactory::new([
                            'locale' => 'fr',
                            'title' => 'Préparation',
                            'content' => 'Faire fondre le chocolat noir avec le beurre',
                        ]),
                        PostSectionTranslationFactory::new([
                            'locale' => 'en',
                            'title' => 'Preparation',
                            'content' => 'Melt the dark chocolate with butter',
                        ]),
                    ],
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
                    'position' => 0,
                    'translations' => [
                        PostMediaTranslationFactory::new(['locale' => 'fr', 'alt' => 'Gâteau chocolat', 'title' => 'Gâteau']),
                        PostMediaTranslationFactory::new(['locale' => 'en', 'alt' => 'Chocolate cake', 'title' => 'Cake']),
                    ],
                ]),
            ],
        ]));

        // Post 2: Vegan cookies - searchable by tag, excerpt
        $this->addState(self::POST_VEGAN_COOKIES, PostFactory::createOne([
            'active' => true,
            'category' => self::get(self::CATEGORY_DESSERTS),
            'cookingTime' => 25,
            'difficulty' => Difficulty::Easy,
            'tags' => [self::get(self::TAG_VEGAN)],
            'translations' => [
                PostTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Cookies aux Pépites',
                    'slug' => 'cookies-pepites',
                    'metaDescription' => str_repeat('E', 120),
                    'excerpt' => 'Des cookies croustillants végétaliens sans produits laitiers',
                ]),
                PostTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Chip Cookies',
                    'slug' => 'chip-cookies',
                    'metaDescription' => str_repeat('F', 120),
                    'excerpt' => 'Crispy vegan cookies without dairy products',
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
                    'position' => 0,
                    'translations' => [
                        PostMediaTranslationFactory::new(['locale' => 'fr', 'alt' => 'Cookies', 'title' => 'Cookies']),
                        PostMediaTranslationFactory::new(['locale' => 'en', 'alt' => 'Cookies', 'title' => 'Cookies']),
                    ],
                ]),
            ],
        ]));

        // Post 3: Tiramisu - searchable by title, section content (mascarpone)
        $this->addState(self::POST_TIRAMISU, PostFactory::createOne([
            'active' => true,
            'category' => self::get(self::CATEGORY_DESSERTS),
            'cookingTime' => 30,
            'difficulty' => Difficulty::Medium,
            'translations' => [
                PostTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Tiramisu Classique',
                    'slug' => 'tiramisu-classique',
                    'metaDescription' => str_repeat('G', 120),
                    'excerpt' => 'Le dessert italien par excellence',
                ]),
                PostTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Classic Tiramisu',
                    'slug' => 'classic-tiramisu',
                    'metaDescription' => str_repeat('H', 120),
                    'excerpt' => 'The quintessential Italian dessert',
                ]),
            ],
            'sections' => [
                PostSectionFactory::new([
                    'position' => 0,
                    'type' => PostSectionType::Default,
                    'translations' => [
                        PostSectionTranslationFactory::new([
                            'locale' => 'fr',
                            'title' => 'Ingrédients',
                            'content' => 'Mascarpone frais, oeufs, café expresso, biscuits',
                        ]),
                        PostSectionTranslationFactory::new([
                            'locale' => 'en',
                            'title' => 'Ingredients',
                            'content' => 'Fresh mascarpone, eggs, espresso coffee, biscuits',
                        ]),
                    ],
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
                    'position' => 0,
                    'translations' => [
                        PostMediaTranslationFactory::new(['locale' => 'fr', 'alt' => 'Tiramisu', 'title' => 'Tiramisu']),
                        PostMediaTranslationFactory::new(['locale' => 'en', 'alt' => 'Tiramisu', 'title' => 'Tiramisu']),
                    ],
                ]),
            ],
        ]));

        // Post 4: INACTIVE - should never appear in search results
        $this->addState(self::POST_INACTIVE, PostFactory::createOne([
            'active' => false,
            'category' => self::get(self::CATEGORY_DESSERTS),
            'cookingTime' => 60,
            'difficulty' => Difficulty::Advanced,
            'translations' => [
                PostTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Recette Secrète Chocolat',
                    'slug' => 'recette-secrete-chocolat',
                    'metaDescription' => str_repeat('I', 120),
                    'excerpt' => 'Une recette au chocolat qui ne devrait pas apparaître',
                ]),
                PostTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Secret Chocolate Recipe',
                    'slug' => 'secret-chocolate-recipe',
                    'metaDescription' => str_repeat('J', 120),
                    'excerpt' => 'A chocolate recipe that should not appear',
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
                    'position' => 0,
                    'translations' => [
                        PostMediaTranslationFactory::new(['locale' => 'fr', 'alt' => 'Secret', 'title' => 'Secret']),
                        PostMediaTranslationFactory::new(['locale' => 'en', 'alt' => 'Secret', 'title' => 'Secret']),
                    ],
                ]),
            ],
        ]));
    }
}
