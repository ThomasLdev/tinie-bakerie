/**
 * Mirrors `App\Tests\Story\E2EFrontStory` (PHP). When you change a slug or a
 * count there, change it here too — these constants are the contract between
 * the deterministic dataset loaded into `app_test` and the Playwright suite.
 */
export const E2E_FIXTURES = {
  category: {
    fr: 'e2e-categorie',
    en: 'e2e-category',
  },
  recipeFull: {
    fr: 'e2e-recette-pleine',
    en: 'e2e-recipe-full',
    servings: 4,
    ingredients: 8,
    steps: 3,
  },
  portions: {
    min: 1,
    max: 24,
  },
} as const;
