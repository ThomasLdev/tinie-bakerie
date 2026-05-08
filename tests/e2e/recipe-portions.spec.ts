import { expect, test } from '@playwright/test';
import { RecipeIndexPage } from './pages/RecipeIndexPage';
import { RecipeShowPage } from './pages/RecipeShowPage';

test.describe('Recipe portions widget', () => {
  let recipe: RecipeShowPage;

  test.beforeEach(async ({ page }) => {
    const index = new RecipeIndexPage(page);
    await index.goto();
    recipe = await index.openFirstRecipe();
  });

  test('increments the displayed portions when the increase button is tapped', async () => {
    const before = await recipe.currentPortions();

    await recipe.increasePortions();

    await expect(recipe.portionsValue).toHaveText(String(before + 1));
  });

  test('decrements the displayed portions when the decrease button is tapped', async () => {
    if ((await recipe.currentPortions()) <= 1) {
      await recipe.increasePortions();
    }
    const before = await recipe.currentPortions();

    await recipe.decreasePortions();

    await expect(recipe.portionsValue).toHaveText(String(before - 1));
  });

  test('disables the decrease button when the minimum is reached', async () => {
    await recipe.decreaseToMinimum();

    await expect(recipe.portionsDecrease).toBeDisabled();
    await expect(recipe.portionsValue).toHaveText('1');
  });

  test('disables the increase button when the maximum is reached', async () => {
    await recipe.increaseToMaximum();

    await expect(recipe.portionsIncrease).toBeDisabled();
    await expect(recipe.portionsValue).toHaveText('24');
  });
});
