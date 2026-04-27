import { expect, type Locator, type Page } from '@playwright/test';

/**
 * POM for the public recipe show page (/fr/recettes/<category>/<recipe>).
 * Encapsulates the title, ingredients checklist and portions widget.
 */
export class RecipeShowPage {
  readonly page: Page;

  // Header
  readonly title: Locator;

  // Ingredients checklist (Stimulus: recipe-checklist)
  readonly ingredientsBlock: Locator;
  readonly ingredientChecks: Locator;
  readonly ingredientLabels: Locator;
  readonly ingredientsCount: Locator;
  readonly ingredientsReset: Locator;

  // Recipe steps (Stimulus: recipe-checklist via the steps section)
  readonly stepChecks: Locator;

  // Portions widget (Stimulus: recipe-portions)
  readonly portionsValue: Locator;
  readonly portionsDecrease: Locator;
  readonly portionsIncrease: Locator;

  constructor(page: Page) {
    this.page = page;
    this.title = page.getByTestId('recipe-show-title');
    this.ingredientsBlock = page.getByTestId('recipe-ingredients');
    this.ingredientChecks = page.getByTestId(/^recipe-ingredient-check-/);
    this.ingredientLabels = page.getByTestId(/^recipe-ingredient-label-/);
    this.ingredientsCount = page.getByTestId('recipe-ingredients-count');
    this.ingredientsReset = page.getByTestId('recipe-ingredients-reset');
    this.stepChecks = page.getByTestId(/^recipe-step-check-/);
    this.portionsValue = page.getByTestId('portions-value');
    this.portionsDecrease = page.getByTestId('portions-decrease');
    this.portionsIncrease = page.getByTestId('portions-increase');
  }

  /**
   * Navigates directly to a recipe by category + recipe slug.
   */
  async goto(categorySlug: string, recipeSlug: string, locale: 'fr' | 'en' = 'fr'): Promise<void> {
    const base = locale === 'fr' ? '/fr/recettes' : '/en/recipes';
    await this.page.goto(`${base}/${categorySlug}/${recipeSlug}`);
    await expect(this.title).toBeVisible();
  }

  async currentPortions(): Promise<number> {
    const text = await this.portionsValue.textContent();
    return Number(text);
  }

  async increasePortions(): Promise<void> {
    await this.portionsIncrease.click();
  }

  async decreasePortions(): Promise<void> {
    await this.portionsDecrease.click();
  }

  /**
   * Clicks decrease until the minimum is reached.
   */
  async decreaseToMinimum(): Promise<void> {
    while (!(await this.portionsDecrease.isDisabled())) {
      await this.portionsDecrease.click();
    }
  }

  /**
   * Clicks increase until the maximum is reached.
   */
  async increaseToMaximum(): Promise<void> {
    while (!(await this.portionsIncrease.isDisabled())) {
      await this.portionsIncrease.click();
    }
  }

  async checkFirstIngredient(): Promise<void> {
    // Tap the visible label rather than the visually-hidden checkbox.
    // Mirrors the real mobile interaction: the label is the user's tap target.
    await this.ingredientLabels.first().click();
  }

  async resetIngredients(): Promise<void> {
    await this.ingredientsReset.click();
  }
}
