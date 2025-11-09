<?php

declare(strict_types=1);

namespace App\Tests\Story;

use App\Entity\Category;
use App\Entity\Post;
use App\Factory\CategoryFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\PostFactory;
use App\Factory\PostMediaFactory;
use App\Factory\PostMediaTranslationFactory;
use App\Factory\PostTranslationFactory;
use App\Services\Media\Enum\MediaType;
use App\Services\Post\Enum\Difficulty;
use Zenstruck\Foundry\Story;

/**
 * Story for PostController functional tests.
 * Provides predictable test data for testing post display functionality.
 */
final class PostControllerTestStory extends Story
{
    public function build(): void
    {
        // Create a test category with translations
        $this->addState('category', CategoryFactory::createOne([
            'translations' => [
                CategoryTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Catégorie Test FR',
                    'slug' => 'categorie-test-fr',
                    'metaDescription' => str_repeat('A', 120),
                    'excerpt' => 'Catégorie pour tests',
                ]),
                CategoryTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Test Category EN',
                    'slug' => 'test-category-en',
                    'metaDescription' => str_repeat('B', 120),
                    'excerpt' => 'Category for testing',
                ]),
            ],
        ]));

        // Create active posts with translations and media
        // Set explicit createdAt timestamps to ensure deterministic ordering
        $this->addState('activePost1', PostFactory::createOne([
            'active' => true,
            'category' => self::get('category'),
            'cookingTime' => 30,
            'difficulty' => Difficulty::Easy,
            'createdAt' => new \DateTimeImmutable('2024-01-01 10:00:00'),
            'translations' => [
                PostTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Article Test 1 FR',
                    'slug' => 'article-test-1-fr',
                    'metaDescription' => str_repeat('C', 120),
                    'excerpt' => 'Premier article de test',
                ]),
                PostTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Test Post 1 EN',
                    'slug' => 'test-post-1-en',
                    'metaDescription' => str_repeat('D', 120),
                    'excerpt' => 'First test post',
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
                    'mediaName' => 'test-image-1.jpg',
                    'type' => MediaType::Image,
                    'position' => 0,
                    'translations' => [
                        PostMediaTranslationFactory::new([
                            'locale' => 'fr',
                            'alt' => 'Image test 1 FR',
                            'title' => 'Titre image test 1',
                        ]),
                        PostMediaTranslationFactory::new([
                            'locale' => 'en',
                            'alt' => 'Test image 1 EN',
                            'title' => 'Test image 1 title',
                        ]),
                    ],
                ]),
            ],
        ]));

        $this->addState('activePost2', PostFactory::createOne([
            'active' => true,
            'category' => self::get('category'),
            'cookingTime' => 45,
            'difficulty' => Difficulty::Medium,
            'createdAt' => new \DateTimeImmutable('2024-01-02 10:00:00'), // Newer post
            'translations' => [
                PostTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Article Test 2 FR',
                    'slug' => 'article-test-2-fr',
                    'metaDescription' => str_repeat('E', 120),
                    'excerpt' => 'Deuxième article de test',
                ]),
                PostTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Test Post 2 EN',
                    'slug' => 'test-post-2-en',
                    'metaDescription' => str_repeat('F', 120),
                    'excerpt' => 'Second test post',
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
                    'mediaName' => 'test-image-2.jpg',
                    'type' => MediaType::Image,
                    'position' => 0,
                    'translations' => [
                        PostMediaTranslationFactory::new([
                            'locale' => 'fr',
                            'alt' => 'Image test 2 FR',
                            'title' => 'Titre image test 2',
                        ]),
                        PostMediaTranslationFactory::new([
                            'locale' => 'en',
                            'alt' => 'Test image 2 EN',
                            'title' => 'Test image 2 title',
                        ]),
                    ],
                ]),
            ],
        ]));

        // Create an inactive post (should not be displayed)
        $this->addState('inactivePost', PostFactory::createOne([
            'active' => false,
            'category' => self::get('category'),
            'cookingTime' => 60,
            'difficulty' => Difficulty::Advanced,
            'translations' => [
                PostTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Article Inactif FR',
                    'slug' => 'article-inactif-fr',
                    'metaDescription' => str_repeat('G', 120),
                    'excerpt' => 'Article inactif',
                ]),
                PostTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Inactive Post EN',
                    'slug' => 'inactive-post-en',
                    'metaDescription' => str_repeat('H', 120),
                    'excerpt' => 'Inactive post',
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
                    'mediaName' => 'test-image-inactive.jpg',
                    'type' => MediaType::Image,
                    'position' => 0,
                    'translations' => [
                        PostMediaTranslationFactory::new([
                            'locale' => 'fr',
                            'alt' => 'Image test inactif FR',
                            'title' => 'Titre image inactif',
                        ]),
                        PostMediaTranslationFactory::new([
                            'locale' => 'en',
                            'alt' => 'Inactive test image EN',
                            'title' => 'Inactive test image title',
                        ]),
                    ],
                ]),
            ],
        ]));
    }

    public function getActivePosts(): array
    {
        return [
            self::get('activePost1'),
            self::get('activePost2'),
        ];
    }

    public function getActivePost(int $index): Post
    {
        return $this->getActivePosts()[$index]->_real();
    }

    public function getInactivePost(): Post
    {
        return self::get('inactivePost')->_real();
    }

    /**
     * Get category slug for specific locale.
     */
    public function getCategorySlug(Category $category, string $locale): string
    {
        return $category->getTranslationByLocale($locale)?->getSlug() ?? '';
    }

    /**
     * Get post slug for specific locale.
     */
    public function getPostSlug(Post $post, string $locale): string
    {
        return $post->getTranslationByLocale($locale)?->getSlug() ?? '';
    }
}
