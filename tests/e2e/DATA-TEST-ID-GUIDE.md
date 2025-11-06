# data-test-id Testing Strategy

## Overview

This project uses `data-test-id` attributes for stable E2E test selectors that:
- ✅ Don't break when CSS classes change
- ✅ Don't break when labels change (i18n)
- ✅ Are self-documenting
- ✅ **Only exist in dev/test** (zero impact on production)

## How It Works

### 1. Form Theme Adds Attributes Automatically

`templates/admin/form_theme.html.twig` extends EasyAdmin's form theme and automatically adds `data-test-id` to all form elements **only in dev/test environments**.

**Production HTML:**
```html
<input name="Post[cookingTime]" />
```

**Dev/Test HTML:**
```html
<input data-test-id="post-cookingtime" name="Post[cookingTime]" />
```

### 2. Naming Convention

Attributes follow this pattern:
```
data-test-id="[entity]-[field]-[index?]"
```

**Examples:**
```html
<input data-test-id="post-cookingtime" />
<select data-test-id="post-difficulty" />
<select data-test-id="post-category" />
<input data-test-id="post-translations-0-title" />
<input data-test-id="post-translations-1-title" />
<input data-test-id="post-media-0-position" />
```

The naming is derived from Symfony's form field names, automatically converted:
- `Post[cookingTime]` → `post-cookingtime`
- `Post[translations][0][title]` → `post-translations-0-title`
- `Post[media][0][type]` → `post-media-0-type`

### 3. Page Object Model (POM)

We use Playwright's Page Object Model pattern to:
- Encapsulate selectors in one place
- Create reusable page interactions
- Make tests readable and maintainable

**File Structure:**
```
tests/e2e/
├── pages/
│   ├── EasyAdminPage.ts       # Base class for all admin pages
│   └── PostFormPage.ts         # Post-specific form interactions
└── post-crud.spec.ts           # Actual tests
```

## Usage in Tests

### Basic Example (Without POM)

```typescript
// ❌ Not recommended: Direct selectors in tests
await page.fill('[data-test-id="post-cookingtime"]', '30');
await page.selectOption('[data-test-id="post-difficulty"]', 'easy');
```

### Better Example (With POM) ⭐

```typescript
// ✅ Recommended: Use Page Object Model
import { PostFormPage } from './pages/PostFormPage';

test('should create post', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    await postForm.fillBasicFields({
        cookingTime: '30',
        difficulty: 'easy',
        categoryIndex: 1
    });
    
    await postForm.submit();
    await postForm.verifySuccess();
});
```

## Adding New Tests

### 1. For Existing Forms

Use the existing POMs:

```typescript
import { PostFormPage } from './pages/PostFormPage';

test('my new test', async ({ page }) => {
    const postForm = new PostFormPage(page);
    await postForm.navigateToNew();
    // Use existing methods
});
```

### 2. For New Forms

Create a new POM:

```typescript
// tests/e2e/pages/CategoryFormPage.ts
import { EasyAdminPage } from './EasyAdminPage';

export class CategoryFormPage extends EasyAdminPage {
    async navigateToNew(): Promise<void> {
        await this.navigateToCrud('App\\Controller\\Admin\\CategoryCrudController', 'new');
    }

    async fillBasicFields(data: { name: string }): Promise<void> {
        await this.fillField('category-name', data.name);
    }
}
```

## Finding Test IDs

### Method 1: Inspect Element (Dev Tools)

1. Run your app in dev mode
2. Open the form in browser
3. Right-click → Inspect
4. Look for `data-test-id` attribute

### Method 2: Playwright Inspector

```bash
make e2e-debug
```

Opens Playwright Inspector where you can:
- Pick locators
- See available `data-test-id` attributes
- Test selectors live

### Method 3: Trace Viewer

```bash
make e2e              # Run tests (some may fail)
make e2e-report       # Open trace viewer
```

Click any test step to see DOM snapshot with all `data-test-id` attributes.

