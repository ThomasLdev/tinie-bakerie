import { test, expect } from '@playwright/test';

test.describe('Configuration Validation', () => {
  test('should load the homepage', async ({ page }) => {
    // Navigate to homepage
    await page.goto('/');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check that we got a successful response
    expect(page.url()).toContain('http://php');
    
    // Check that the page has some content
    const body = await page.locator('body');
    await expect(body).toBeVisible();
  });

  test('should have proper page title', async ({ page }) => {
    await page.goto('/');
    
    // Just check that there's a title tag (not empty)
    const title = await page.title();
    expect(title.length).toBeGreaterThan(0);
  });
});
