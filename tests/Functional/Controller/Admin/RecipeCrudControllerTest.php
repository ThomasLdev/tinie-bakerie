<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin;

use App\Controller\Admin\RecipeCrudController;
use App\Entity\Recipe;
use App\EventSubscriber\LocaleFilterSubscriber;
use App\Factory\CategoryFactory;
use App\Factory\CategoryTranslationFactory;
use App\Factory\RecipeFactory;
use App\Factory\RecipeTranslationFactory;
use App\Form\Type\IngredientGroupType;
use App\Form\Type\IngredientGroupTranslationType;
use App\Form\Type\IngredientTranslationType;
use App\Form\Type\IngredientType;
use App\Form\Type\PostMediaType;
use App\Form\Type\PostMediaTranslationType;
use App\Form\Type\RecipeStepTranslationType;
use App\Form\Type\RecipeStepType;
use App\Form\Type\RecipeTranslationType;
use App\Services\Locale\Locales;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Smoke tests for Recipe CRUD controller in EasyAdmin.
 * Covers index, new, edit, detail and method restrictions.
 *
 * @internal
 */
#[CoversClass(RecipeCrudController::class)]
#[CoversClass(Locales::class)]
#[CoversClass(LocaleFilterSubscriber::class)]
#[CoversClass(RecipeTranslationType::class)]
#[CoversClass(IngredientGroupType::class)]
#[CoversClass(IngredientGroupTranslationType::class)]
#[CoversClass(IngredientType::class)]
#[CoversClass(IngredientTranslationType::class)]
#[CoversClass(RecipeStepType::class)]
#[CoversClass(RecipeStepTranslationType::class)]
#[CoversClass(PostMediaType::class)]
#[CoversClass(PostMediaTranslationType::class)]
final class RecipeCrudControllerTest extends WebTestCase
{
    use Factories;
    use ResetDatabase;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testRecipeEntityName(): void
    {
        self::assertSame(Recipe::class, RecipeCrudController::getEntityFqcn());
    }

    public function testIndexPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/recipe');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
        self::assertSelectorExists('a[href*="/admin/recipe/new"]');
    }

    public function testIndexPageShowsMultipleRecipes(): void
    {
        $category = $this->createCategory();
        RecipeFactory::createMany(3, fn () => [
            'category' => $category,
            'translations' => $this->createRecipeTranslations(),
        ]);

        $this->client->request('GET', '/admin/recipe');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.table');
    }

    public function testNewPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/admin/recipe/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testEditPageLoadsSuccessfully(): void
    {
        $recipe = $this->createRecipe();

        $this->client->request('GET', "/admin/recipe/{$recipe->getId()}/edit");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testEditPageWithNonExistentRecipeReturns404(): void
    {
        $this->client->request('GET', '/admin/recipe/99999/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDetailPageLoadsSuccessfully(): void
    {
        $recipe = $this->createRecipe();

        $this->client->request('GET', "/admin/recipe/{$recipe->getId()}");

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href*="/edit"]');
    }

    public function testDetailPageWithNonExistentRecipeReturns404(): void
    {
        $this->client->request('GET', '/admin/recipe/99999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testIndexOnlyAcceptsGetRequests(): void
    {
        $this->client->request('POST', '/admin/recipe');

        self::assertResponseStatusCodeSame(405);
    }

    public function testDetailOnlyAcceptsGetRequests(): void
    {
        $recipe = $this->createRecipe();

        $this->client->request('POST', "/admin/recipe/{$recipe->getId()}");

        self::assertResponseStatusCodeSame(405);
    }

    private function createRecipe(): Recipe
    {
        return RecipeFactory::createOne([
            'category' => $this->createCategory(),
            'translations' => $this->createRecipeTranslations(),
        ]);
    }

    private function createCategory(): \App\Entity\Category
    {
        return CategoryFactory::createOne([
            'translations' => [
                CategoryTranslationFactory::new([
                    'locale' => 'fr',
                    'title' => 'Catégorie Admin Test FR',
                    'slug' => 'categorie-admin-test-fr',
                    'metaDescription' => str_repeat('A', 120),
                    'excerpt' => 'Catégorie admin',
                ]),
                CategoryTranslationFactory::new([
                    'locale' => 'en',
                    'title' => 'Admin Test Category EN',
                    'slug' => 'admin-test-category-en',
                    'metaDescription' => str_repeat('B', 120),
                    'excerpt' => 'Admin category',
                ]),
            ],
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    private function createRecipeTranslations(): array
    {
        $suffix = bin2hex(random_bytes(4));

        return [
            RecipeTranslationFactory::new([
                'locale' => 'fr',
                'title' => "Recette Admin {$suffix} FR",
                'slug' => "recette-admin-{$suffix}-fr",
                'metaDescription' => str_repeat('C', 120),
                'excerpt' => 'Recette admin',
            ]),
            RecipeTranslationFactory::new([
                'locale' => 'en',
                'title' => "Admin Recipe {$suffix} EN",
                'slug' => "admin-recipe-{$suffix}-en",
                'metaDescription' => str_repeat('D', 120),
                'excerpt' => 'Admin recipe',
            ]),
        ];
    }
}
