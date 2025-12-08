<?php

declare(strict_types=1);

namespace App\Tests\Story;

use App\Entity\Post;
use App\Factory\CategoryFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\PostFactory;
use App\Factory\PostTranslationFactory;
use App\Factory\TagFactory;
use App\Factory\TagTranslationFactory;
use App\Services\Post\Enum\Difficulty;
use Zenstruck\Foundry\Story;

final class MeilisearchIndexingStory extends Story
{
    public const string FR_TITLE = 'Recette Test FR';
    public const string EN_TITLE = 'Test Recipe EN';
    public const string FR_EXCERPT = 'Extrait de test';
    public const string EN_EXCERPT = 'Test excerpt';
    public const string CATEGORY_FR_TITLE = 'Catégorie FR';
    public const string CATEGORY_EN_TITLE = 'Category EN';
    public const string TAG_FR = 'Dessert FR';
    public const string TAG_EN = 'Dessert EN';
    public const int COOKING_TIME = 30;
    public const Difficulty DIFFICULTY = Difficulty::Easy;

    public function build(): void
    {
        $category = CategoryFactory::createOne([
            'translations' => [
                CategoryTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => self::CATEGORY_FR_TITLE,
                    'slug' => 'categorie-fr',
                    'metaDescription' => str_repeat('A', 120),
                    'excerpt' => 'Catégorie pour tests',
                ]),
                CategoryTranslationFactory::new([
                    'locale' => 'en',
                    'title' => self::CATEGORY_EN_TITLE,
                    'slug' => 'category-en',
                    'metaDescription' => str_repeat('B', 120),
                    'excerpt' => 'Category for testing',
                ]),
            ],
        ]);

        $tag = TagFactory::createOne([
            'translations' => [
                TagTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => self::TAG_FR,
                ]),
                TagTranslationFactory::new([
                    'locale' => 'en',
                    'title' => self::TAG_EN,
                ]),
            ],
        ]);

        $this->addState('postWithBothLocales', PostFactory::createOne([
            'active' => true,
            'category' => $category,
            'cookingTime' => self::COOKING_TIME,
            'difficulty' => self::DIFFICULTY,
            'createdAt' => new \DateTimeImmutable('2024-01-15 10:00:00'),
            'tags' => [$tag],
            'translations' => [
                PostTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => self::FR_TITLE,
                    'slug' => 'recette-test-fr',
                    'metaDescription' => str_repeat('C', 120),
                    'excerpt' => self::FR_EXCERPT,
                ]),
                PostTranslationFactory::new([
                    'locale' => 'en',
                    'title' => self::EN_TITLE,
                    'slug' => 'test-recipe-en',
                    'metaDescription' => str_repeat('D', 120),
                    'excerpt' => self::EN_EXCERPT,
                ]),
            ],
        ]));

        $this->addState('postWithFrenchOnly', PostFactory::createOne([
            'active' => true,
            'category' => $category,
            'cookingTime' => 45,
            'difficulty' => Difficulty::Medium,
            'createdAt' => new \DateTimeImmutable('2024-01-16 10:00:00'),
            'translations' => [
                PostTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Article FR Seulement',
                    'slug' => 'article-fr-seulement',
                    'metaDescription' => str_repeat('E', 120),
                    'excerpt' => 'Cet article existe uniquement en français',
                ]),
            ],
        ]));
    }

    public function getPostWithBothLocales(): Post
    {
        return self::get('postWithBothLocales')->_real();
    }

    public function getPostWithFrenchOnly(): Post
    {
        return self::get('postWithFrenchOnly')->_real();
    }
}
