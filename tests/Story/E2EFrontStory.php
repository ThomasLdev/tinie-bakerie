<?php

declare(strict_types=1);

namespace App\Tests\Story;

use App\Factory\CategoryFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\IngredientFactory;
use App\Factory\IngredientGroupFactory;
use App\Factory\IngredientGroupTranslationFactory;
use App\Factory\IngredientTranslationFactory;
use App\Factory\RecipeFactory;
use App\Factory\RecipeStepFactory;
use App\Factory\RecipeStepTranslationFactory;
use App\Factory\RecipeTranslationFactory;
use App\Services\Post\Enum\Difficulty;
use App\Services\PostSection\Enum\PostSectionType;
use Zenstruck\Foundry\Story;

/**
 * Deterministic dataset for the public-front Playwright suite.
 *
 * Loaded by `bin/console app:e2e:reset` against the `app_test` database, so slugs,
 * counts and servings stay stable between runs and between workers. Tests can
 * `goto('/fr/recettes/<categorySlug>/<recipeSlug>')` directly without scanning
 * the index for "the first card".
 */
final class E2EFrontStory extends Story
{
    public const string CATEGORY_SLUG_FR = 'e2e-categorie';

    public const string CATEGORY_SLUG_EN = 'e2e-category';

    public const string RECIPE_FULL_SLUG_FR = 'e2e-recette-pleine';

    public const string RECIPE_FULL_SLUG_EN = 'e2e-recipe-full';

    public const int RECIPE_FULL_SERVINGS = 4;

    public const int RECIPE_FULL_INGREDIENTS = 8;

    public const int RECIPE_FULL_STEPS = 3;

    public const int PORTIONS_MIN = 1;

    public const int PORTIONS_MAX = 24;

    public function build(): void
    {
        $category = CategoryFactory::createOne([
            'isFeatured' => true,
            'translations' => [
                CategoryTranslationFactory::new([
                    'locale' => 'fr',
                    // Slug is generated from title by CategoryTranslationFactory::initialize().
                    'title' => 'E2E Categorie',
                    'metaDescription' => str_repeat('A', 120),
                    'excerpt' => 'Catégorie utilisée par la suite Playwright.',
                ]),
                CategoryTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'E2E Category',
                    'metaDescription' => str_repeat('B', 120),
                    'excerpt' => 'Category used by the Playwright suite.',
                ]),
            ],
        ]);

        $recipe = RecipeFactory::createOne([
            'active' => true,
            'category' => $category,
            'cookingTime' => 30,
            'preparationTime' => 15,
            'difficulty' => Difficulty::Easy,
            'servings' => self::RECIPE_FULL_SERVINGS,
            'createdAt' => new \DateTimeImmutable('2026-01-01 10:00:00'),
            'translations' => [
                RecipeTranslationFactory::new([
                    'locale' => 'fr',
                    // Slug is generated from title by RecipeTranslationFactory::initialize().
                    'title' => 'E2E Recette Pleine',
                    'metaDescription' => str_repeat('C', 120),
                    'excerpt' => 'Recette utilisée par la suite Playwright.',
                ]),
                RecipeTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'E2E Recipe Full',
                    'metaDescription' => str_repeat('D', 120),
                    'excerpt' => 'Recipe used by the Playwright suite.',
                ]),
            ],
        ]);

        $group = IngredientGroupFactory::createOne([
            'recipe' => $recipe,
            'position' => 0,
            'translations' => [
                IngredientGroupTranslationFactory::new(['locale' => 'fr', 'label' => 'Pour la recette']),
                IngredientGroupTranslationFactory::new(['locale' => 'en', 'label' => 'For the recipe']),
            ],
        ]);

        for ($i = 0; $i < self::RECIPE_FULL_INGREDIENTS; ++$i) {
            IngredientFactory::createOne([
                'group' => $group,
                'position' => $i,
                'baseQuantity' => 100.0 + $i * 10,
                'translations' => [
                    IngredientTranslationFactory::new([
                        'locale' => 'fr',
                        'name' => \sprintf('Ingrédient FR %d', $i + 1),
                        'unit' => 'g',
                    ]),
                    IngredientTranslationFactory::new([
                        'locale' => 'en',
                        'name' => \sprintf('Ingredient EN %d', $i + 1),
                        'unit' => 'g',
                    ]),
                ],
            ]);
        }

        for ($i = 0; $i < self::RECIPE_FULL_STEPS; ++$i) {
            RecipeStepFactory::createOne([
                'post' => $recipe,
                'position' => $i,
                'type' => PostSectionType::Default,
                'translations' => [
                    RecipeStepTranslationFactory::new([
                        'locale' => 'fr',
                        'title' => \sprintf('Étape FR %d', $i + 1),
                        'content' => \sprintf('Contenu de l\'étape %d.', $i + 1),
                    ]),
                    RecipeStepTranslationFactory::new([
                        'locale' => 'en',
                        'title' => \sprintf('Step EN %d', $i + 1),
                        'content' => \sprintf('Step %d content.', $i + 1),
                    ]),
                ],
            ]);
        }
    }
}
