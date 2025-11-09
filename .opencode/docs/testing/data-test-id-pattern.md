# Data Test ID Pattern

## Overview

The `data-test-id` pattern provides a reliable and maintainable way to target elements in functional and E2E tests. This pattern uses a custom Twig function `test_id()` that renders `data-test-id` attributes **only in the test environment**, keeping production HTML clean.

## Why Use data-test-id?

### Problems with Traditional Selectors

❌ **CSS Classes**
```html
<button class="btn btn-primary submit-button">Submit</button>
```
- Fragile: Class names change for styling
- Ambiguous: Multiple buttons might share classes
- Not semantic: Classes are for styling, not testing

❌ **Generic HTML Elements**
```html
<button>Submit</button>
```
- Fragile: Multiple buttons on page
- Not specific: Requires complex selectors like `nav > div > button:first-child`

❌ **Text Content**
```html
<button>Submit</button>
```
- Breaks with translations
- Changes with copy updates
- Not reliable for dynamic content

### Benefits of data-test-id

✅ **Explicit Test Contract**
```html
<button {{ test_id('submit-button') }}>Submit</button>
<!-- In test environment renders: -->
<button data-test-id="submit-button">Submit</button>
```
- Clear intent: This element is meant to be tested
- Stable: Survives styling and copy changes
- Fast: Direct attribute selectors are efficient
- Clean: Only renders in test environment

## Implementation

### Backend: TestExtension

Location: `src/Twig/TestExtension.php`

```php
<?php

declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Attribute\AsTwigFunction;
use Twig\Markup;

/**
 * Twig extension that adds data-test-id attributes for easier E2E/Functional testing.
 * Only renders in test environment to keep production HTML clean.
 */
readonly class TestExtension
{
    public function __construct(
        #[Autowire('%kernel.environment%')] 
        private string $environment
    ) {}

    /**
     * Renders a data-test-id attribute only in test environment.
     *
     * Usage in Twig:
     *   <button {{ test_id('submit-button') }}>Submit</button>
     *   <div {{ test_id('post-' ~ post.id) }}>...</div>
     *
     * Renders in test:
     *   <button data-test-id="submit-button">Submit</button>
     *
     * Renders in prod/dev:
     *   <button>Submit</button>
     */
    #[AsTwigFunction('test_id')]
    public function renderTestId(string $identifier): string|Markup
    {
        if ('test' !== $this->environment) {
            return '';
        }

        $attribute = \sprintf(
            'data-test-id="%s"', 
            htmlspecialchars($identifier, \ENT_COMPAT, 'UTF-8')
        );

        return new Markup($attribute, 'UTF-8');
    }
}
```

**Key Features:**
- ✅ Environment-aware: Only renders in `test` environment
- ✅ XSS-safe: Uses `htmlspecialchars()` for security
- ✅ Returns Markup: Prevents double-escaping in Twig
- ✅ UTF-8 encoding: Handles special characters correctly

### Frontend: Template Usage

#### Basic Usage

```twig
{# Single element #}
<button {{ test_id('submit-button') }}>Submit</button>

{# Navigation #}
<header {{ test_id('header') }}>
    <nav {{ test_id('navbar') }}>
        <!-- navigation items -->
    </nav>
</header>

{# Form fields #}
<input type="email" {{ test_id('email-input') }} />
<button type="submit" {{ test_id('submit-form') }}>Submit</button>
```

#### Dynamic IDs with Variables

```twig
{# Using entity IDs #}
{% for post in posts %}
    <article {{ test_id('post-card-' ~ post.id) }}>
        <h3 {{ test_id('post-title-' ~ post.id) }}>{{ post.title }}</h3>
    </article>
{% endfor %}

{# Using slugs #}
<div {{ test_id('category-' ~ category.slug) }}>
    {{ category.title }}
</div>

{# Using loop index #}
{% for item in items %}
    <li {{ test_id('item-' ~ loop.index) }}>{{ item.name }}</li>
{% endfor %}
```

#### Conditional Test IDs

```twig
{# Add test ID only for specific states #}
<button 
    class="btn"
    {% if post.isPublished %}
        {{ test_id('published-post-' ~ post.id) }}
    {% else %}
        {{ test_id('draft-post-' ~ post.id) }}
    {% endif %}
>
    {{ post.isPublished ? 'View' : 'Edit Draft' }}
</button>
```

## Testing with data-test-id

### Functional Tests (PHPUnit + Symfony)

#### Basic Assertions

```php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HeaderControllerTest extends WebTestCase
{
    public function testHeaderIsPresent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/header');

        // Assert element exists
        self::assertSelectorExists('[data-test-id="header"]');
        self::assertSelectorExists('[data-test-id="navbar"]');
    }

    public function testHeaderNotRenderedWhenNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        // Assert element does NOT exist
        self::assertSelectorNotExists('[data-test-id="admin-menu"]');
    }
}
```

