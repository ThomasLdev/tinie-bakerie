<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\RecipeController;
use App\EventSubscriber\LocaleFilterSubscriber;
use App\Repository\RecipeRepository;
use App\Services\Filter\LocaleFilter;
use App\Tests\Story\RecipeControllerTestStory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[CoversClass(RecipeController::class)]
#[CoversClass(RecipeRepository::class)]
#[CoversClass(LocaleFilter::class)]
#[CoversClass(LocaleFilterSubscriber::class)]
final class RecipeControllerTest extends BaseControllerTestCase
{
    private const string BASE_URL_FR = '/fr/recettes';

    private const string BASE_URL_EN = '/en/recipes';

    /**
     * @param array<string> $expectedTitles
     */
    #[DataProvider('getRecipeControllerIndexData')]
    public function testIndex(array $expectedTitles, string $baseUrl): void
    {
        /** @var RecipeControllerTestStory $story */
        $story = $this->loadStory(static fn (): RecipeControllerTestStory => RecipeControllerTestStory::load());
        $activeRecipes = $story->getActiveRecipes();
        $activeRecipesCount = \count($activeRecipes);

        $crawler = $this->client->request(Request::METHOD_GET, $baseUrl);

        self::assertResponseIsSuccessful();

        $recipeCards = $crawler->filter('[data-test-id^="recipe-card-"]');
        self::assertCount(
            $activeRecipesCount,
            $recipeCards,
            \sprintf('Expected %s active recipes to be displayed on the index page', $activeRecipesCount),
        );

        $html = $crawler->html();

        foreach ($expectedTitles as $title) {
            self::assertStringContainsString($title, $html, \sprintf('Recipe title "%s" should be present', $title));
        }

        $firstPos = strpos($html, (string) $expectedTitles[0]);
        $secondPos = strpos($html, (string) $expectedTitles[1]);

        self::assertLessThan(
            $secondPos,
            $firstPos,
            \sprintf(
                'Recipe "%s" (newer) should appear before "%s" (older) in HTML (ordered by createdAt DESC)',
                $expectedTitles[0],
                $expectedTitles[1],
            ),
        );
    }

    #[DataProvider('getRecipeControllerShowData')]
    public function testShowWithFoundRecipe(string $expected, string $locale, string $baseUrl): void
    {
        /** @var RecipeControllerTestStory $story */
        $story = $this->loadStory(static fn (): RecipeControllerTestStory => RecipeControllerTestStory::load());
        $recipe = $story->getActiveRecipe(0);
        $recipeSlug = $story->getRecipeSlug($recipe, $locale);
        $category = $recipe->getCategory();
        self::assertNotNull($category, 'Recipe should have a category');
        $categorySlug = $story->getCategorySlug($category, $locale);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s/%s', $baseUrl, $categorySlug, $recipeSlug),
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('[data-test-id="recipe-show-title"]', $expected);
    }

