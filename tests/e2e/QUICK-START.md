# E2E Testing Quick Start

## First Time Setup

```bash
# Install Playwright
make e2e-install

# Clear Symfony cache (ensure form theme loads)
docker compose exec php bin/console cache:clear
```

## Running Tests

```bash
# Run all tests
make e2e

# View results (opens interactive report)
make e2e-report

# Debug tests (step-by-step)
make e2e-debug
```

## Writing a New Test

### 1. Use Existing POM

```typescript
import { PostFormPage } from './pages/PostFormPage';

test('my new test', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    await postForm.createMinimalPost({
        cookingTime: '30',
        difficulty: 'easy',
        categoryIndex: 1,
        titleFr: 'Test FR',
        titleEn: 'Test EN'
    });
});
```

### 2. Or Use Individual Methods

```typescript
test('my custom flow', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    await postForm.navigateToNew();
    await postForm.fillBasicFields({ ... });
    await postForm.addMediaItem();
    await postForm.fillMediaItem(0, { ... });
    await postForm.submit();
    await postForm.verifySuccess();
});
```

## Finding Selectors

### Option 1: Browser Dev Tools
1. Open form in browser (dev environment)
2. Right-click → Inspect
3. Look for `data-test-id` attribute

### Option 2: Trace Viewer
1. Run tests: `make e2e`
2. Open report: `make e2e-report`
3. Click any test step
4. See DOM snapshot with all `data-test-id` attributes

## Common Patterns

### Fill a form field
```typescript
await postForm.fillField('post-cookingtime', '30');
```

### Select from dropdown
```typescript
await postForm.selectOption('post-difficulty', 'easy');
// Or by index:
await postForm.selectOption('post-category', { index: 1 });
```

### Check/uncheck checkbox
```typescript
await postForm.check('post-active');
await postForm.uncheck('post-active');
```

### Wait for element
```typescript
await postForm.waitForElement('post-media-0-position');
```

### Submit and verify
```typescript
await postForm.submit();
await postForm.verifySuccess();
```

## Troubleshooting

### Test fails with "selector not found"
1. Run `make e2e-report`
2. Click failed test
3. Open trace viewer
4. See actual `data-test-id` in DOM
5. Update POM method

### Form theme not working
```bash
# Clear cache
docker compose exec php bin/console cache:clear

# Verify form theme is registered
grep -n "addFormTheme" src/Controller/Admin/DashboardController.php
```

### No data-test-id in HTML
- Check `APP_ENV=dev` or `APP_ENV=test`
- Production never has test attributes (by design)

## Need Help?

- 📚 [DATA-TEST-ID-GUIDE.md](./DATA-TEST-ID-GUIDE.md) - Complete guide
- 📚 [README.md](./README.md) - E2E testing overview
- 📚 [UI-DEBUGGING.md](./UI-DEBUGGING.md) - Visual debugging
- 🌐 [Playwright Docs](https://playwright.dev/docs/intro)
