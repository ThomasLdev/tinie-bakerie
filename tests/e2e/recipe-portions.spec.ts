import { expect, test } from '@playwright/test';
import { E2E_FIXTURES } from './fixtures';
import { RecipeShowPage } from './pages/RecipeShowPage';

test.describe('Recipe portions widget', () => {
  let recipe: RecipeShowPage;

  test.beforeEach(async ({ page }) => {
    recipe = new RecipeShowPage(page);
    await recipe.goto(E2E_FIXTURES.category.fr, E2E_FIXTURES.recipeFull.fr);
  });

  test('increments the displayed portions when the increase button is tapped', async () => {
    await expect(recipe.portionsValue).toHaveText(String(E2E_FIXTURES.recipeFull.servings));

    await recipe.increasePortions();

    await expect(recipe.portionsValue).toHaveText(String(E2E_FIXTURES.recipeFull.servings + 1));
  });

  test('decrements the displayed portions when the decrease button is tapped', async () => {
    await expect(recipe.portionsValue).toHaveText(String(E2E_FIXTURES.recipeFull.servings));

    await recipe.decreasePortions();

    await expect(recipe.portionsValue).toHaveText(String(E2E_FIXTURES.recipeFull.servings - 1));
  });

  test('disables the decrease button when the minimum is reached', async () => {
    await recipe.decreaseToMinimum();

    await expect(recipe.portionsDecrease).toBeDisabled();
    await expect(recipe.portionsValue).toHaveText(String(E2E_FIXTURES.portions.min));
  });

  test('disables the increase button when the maximum is reached', async () => {
    await recipe.increaseToMaximum();

    await expect(recipe.portionsIncrease).toBeDisabled();
    await expect(recipe.portionsValue).toHaveText(String(E2E_FIXTURES.portions.max));
  });
});
