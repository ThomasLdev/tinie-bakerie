# E2E Testing Setup with Playwright + Docker

## Quick Start

### 1. First-Time Setup

```bash
# Install Playwright dependencies
make e2e-install
```

This will:
- Install Node.js dependencies (Playwright)
- Download browser binaries
- All inside Docker (no Node.js needed on host!)

### 2. Run E2E Tests

```bash
# Make sure your app is running
make up

# Run E2E tests
make e2e
```

### 3. View Results (Interactive UI!)

```bash
# View HTML report with trace viewer (BEST for debugging!)
make e2e-report
```

This opens an interactive report on **http://localhost:9323** with:
- 📸 Screenshots
- 🎬 Videos
- 🔍 Time-travel debugging (trace viewer)
- 📊 Test timeline

## What Gets Tested?

E2E tests cover **JavaScript-dependent features** that can't be tested with PHPUnit:

✅ **Tested with E2E (Playwright)**:
- Clicking "Add Media" button (JavaScript collection)
- Removing collection items dynamically
- Full UI-to-database integration

✅ **Tested with PHPUnit** (faster):
- Form validation (all edge cases)
- Database persistence and cascades
- Business logic

## Available Commands

```bash
make e2e              # Run all E2E tests
make e2e-report       # View interactive HTML report (BEST!)
make e2e-show-trace   # View trace for failed tests
make e2e-debug        # Run in debug mode
make test-all         # Run PHPUnit + E2E tests
```

## Architecture

```
┌─────────────────────────────────────────┐
│  Playwright Container (Docker)          │
│  - Node.js + Playwright                 │
│  - Chromium browser                     │
│  - Runs tests                           │
└──────────────┬──────────────────────────┘
               │ http://php:80
               ↓
┌─────────────────────────────────────────┐
│  PHP Container (FrankenPHP)             │
│  - Symfony application                  │
│  - Serves the app                       │
└──────────────┬──────────────────────────┘
               │
               ↓
┌─────────────────────────────────────────┐
│  PostgreSQL Container                   │
│  - Test database                        │
└─────────────────────────────────────────┘
```

## Why Docker + Playwright?

✅ No Node.js installation on host  
✅ Consistent environment across team  
✅ Works in CI/CD out of the box  
✅ Isolated from your main development environment  

## Writing Your First Test

See `tests/e2e/post-crud.spec.ts` for examples.

Basic structure:

```typescript
import { test, expect } from '@playwright/test';

test('my feature', async ({ page }) => {
  // Navigate
  await page.goto('/admin/post/new');
  
  // Interact
  await page.click('[data-testid="add-button"]');
  await page.fill('input[name="title"]', 'Test');
  
  // Assert
  await expect(page.locator('.success')).toBeVisible();
});
```

## Troubleshooting

### Tests can't reach the app

**Problem**: `Error: net::ERR_CONNECTION_REFUSED at http://php:80`

**Solution**: Make sure your app is running:
```bash
make up
docker compose ps  # Should show 'php' service running
```

### Selectors not found

**Problem**: `Selector "button.add-media" not found`

**Solution**: Use Playwright Inspector to find correct selectors:
```bash
make e2e-debug
```

### Slow tests

**Problem**: Tests take a long time

**Solution**: 
- E2E tests are inherently slower (browser startup, full stack)
- Use PHPUnit for most tests (much faster)
- Keep E2E tests minimal (2-3 per feature)

## Next Steps

1. ✅ Run `make e2e-install` (first time only)
2. ✅ Run `make e2e` to verify setup
3. ✅ Check `tests/e2e/README.md` for detailed documentation
4. ✅ Write E2E tests for JavaScript-heavy features
5. ✅ Keep using PHPUnit for validation and business logic

## Learning Resources

- 📖 [Playwright Docs](https://playwright.dev/docs/intro)
- 📖 [tests/e2e/README.md](tests/e2e/README.md) - Detailed guide
- 📖 [testing-guide.md](testing-guide.md) - Overall testing strategy
