import { expect, type Locator, type Page } from '@playwright/test';
import { RecipeShowPage } from './RecipeShowPage';

/**
 * POM for the public recipe index page (/fr/recettes, /en/recipes).
 */
export class RecipeIndexPage {
  readonly page: Page;
  readonly recipeCards: Locator;

  constructor(page: Page) {
    this.page = page;
    this.recipeCards = page.getByTestId(/^recipe-card-/);
  }

  async goto(locale: 'fr' | 'en' = 'fr'): Promise<void> {
    const path = locale === 'fr' ? '/fr/recettes' : '/en/recipes';
    await this.page.goto(path);
    await expect(this.recipeCards.first()).toBeVisible();
  }

  /**
   * Clicks the first recipe card and returns a POM for the recipe show page.
   * The returned POM has its `title` already visible.
   */
  async openFirstRecipe(): Promise<RecipeShowPage> {
    await this.recipeCards.first().click();
    const showPage = new RecipeShowPage(this.page);
    await expect(showPage.title).toBeVisible();
    return showPage;
  }
}