#### Counting Elements

```php
public function testPostListShowsAllPosts(): void
{
    // Assuming 3 posts in fixtures
    $client = static::createClient();
    $crawler = $client->request('GET', '/posts');

    // Count elements with test ID pattern
    $postCards = $crawler->filter('[data-test-id^="post-card-"]');
    self::assertCount(3, $postCards);
}
```

#### Checking Content

```php
public function testPostTitleIsDisplayed(): void
{
    $client = static::createClient();
    $crawler = $client->request('GET', '/post/my-post');

    // Get text content from element
    $title = $crawler->filter('[data-test-id="post-show-title"]')->text();
    self::assertSame('My Post Title', $title);
}
```

#### Form Interactions

```php
public function testLoginForm(): void
{
    $client = static::createClient();
    $crawler = $client->request('GET', '/login');

    // Fill form using test IDs
    $form = $crawler->selectButton('submit-login')->form([
        '[data-test-id="email-input"]' => 'user@example.com',
        '[data-test-id="password-input"]' => 'password123',
    ]);

    $client->submit($form);
    
    self::assertResponseRedirects('/dashboard');
}
```

### E2E Tests (Playwright)

#### Basic Locators

```typescript
import { test, expect } from '@playwright/test';

test('header is visible', async ({ page }) => {
    await page.goto('/');

    // Find element by test ID
    const header = page.locator('[data-test-id="header"]');
    await expect(header).toBeVisible();
});

test('navbar contains links', async ({ page }) => {
    await page.goto('/');

    // Find nested element
    const navbar = page.locator('[data-test-id="navbar"]');
    const links = navbar.locator('a');
    
    await expect(links).toHaveCount(5);
});
```

#### Interactions

```typescript
test('submit form', async ({ page }) => {
    await page.goto('/contact');

    // Fill inputs by test ID
    await page.locator('[data-test-id="email-input"]').fill('test@example.com');
    await page.locator('[data-test-id="message-input"]').fill('Hello!');
    
    // Click button by test ID
    await page.locator('[data-test-id="submit-button"]').click();
    
    // Check success message
    const success = page.locator('[data-test-id="success-message"]');
    await expect(success).toBeVisible();
});
```

#### Dynamic IDs

```typescript
test('click specific post', async ({ page }) => {
    await page.goto('/posts');

    // Click post with ID 123
    await page.locator('[data-test-id="post-card-123"]').click();
    
    await expect(page).toHaveURL('/post/123');
});

test('all posts have titles', async ({ page }) => {
    await page.goto('/posts');

    // Find all elements matching pattern
    const postTitles = page.locator('[data-test-id^="post-title-"]');
    
    await expect(postTitles).not.toHaveCount(0);
    
    // Verify each has text
    const count = await postTitles.count();
    for (let i = 0; i < count; i++) {
        await expect(postTitles.nth(i)).not.toBeEmpty();
    }
});
```

## Naming Conventions

### Component-Level IDs

Use kebab-case with component hierarchy:

```twig
{# Good: Clear hierarchy #}
<header {{ test_id('header') }}>
    <nav {{ test_id('navbar') }}>
        <ul {{ test_id('navbar-menu') }}>
            <li {{ test_id('navbar-menu-item-home') }}>Home</li>
        </ul>
    </nav>
</header>

{# Avoid: Too generic #}
<header {{ test_id('h1') }}>  ❌
<nav {{ test_id('nav') }}>     ❌
```

### Action-Based IDs

Use verb-noun pattern for interactive elements:

```twig
{# Good: Describes action #}
<button {{ test_id('submit-form') }}>Submit</button>
<button {{ test_id('delete-post') }}>Delete</button>
<a {{ test_id('edit-profile') }}>Edit</a>

{# Avoid: Not descriptive #}
<button {{ test_id('button1') }}>Submit</button>  ❌
<button {{ test_id('btn') }}>Delete</button>      ❌
```

### Entity-Based IDs

Include entity type and identifier:

```twig
{# Good: Clear entity reference #}
<article {{ test_id('post-card-' ~ post.id) }}>
<div {{ test_id('category-' ~ category.slug) }}>
<span {{ test_id('user-badge-' ~ user.id) }}>

{# Avoid: Missing context #}
<article {{ test_id('card-' ~ post.id) }}>  ❌
<div {{ test_id(category.slug) }}>          ❌
```

### State-Based IDs

Include state information when relevant:

```twig
{# Good: State is clear #}
<div {{ test_id(post.isPublished ? 'post-published' : 'post-draft') }}>
<button {{ test_id('toggle-' ~ (menu.isOpen ? 'close' : 'open')) }}>

{# Avoid: State unclear #}
<div {{ test_id('post') }}>  ❌ (Published? Draft?)
```

