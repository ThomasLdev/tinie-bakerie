# E2E Tests with Playwright

This directory contains End-to-End (E2E) tests using [Playwright](https://playwright.dev/) running in Docker.

## Purpose

E2E tests cover the "dead angles" that can't be tested with PHPUnit:
- **JavaScript interactions**: Form collection items added dynamically (e.g., "Add Media" button)
- **Full integration**: Complete flow from UI to database with all cascades and relationships

**Note**: Most validation and business logic is tested in faster PHPUnit tests:
- `tests/Form/Type/*` - Form validation and data mapping (unit tests)
- `tests/Integration/*` - Database persistence and relationships (integration tests)

## Setup

### 1. Install Dependencies (First Time Only)

```bash
make e2e-install
```

This installs Playwright and its dependencies inside the Docker container.

### 2. Ensure Your App is Running

Make sure your Symfony application is up and running:

```bash
make up
```

### 3. Load Test Fixtures

E2E tests require test data (categories, users, etc.):

```bash
make fixtures
```

## Running Tests

### Run All E2E Tests

```bash
make e2e
```

### View Test Report (BEST for visual debugging) ⭐

```bash
make e2e-report
```

This opens an interactive HTML report on http://localhost:9323 showing:
- ✅ Test results with pass/fail status
- 📸 Screenshots on failure
- 🎬 Videos of test execution
- 📊 Test timeline and duration
- 🔍 **Trace viewer** with DOM snapshots at each step

**This is the best way to debug E2E tests in Docker!**

### View Trace for Failed Tests

```bash
make e2e-show-trace
```

Opens the trace viewer specifically for failed tests, showing:
- Step-by-step actions
- DOM snapshots
- Network requests
- Console logs

### Run Tests in Debug Mode

```bash
make e2e-debug
```

Enables debug mode with `PWDEBUG=1` (pauses on failures).

### Run Tests with Visible Browser

```bash
make e2e-headed
```

Shows the browser window (not headless mode).

### View Test Report

After tests run, view the HTML report:

```bash
make e2e-report
```

## Running All Tests

Run PHPUnit + E2E tests together:

```bash
make test-all
```

## Writing New Tests

### Test Structure

```typescript
import { test, expect } from '@playwright/test';

test.describe('Feature Name', () => {
  
  test.beforeEach(async ({ page }) => {
    // Setup: Load fixtures, login, etc.
  });

  test('should do something', async ({ page }) => {
    // 1. Navigate
    await page.goto('/admin/post/new');
    
    // 2. Interact
    await page.fill('input[name="title"]', 'Test');
    await page.click('button[type="submit"]');
    
    // 3. Assert
    await expect(page).toHaveURL(/\/admin\?/);
  });
});
```

### Best Practices

1. **Test JavaScript-dependent features only**
   - Don't duplicate what PHPUnit tests already cover
   - Focus on dynamic UI interactions

2. **Use data-* attributes for selectors**
   - More stable than CSS classes
   - Example: `data-testid="add-media-button"`

3. **Wait for elements properly**
   ```typescript
   await page.waitForSelector('.media-item');
   await expect(page.locator('.success')).toBeVisible();
   ```

4. **Keep tests independent**
   - Each test should be able to run alone
   - Don't rely on test execution order

5. **Use descriptive test names**
   ```typescript
   test('should create post with multiple media items')
   ```

## Debugging Failed Tests

### View Screenshots

Playwright automatically takes screenshots on failure:
```
test-results/
  ├── test-name-chromium/
  │   ├── test-failed-1.png
  │   └── trace.zip
```

### View Trace

Open the trace viewer to see step-by-step execution:
```bash
npx playwright show-trace test-results/trace.zip
```

### Common Issues

**Issue**: Tests can't connect to the app
- **Solution**: Make sure `make up` is running
- **Check**: `http://php:80` is accessible from the playwright container

**Issue**: Selectors not found
- **Solution**: Inspect the actual form HTML in EasyAdmin
- **Tip**: Use Playwright Inspector (`make e2e-debug`)

**Issue**: Tests are flaky
- **Solution**: Add proper waits
  ```typescript
  await page.waitForLoadState('networkidle');
  await expect(page.locator('.element')).toBeVisible();
  ```

## Configuration

Edit `playwright.config.ts` to:
- Change browser (chromium/firefox/webkit)
- Adjust timeouts
- Configure parallel execution
- Set up video recording

## Architecture

```
├── tests/e2e/                  # E2E tests (this directory)
│   ├── post-crud.spec.ts       # Post creation with media
│   └── search.spec.ts          # Future: Search functionality
├── playwright.config.ts         # Playwright configuration
├── package.json                 # Node dependencies
└── compose.yaml                 # Playwright service definition
```

## Docker Details

The Playwright service:
- Uses official Microsoft Playwright image
- Runs in the same network as your app
- Accesses the app via `http://php:80`
- Only starts when explicitly called (profile: testing)

## Continuous Integration

To run E2E tests in CI:

```yaml
# .github/workflows/test.yml (example)
- name: Run E2E tests
  run: make e2e
```

## Resources

- [Playwright Documentation](https://playwright.dev/docs/intro)
- [Playwright Best Practices](https://playwright.dev/docs/best-practices)
- [Debugging Tests](https://playwright.dev/docs/debug)
- [Writing Tests](https://playwright.dev/docs/writing-tests)