## Benefits

| Selector Type | Example | Stable? | Readable? | Maintainable? |
|---------------|---------|---------|-----------|---------------|
| CSS Class | `.btn-primary` | ❌ Breaks with styling | ❌ | ❌ |
| Text Content | `button:has-text("Add")` | ❌ Breaks with i18n | ✅ | ❌ |
| Name Attribute | `[name*="[cookingTime]"]` | ⚠️ Verbose | ⚠️ | ⚠️ |
| **data-test-id** | `[data-test-id="post-cookingtime"]` | **✅** | **✅** | **✅** |

## Maintenance

### Adding New Fields

When you add a new field to a form:
1. **No action needed!** The form theme automatically adds `data-test-id`
2. Check the generated ID in dev tools
3. Add method to POM if needed

### Changing Field Names

If you rename a Symfony form field:
1. The `data-test-id` automatically updates
2. Tests will fail (expected!)
3. Update the POM method
4. All tests using that method are fixed

### Production Safety

The form theme checks environment:
```twig
{% if app.environment != 'prod' %}
    data-test-id="..."
{% endif %}
```

Production builds **never** include test attributes.

## Troubleshooting

### Test IDs Not Appearing

**Problem:** No `data-test-id` in HTML

**Solutions:**
1. Check you're in dev/test environment (`APP_ENV=dev`)
2. Verify form theme is registered in `DashboardController::configureCrud()`
3. Clear Symfony cache: `php bin/console cache:clear`

### Wrong Test ID

**Problem:** Test ID doesn't match expectation

**Solution:** 
- Inspect actual HTML in browser
- Test IDs are derived from form field names
- Use Playwright Inspector to find correct ID

### Collection Items Not Working

**Problem:** Can't find `data-test-id` for dynamic collection items

**Solution:**
- Collection items get IDs like `post-media-0-position`
- Wait for element to appear before interacting:
  ```typescript
  await postForm.waitForElement('post-media-0-position');
  ```

## Best Practices

### ✅ DO

- Use POM for all test interactions
- Use `data-test-id` for all selectors
- Wait for elements before interacting
- Keep test IDs lowercase with dashes

### ❌ DON'T

- Don't use CSS classes as selectors
- Don't use text content as primary selectors
- Don't use complex XPath selectors
- Don't hardcode selectors in tests (use POM!)

## Examples

### Creating a Post

```typescript
test('should create post with media', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    // Navigate
    await postForm.navigateToNew();
    
    // Fill basic fields
    await postForm.fillBasicFields({
        cookingTime: '30',
        difficulty: 'easy',
        categoryIndex: 1,
        active: true
    });
    
    // Fill translations
    await postForm.fillTranslation(0, {
        title: 'My Post FR',
        metaDescription: 'A'.repeat(120),
        excerpt: 'B'.repeat(50)
    });
    
    // Add media (JavaScript collection)
    await postForm.addMediaItem();
    await postForm.fillMediaItem(0, {
        position: '0',
        type: 'image',
        translationsFr: { alt: 'Alt FR', title: 'Title FR' },
        translationsEn: { alt: 'Alt EN', title: 'Title EN' }
    });
    
    // Submit and verify
    await postForm.submit();
    await postForm.verifySuccess();
});
```

### Testing Validation

```typescript
test('should show validation error', async ({ page }) => {
    const postForm = new PostFormPage(page);
    
    await postForm.navigateToNew();
    await postForm.fillField('post-cookingtime', '2000'); // Invalid
    await postForm.submitForm();
    await postForm.verifyValidationError();
});
```

## Resources

- [Playwright Page Object Model](https://playwright.dev/docs/pom)
- [Playwright Locators](https://playwright.dev/docs/locators)
- [Testing Best Practices](https://playwright.dev/docs/best-practices)
- [tests/e2e/README.md](./README.md) - E2E testing guide
- [TESTING-DECISION-GUIDE.md](../../TESTING-DECISION-GUIDE.md) - When to use which test