## Best Practices

### DO ✅

1. **Add test IDs to Testable Elements**
   ```twig
   {# Interactive elements #}
   <button {{ test_id('submit-button') }}>Submit</button>
   <a {{ test_id('nav-link-home') }}>Home</a>
   
   {# Content that needs verification #}
   <h1 {{ test_id('page-title') }}>Title</h1>
   <div {{ test_id('error-message') }}>Error</div>
   
   {# Dynamic lists #}
   {% for post in posts %}
       <article {{ test_id('post-card-' ~ post.id) }}>
   {% endfor %}
   ```

2. **Use Descriptive Names**
   ```twig
   ✅ {{ test_id('submit-contact-form') }}
   ✅ {{ test_id('delete-post-button') }}
   ✅ {{ test_id('user-avatar-' ~ user.id) }}
   
   ❌ {{ test_id('btn1') }}
   ❌ {{ test_id('div') }}
   ❌ {{ test_id('item') }}
   ```

3. **Use Consistent Patterns**
   ```twig
   {# All cards follow same pattern #}
   {{ test_id('post-card-' ~ post.id) }}
   {{ test_id('category-card-' ~ category.id) }}
   {{ test_id('user-card-' ~ user.id) }}
   
   {# All actions follow verb-noun #}
   {{ test_id('submit-form') }}
   {{ test_id('delete-post') }}
   {{ test_id('edit-profile') }}
   ```

4. **Test Environment Only**
   ```twig
   {# Automatic - no manual checks needed #}
   <button {{ test_id('my-button') }}>Click</button>
   
   {# Renders in test env: #}
   <button data-test-id="my-button">Click</button>
   
   {# Renders in prod/dev: #}
   <button>Click</button>
   ```

### DON'T ❌

1. **Don't Overuse**
   ```twig
   ❌ Don't add to every element
   <div {{ test_id('wrapper') }}>
       <div {{ test_id('inner') }}>
           <span {{ test_id('text') }}>Hello</span>
       </div>
   </div>
   
   ✅ Only add where needed for testing
   <div>
       <div>
           <span {{ test_id('greeting-message') }}>Hello</span>
       </div>
   </div>
   ```

2. **Don't Use Generic Names**
   ```twig
   ❌ {{ test_id('button') }}
   ❌ {{ test_id('div1') }}
   ❌ {{ test_id('item') }}
   
   ✅ {{ test_id('submit-checkout') }}
   ✅ {{ test_id('product-card-' ~ product.id) }}
   ✅ {{ test_id('cart-item-' ~ item.id) }}
   ```

3. **Don't Duplicate IDs**
   ```twig
   ❌ Multiple elements with same ID
   <button {{ test_id('submit') }}>Submit Form</button>
   <button {{ test_id('submit') }}>Submit Comment</button>
   
   ✅ Unique IDs
   <button {{ test_id('submit-form') }}>Submit Form</button>
   <button {{ test_id('submit-comment') }}>Submit Comment</button>
   ```

4. **Don't Use for Non-Test Purposes**
   ```twig
   ❌ Don't use for JavaScript selectors
   <button {{ test_id('menu-toggle') }} onclick="toggle()">
       {# Use data-action for Stimulus instead #}
   
   ✅ Use appropriate attributes
   <button {{ test_id('menu-toggle') }} data-action="click->menu#toggle">
   ```

## Common Patterns

### Navigation Menus

```twig
<nav {{ test_id('navbar') }}>
    <a href="/" {{ test_id('nav-link-home') }}>Home</a>
    <a href="/about" {{ test_id('nav-link-about') }}>About</a>
    <a href="/contact" {{ test_id('nav-link-contact') }}>Contact</a>
</nav>
```

### Forms

```twig
<form {{ test_id('contact-form') }}>
    <input type="text" name="name" {{ test_id('input-name') }} />
    <input type="email" name="email" {{ test_id('input-email') }} />
    <textarea name="message" {{ test_id('input-message') }}></textarea>
    <button type="submit" {{ test_id('submit-contact') }}>Send</button>
    
    <div {{ test_id('form-errors') }} style="display: none;">
        {# Error messages #}
    </div>
</form>
```

### Lists with Dynamic Items

```twig
<div {{ test_id('post-list') }}>
    {% for post in posts %}
        <article {{ test_id('post-card-' ~ post.id) }}>
            <h2 {{ test_id('post-title-' ~ post.id) }}>{{ post.title }}</h2>
            <p {{ test_id('post-excerpt-' ~ post.id) }}>{{ post.excerpt }}</p>
            <a href="{{ path('post_show', {slug: post.slug}) }}"
               {{ test_id('post-link-' ~ post.id) }}>
                Read More
            </a>
        </article>
    {% endfor %}
</div>
```