    #[DataProvider('getRecipeControllerShowNotFoundData')]
    public function testShowRendersIngredientsAndSteps(string $locale, string $baseUrl): void
    {
        /** @var RecipeControllerTestStory $story */
        $story = $this->loadStory(static fn (): RecipeControllerTestStory => RecipeControllerTestStory::load());
        $recipe = $story->getRecipeWithIngredientsAndSteps();
        $recipeSlug = $story->getRecipeSlug($recipe, $locale);
        $category = $recipe->getCategory();
        self::assertNotNull($category, 'Recipe should have a category');
        $categorySlug = $story->getCategorySlug($category, $locale);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s/%s', $baseUrl, $categorySlug, $recipeSlug),
        );

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('[data-test-id="recipe-ingredients"]'));
        self::assertCount(
            $story->getIngredientCount(),
            $crawler->filter('[data-test-id^="recipe-ingredient-check-"]'),
        );
        self::assertCount(
            $story->getStepCount(),
            $crawler->filter('[data-test-id^="recipe-step-check-"]'),
        );
        self::assertCount(1, $crawler->filter('[data-test-id="portions-decrease"]'));
        self::assertCount(1, $crawler->filter('[data-test-id="portions-value"]'));
        self::assertCount(1, $crawler->filter('[data-test-id="portions-increase"]'));
    }

    #[DataProvider('getRecipeControllerShowNotFoundData')]
    public function testShowWithInactiveRecipe(string $locale, string $baseUrl): void
    {
        /** @var RecipeControllerTestStory $story */
        $story = $this->loadStory(static fn (): RecipeControllerTestStory => RecipeControllerTestStory::load());
        $recipe = $story->getInactiveRecipe();
        $recipeSlug = $story->getRecipeSlug($recipe, $locale);
        $category = $recipe->getCategory();
        self::assertNotNull($category, 'Recipe should have a category');
        $categorySlug = $story->getCategorySlug($category, $locale);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s/%s', $baseUrl, $categorySlug, $recipeSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testShowWithNotFoundRecipe(): void
    {
        foreach ([self::BASE_URL_FR, self::BASE_URL_EN] as $baseUrl) {
            $this->client->request(
                Request::METHOD_GET,
                \sprintf('%s/bad-category-slug/%s', $baseUrl, 'unknown-category/unknown-recipe'),
            );

            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        }
    }

    #[DataProvider('getRecipeControllerShowNotFoundData')]
    public function testShowWithBadCategorySlug(string $locale, string $baseUrl): void
    {
        /** @var RecipeControllerTestStory $story */
        $story = $this->loadStory(static fn (): RecipeControllerTestStory => RecipeControllerTestStory::load());
        $recipe = $story->getActiveRecipe(0);
        $recipeSlug = $story->getRecipeSlug($recipe, $locale);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/bad-category-slug/%s', $baseUrl, $recipeSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[DataProvider('getRecipeControllerBaseUrlData')]
    public function testIndexWithNoRecipes(string $baseUrl): void
    {
        $this->client->request(Request::METHOD_GET, $baseUrl);

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('[data-test-id^="recipe-card-"]');
    }

    #[DataProvider('getHttpMethodsData')]
    public function testIndexRejectsNonGetMethods(string $method, string $baseUrl): void
    {
        $this->client->request($method, $baseUrl);

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    #[DataProvider('getHttpMethodsData')]
    public function testShowRejectsNonGetMethods(string $method, string $baseUrl): void
    {
        /** @var RecipeControllerTestStory $story */
        $story = $this->loadStory(static fn (): RecipeControllerTestStory => RecipeControllerTestStory::load());
        $recipe = $story->getActiveRecipe(0);
        $locale = $baseUrl === self::BASE_URL_FR ? 'fr' : 'en';
        $recipeSlug = $story->getRecipeSlug($recipe, $locale);
        $category = $recipe->getCategory();
        self::assertNotNull($category, 'Recipe should have a category');
        $categorySlug = $story->getCategorySlug($category, $locale);

        $this->client->request(
            $method,
            \sprintf('%s/%s/%s', $baseUrl, $categorySlug, $recipeSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public static function getRecipeControllerIndexData(): \Generator
    {
        yield 'should find two recipes on fr recipe index' => [['Recette Test 2 FR', 'Recette Test 1 FR'], self::BASE_URL_FR];

        yield 'should find two recipes on en recipe index' => [['Test Recipe 2 EN', 'Test Recipe 1 EN'], self::BASE_URL_EN];
    }

    public static function getRecipeControllerShowData(): \Generator
    {
        yield 'should find fr title on recipe fr page' => ['Recette Test 1 FR', 'fr', self::BASE_URL_FR];

        yield 'should find en title on recipe en page' => ['Test Recipe 1 EN', 'en', self::BASE_URL_EN];
    }

    public static function getRecipeControllerShowNotFoundData(): \Generator
    {
        yield 'fr recipe base url' => ['fr', self::BASE_URL_FR];

        yield 'en recipe base url' => ['en', self::BASE_URL_EN];
    }

    public static function getRecipeControllerBaseUrlData(): \Generator
    {
        yield 'fr base url' => [self::BASE_URL_FR];

        yield 'en base url' => [self::BASE_URL_EN];
    }

    public static function getHttpMethodsData(): \Generator
    {
        yield 'POST method on fr url' => [Request::METHOD_POST, self::BASE_URL_FR];

        yield 'POST method on en url' => [Request::METHOD_POST, self::BASE_URL_EN];

        yield 'PUT method on fr url' => [Request::METHOD_PUT, self::BASE_URL_FR];

        yield 'PUT method on en url' => [Request::METHOD_PUT, self::BASE_URL_EN];

        yield 'DELETE method on fr url' => [Request::METHOD_DELETE, self::BASE_URL_FR];

        yield 'DELETE method on en url' => [Request::METHOD_DELETE, self::BASE_URL_EN];

        yield 'PATCH method on fr url' => [Request::METHOD_PATCH, self::BASE_URL_FR];

        yield 'PATCH method on en url' => [Request::METHOD_PATCH, self::BASE_URL_EN];
    }

    /**
     * CRITICAL TEST: Verify controller rejects cross-locale slug combinations.
     * French URL with English slug must return 404 (and vice versa).
     */
    #[DataProvider('getCrossLocaleSlugData')]
    public function testShowRejectsCrossLocaleSlugCombination(string $urlLocale, string $urlBase, string $slugLocale): void
    {
        /** @var RecipeControllerTestStory $story */
        $story = $this->loadStory(static fn (): RecipeControllerTestStory => RecipeControllerTestStory::load());
        $recipe = $story->getActiveRecipe(0);
        $category = $recipe->getCategory();
        self::assertNotNull($category, 'Recipe should have a category');

        $categorySlug = $story->getCategorySlug($category, $slugLocale);
        $recipeSlug = $story->getRecipeSlug($recipe, $slugLocale);

        $this->client->request(
            Request::METHOD_GET,
            \sprintf('%s/%s/%s', $urlBase, $categorySlug, $recipeSlug),
        );

        self::assertResponseStatusCodeSame(
            Response::HTTP_NOT_FOUND,
            \sprintf(
                'Should reject %s URL with %s slug combination (%s/%s/%s)',
                $urlLocale,
                $slugLocale,
                $urlBase,
                $categorySlug,
                $recipeSlug,
            ),
        );
    }

    public static function getCrossLocaleSlugData(): \Generator
    {
        yield 'French URL with English slug' => ['fr', self::BASE_URL_FR, 'en'];

        yield 'English URL with French slug' => ['en', self::BASE_URL_EN, 'fr'];
    }
}
