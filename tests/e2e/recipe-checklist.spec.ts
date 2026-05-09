import { expect, test } from '@playwright/test';
import { E2E_FIXTURES } from './fixtures';
import { RecipeShowPage } from './pages/RecipeShowPage';

test.describe('Recipe ingredients checklist', () => {
  let recipe: RecipeShowPage;

  test.beforeEach(async ({ page }) => {
    recipe = new RecipeShowPage(page);
    await recipe.goto(E2E_FIXTURES.category.fr, E2E_FIXTURES.recipeFull.fr);
    await expect(recipe.ingredientChecks).toHaveCount(E2E_FIXTURES.recipeFull.ingredients);
  });

  test('updates the checked count when an ingredient is ticked', async () => {
    await expect(recipe.ingredientsCount).toHaveText('0');

    await recipe.checkFirstIngredient();

    await expect(recipe.ingredientsCount).toHaveText('1');
  });

  test('resets all checked ingredients when the reset button is tapped', async () => {
    await recipe.checkFirstIngredient();
    await expect(recipe.ingredientsCount).toHaveText('1');

    await recipe.resetIngredients();

    await expect(recipe.ingredientsCount).toHaveText('0');
    await expect(recipe.ingredientChecks.first()).not.toBeChecked();
  });

  test('persists checked ingredients across a page reload', async ({ page }) => {
    await recipe.checkFirstIngredient();
    await expect(recipe.ingredientsCount).toHaveText('1');

    await page.reload();

    const reloaded = new RecipeShowPage(page);
    await expect(reloaded.title).toBeVisible();
    await expect(reloaded.ingredientsCount).toHaveText('1');
    await expect(reloaded.ingredientChecks.first()).toBeChecked();
  });
});
