<?php

declare(strict_types=1);

namespace App\Tests\Story;

use App\Entity\Category;
use App\Entity\Recipe;
use App\Factory\CategoryFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\PostMediaFactory;
use App\Factory\PostMediaTranslationFactory;
use App\Factory\RecipeFactory;
use App\Factory\RecipeTranslationFactory;
use App\Services\Post\Enum\Difficulty;
use Zenstruck\Foundry\Story;

/**
 * Story for RecipeController functional tests.
 * Provides predictable test data for testing recipe display functionality.
 */
final class RecipeControllerTestStory extends Story
{
    public function build(): void
    {
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

        $this->addState('activeRecipe1', RecipeFactory::createOne([
            'active' => true,
            'category' => self::get('category'),
            'cookingTime' => 30,
            'difficulty' => Difficulty::Easy,
            'createdAt' => new \DateTimeImmutable('2024-01-01 10:00:00'),
            'translations' => [
                RecipeTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Recette Test 1 FR',
                    'slug' => 'recette-test-1-fr',
                    'metaDescription' => str_repeat('C', 120),
                    'excerpt' => 'Première recette de test',
                ]),
                RecipeTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Test Recipe 1 EN',
                    'slug' => 'test-recipe-1-en',
                    'metaDescription' => str_repeat('D', 120),
                    'excerpt' => 'First test recipe',
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
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

        $this->addState('activeRecipe2', RecipeFactory::createOne([
            'active' => true,
            'category' => self::get('category'),
            'cookingTime' => 45,
            'difficulty' => Difficulty::Medium,
            'createdAt' => new \DateTimeImmutable('2024-01-02 10:00:00'),
            'translations' => [
                RecipeTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Recette Test 2 FR',
                    'slug' => 'recette-test-2-fr',
                    'metaDescription' => str_repeat('E', 120),
                    'excerpt' => 'Deuxième recette de test',
                ]),
                RecipeTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Test Recipe 2 EN',
                    'slug' => 'test-recipe-2-en',
                    'metaDescription' => str_repeat('F', 120),
                    'excerpt' => 'Second test recipe',
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
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

        $this->addState('inactiveRecipe', RecipeFactory::createOne([
            'active' => false,
            'category' => self::get('category'),
            'cookingTime' => 60,
            'difficulty' => Difficulty::Advanced,
            'translations' => [
                RecipeTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Recette Inactive FR',
                    'slug' => 'recette-inactive-fr',
                    'metaDescription' => str_repeat('G', 120),
                    'excerpt' => 'Recette inactive',
                ]),
                RecipeTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Inactive Recipe EN',
                    'slug' => 'inactive-recipe-en',
                    'metaDescription' => str_repeat('H', 120),
                    'excerpt' => 'Inactive recipe',
                ]),
            ],
            'media' => [
                PostMediaFactory::new([
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

    /**
     * @return list<Recipe>
     */
    public function getActiveRecipes(): array
    {
        return [
            $this->getRecipe('activeRecipe1'),
            $this->getRecipe('activeRecipe2'),
        ];
    }

    public function getActiveRecipe(int $index): Recipe
    {
        return $this->getActiveRecipes()[$index];
    }

    public function getInactiveRecipe(): Recipe
    {
        return $this->getRecipe('inactiveRecipe');
    }

    public function getCategorySlug(Category $category, string $locale): string
    {
        return $category->getTranslationByLocale($locale)?->getSlug() ?? '';
    }

    public function getRecipeSlug(Recipe $recipe, string $locale): string
    {
        return $recipe->getTranslationByLocale($locale)?->getSlug() ?? '';
    }

    private function getRecipe(string $key): Recipe
    {
        $recipe = self::get($key);
        \assert($recipe instanceof Recipe);

        return $recipe;
    }
}