### Modals and Overlays

```twig
<div {{ test_id('modal-confirm-delete') }} class="modal">
    <div {{ test_id('modal-content') }}>
        <h3 {{ test_id('modal-title') }}>Confirm Deletion</h3>
        <p {{ test_id('modal-message') }}>Are you sure?</p>
        <button {{ test_id('modal-confirm') }}>Yes, Delete</button>
        <button {{ test_id('modal-cancel') }}>Cancel</button>
    </div>
</div>
```

### Status Messages

```twig
{% if success %}
    <div {{ test_id('alert-success') }}>
        {{ success }}
    </div>
{% endif %}

{% if error %}
    <div {{ test_id('alert-error') }}>
        {{ error }}
    </div>
{% endif %}
```

## Workflow Integration

### When Adding New Templates

1. **Identify Testable Elements**
   - Interactive elements (buttons, links, inputs)
   - Content that will be verified (titles, messages)
   - Dynamic lists (posts, categories, users)

2. **Add test_id() Function**
   ```twig
   <button {{ test_id('descriptive-name') }}>Action</button>
   ```

3. **Write Tests Using test_id**
   ```php
   self::assertSelectorExists('[data-test-id="descriptive-name"]');
   ```

### When Writing Tests

1. **Prefer data-test-id Over Other Selectors**
   ```php
   ✅ self::assertSelectorExists('[data-test-id="submit-button"]');
   ❌ self::assertSelectorExists('.btn.btn-primary');
   ❌ self::assertSelectorExists('button:first-child');
   ```

2. **Request test_id If Missing**
   - If template doesn't have test IDs, add them first
   - Don't rely on CSS classes or element positions
   - Keep tests stable and maintainable

3. **Use Consistent Patterns**
   - Follow naming conventions in this guide
   - Match patterns used elsewhere in codebase
   - Make test IDs predictable

## Migration Guide

### Updating Existing Tests

**Before (fragile):**
```php
// Using CSS classes
$crawler->filter('.header')->first();
$crawler->filter('.nav-item')->eq(2);
$crawler->filter('button.btn-primary')->first();
```

**After (stable):**
```php
// Using data-test-id
$crawler->filter('[data-test-id="header"]');
$crawler->filter('[data-test-id="nav-item-about"]');
$crawler->filter('[data-test-id="submit-button"]');
```

### Adding test_id to Existing Templates

1. **Identify Critical Elements**
   - What do your tests target?
   - What interactions do users perform?
   - What content needs verification?

2. **Add test_id Incrementally**
   ```twig
   {# Step 1: Add to main sections #}
   <header {{ test_id('header') }}>
   
   {# Step 2: Add to navigation #}
   <nav {{ test_id('navbar') }}>
   
   {# Step 3: Add to specific elements as tests need them #}
   <button {{ test_id('submit-form') }}>
   ```

3. **Update Tests**
   - Replace fragile selectors with test IDs
   - Run tests to verify they still pass
   - Remove old selector-based tests

## Testing the TestExtension

Location: `tests/Unit/Twig/TestExtensionTest.php`

```php
final class TestExtensionTest extends TestCase
{
    public function testRenderTestIdInTestEnvironment(): void
    {
        $extension = new TestExtension('test');
        $result = $extension->renderTestId('submit-button');

        self::assertInstanceOf(Markup::class, $result);
        self::assertSame('data-test-id="submit-button"', (string) $result);
    }

    public function testRenderTestIdInProdEnvironmentReturnsEmpty(): void
    {
        $extension = new TestExtension('prod');
        $result = $extension->renderTestId('submit-button');

        self::assertSame('', $result);
    }

    public function testRenderTestIdEscapesHtmlSpecialChars(): void
    {
        $extension = new TestExtension('test');
        $result = $extension->renderTestId('button-with-"quotes"');

        self::assertSame('data-test-id="button-with-&quot;quotes&quot;"', (string) $result);
    }
}
```

## Summary

The `data-test-id` pattern provides:

✅ **Stability** - Survives styling and content changes
✅ **Clarity** - Explicit contract between templates and tests
✅ **Performance** - Fast attribute selectors
✅ **Cleanliness** - Only in test environment
✅ **Security** - XSS-safe with proper escaping
✅ **Maintainability** - Tests are easier to understand and update

**Remember:** 
- Add `{{ test_id('name') }}` to templates for elements you want to test
- Use `[data-test-id="name"]` in tests to find those elements
- Follow naming conventions for consistency
- Only add test IDs where actually needed

---

**Related Documentation:**
- [Complete Testing Guide](complete-guide.md)
- [Testing Decision Guide](decision-guide.md)
- [E2E Setup](e2e-setup.md)
