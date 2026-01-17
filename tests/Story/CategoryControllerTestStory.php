<?php

declare(strict_types=1);

namespace App\Tests\Story;

use App\Entity\Category;
use App\Factory\CategoryFactory;
use App\Factory\CategoryMediaFactory;
use App\Factory\CategoryMediaTranslationFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\PostFactory;
use App\Factory\PostMediaFactory;
use App\Factory\PostMediaTranslationFactory;
use App\Factory\PostTranslationFactory;
use App\Factory\TagFactory;
use App\Factory\TagTranslationFactory;
use Zenstruck\Foundry\Story;

/**
 * Story for CategoryController functional tests.
 * Provides predictable test data for testing category display functionality.
 */
final class CategoryControllerTestStory extends Story
{
    public function build(): void
    {
        // Create a test tag for posts
        $tag = TagFactory::createOne([
            'backgroundColor' => '#FF5733',
            'textColor' => '#FFFFFF',
            'translations' => [
                TagTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Tag Test FR',
                ]),
                TagTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Test Tag EN',
                ]),
            ],
        ]);

        // Create test categories with translations and media
        // Set explicit createdAt timestamps to ensure deterministic ordering
        $category1 = CategoryFactory::createOne([
            'createdAt' => new \DateTimeImmutable('2024-01-01 10:00:00'),
            'translations' => [
                CategoryTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Catégorie Test 1 FR',
                    'slug' => 'categorie-test-1-fr',
                    'metaDescription' => str_repeat('A', 120),
                    'excerpt' => 'Première catégorie de test',
                    'description' => 'Description complète de la première catégorie',
                ]),
                CategoryTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Test Category 1 EN',
                    'slug' => 'test-category-1-en',
                    'metaDescription' => str_repeat('B', 120),
                    'excerpt' => 'First test category',
                    'description' => 'Full description of the first category',
                ]),
            ],
            'media' => [
                CategoryMediaFactory::new([
                    'position' => 0,
                    'translations' => [
                        CategoryMediaTranslationFactory::new([
                            'locale' => 'fr',
                            'alt' => 'Image catégorie test 1 FR',
                            'title' => 'Titre image catégorie test 1',
                        ]),
                        CategoryMediaTranslationFactory::new([
                            'locale' => 'en',
                            'alt' => 'Test category image 1 EN',
                            'title' => 'Test category image 1 title',
                        ]),
                    ],
                ]),
            ],
        ]);

        // Add posts to category1
        PostFactory::createOne([
            'category' => $category1,
            'active' => true,
            'createdAt' => new \DateTimeImmutable('2024-01-01 11:00:00'),
            'tags' => [$tag],
            'translations' => [
                PostTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Post Test 1 FR',
                    'slug' => 'post-test-1-fr',
                    'excerpt' => 'Premier post de test',
                ]),
                PostTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Test Post 1 EN',
                    'slug' => 'test-post-1-en',
                    'excerpt' => 'First test post',
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
                    'position' => 0,
                    'translations' => [
                        PostMediaTranslationFactory::new([
                            'locale' => 'fr',
                            'alt' => 'Image post test 1 FR',
                            'title' => 'Titre image post test 1',
                        ]),
                        PostMediaTranslationFactory::new([
                            'locale' => 'en',
                            'alt' => 'Test post image 1 EN',
                            'title' => 'Test post image 1 title',
                        ]),
                    ],
                ]),
            ],
        ]);

        PostFactory::createOne([
            'category' => $category1,
            'active' => true,
            'createdAt' => new \DateTimeImmutable('2024-01-01 12:00:00'),
            'tags' => [$tag],
            'translations' => [
                PostTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Post Test 2 FR',
                    'slug' => 'post-test-2-fr',
                    'excerpt' => 'Deuxième post de test',
                ]),
                PostTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Test Post 2 EN',
                    'slug' => 'test-post-2-en',
                    'excerpt' => 'Second test post',
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
                    'position' => 0,
                    'translations' => [
                        PostMediaTranslationFactory::new([
                            'locale' => 'fr',
                            'alt' => 'Image post test 2 FR',
                            'title' => 'Titre image post test 2',
                        ]),
                        PostMediaTranslationFactory::new([
                            'locale' => 'en',
                            'alt' => 'Test post image 2 EN',
                            'title' => 'Test post image 2 title',
                        ]),
                    ],
                ]),
            ],
        ]);

        $this->addState('category1', $category1);

        $this->addState('category2', CategoryFactory::createOne([
            'createdAt' => new \DateTimeImmutable('2024-01-02 10:00:00'), // Newer category
            'translations' => [
                CategoryTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Catégorie Test 2 FR',
                    'slug' => 'categorie-test-2-fr',
                    'metaDescription' => str_repeat('C', 120),
                    'excerpt' => 'Deuxième catégorie de test',
                    'description' => 'Description complète de la deuxième catégorie',
                ]),
                CategoryTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Test Category 2 EN',
                    'slug' => 'test-category-2-en',
                    'metaDescription' => str_repeat('D', 120),
                    'excerpt' => 'Second test category',
                    'description' => 'Full description of the second category',
                ]),
            ],
            'media' => [
                CategoryMediaFactory::new([
                    'position' => 0,
                    'translations' => [
                        CategoryMediaTranslationFactory::new([
                            'locale' => 'fr',
                            'alt' => 'Image catégorie test 2 FR',
                            'title' => 'Titre image catégorie test 2',
                        ]),
                        CategoryMediaTranslationFactory::new([
                            'locale' => 'en',
                            'alt' => 'Test category image 2 EN',
                            'title' => 'Test category image 2 title',
                        ]),
                    ],
                ]),
            ],
        ]));
    }

    public function getCategories(): array
    {
        return [
            self::get('category1'),
            self::get('category2'),
        ];
    }

    public function getCategory(int $index): Category
    {
        return $this->getCategories()[$index]->_real();
    }

    /**
     * Get category slug for specific locale.
     */
    public function getCategorySlug(Category $category, string $locale): string
    {
        return $category->getTranslationByLocale($locale)?->getSlug() ?? '';
    }
}
