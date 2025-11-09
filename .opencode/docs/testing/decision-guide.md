# Testing Decision Guide

Quick reference for choosing the right testing approach for each scenario.

## ⚠️ CRITICAL: Coverage Tracking

**EVERY test class MUST include `#[CoversClass()]` attributes**

```php
use PHPUnit\Framework\Attributes\CoversClass;

// Include only classes with executable logic (NOT entities)
#[CoversClass(PostService::class)]       // ✅ Service with logic
#[CoversClass(PostRepository::class)]    // ✅ Repository with queries
// Note: Post entity excluded - it's a data structure
final class PostServiceTest extends KernelTestCase
{
    // Tests...
}
```

**Rule**: Include controllers, services, repositories, forms, enums, exceptions.  
**Exclude**: Entities (they're data structures, not logic).

**See complete-guide.md for detailed coverage tracking rules.**

## Quick Decision Tree

```
Need to test something?
    │
    ├─ Does it require JavaScript?
    │   ├─ YES → E2E Test (Playwright)
    │   └─ NO  → Continue...
    │
    ├─ Does it interact with database?
    │   ├─ YES → Integration Test (KernelTestCase)
    │   └─ NO  → Continue...
    │
    └─ Pure logic/validation?
        └─ YES → Unit Test (TypeTestCase or TestCase)
```

## Testing Matrix

| Scenario | Test Type | Tool | Speed | Example |
|----------|-----------|------|-------|---------|
| **Form validation** | Unit | TypeTestCase | ⚡ Fast | `PostTypeTest::testRejectsInvalidAlt()` |
| **JavaScript collection add** | E2E | Playwright | 🐌 Slow | `post-crud.spec.ts::should create post` |
| **Database cascade** | Integration | KernelTestCase | 🏃 Medium | `PostPersistenceTest::testCascade()` |
| **Controller route** | Functional | WebTestCase | 🏃 Medium | `PostControllerTest::testShow()` |
| **Pure algorithm** | Unit | TestCase | ⚡ Fast | `SlugGeneratorTest::testGenerate()` |

## Specific Use Cases

### Post Creation with Media

```
✅ Form Unit Test (TypeTestCase)
   - Test: Form accepts valid media data
   - Test: Form rejects invalid position
   - Test: Form rejects short alt text
   - Speed: <1 second for 10 tests

✅ Integration Test (KernelTestCase)  
   - Test: Post with media persists to database
   - Test: Cascade saves media and translations
   - Test: Orphan removal deletes unused media
   - Speed: ~5 seconds for 5 tests

✅ E2E Test (Playwright)
   - Test: "Add Media" button creates form fields
   - Test: Submitting form saves to database
   - Test: Can add multiple media items
   - Speed: ~30 seconds for 3 tests

❌ Don't Test with E2E:
   - Validation edge cases (use form unit tests)
   - Database-only operations (use integration tests)
```

### Search Feature (Future)

```
✅ Form Unit Test
   - Test: Search form validates query length
   - Test: Search form accepts filters

✅ Integration Test
   - Test: Search service returns correct results
   - Test: Search filters work with database

✅ E2E Test (if JavaScript)
   - Test: Autocomplete dropdown appears
   - Test: Clicking suggestion updates form
   - Test: Real-time search works
```

## Test Count Guidelines

For a typical feature:

```
Form Unit Tests:     10-15 tests (validation edge cases)
Integration Tests:   3-5 tests (database operations)
E2E Tests:          1-3 tests (JavaScript interactions)
Functional Tests:   2-5 tests (HTTP routes, simple flows)
```

**Example: Post CRUD Feature**
- 15 form unit tests (all validation scenarios)
- 5 integration tests (cascades, orphan removal)
- 3 E2E tests (JavaScript collection management)
- 5 functional tests (non-JavaScript controller actions)
- **Total: 28 tests in ~45 seconds**

## Speed Comparison

```
Unit Tests (Form):        0.05 sec/test  →  10 tests = 0.5 sec
Integration Tests (DB):   1 sec/test     →  5 tests = 5 sec
Functional Tests (HTTP):  0.5 sec/test   →  5 tests = 2.5 sec
E2E Tests (Browser):      10 sec/test    →  3 tests = 30 sec
                                          ──────────────────
                                          Total: ~38 seconds
```

## When NOT to Write Tests

Skip testing for:
- ❌ Simple getters/setters
- ❌ Symfony framework code
- ❌ Doctrine ORM behavior
- ❌ Trivial constructors
- ❌ Third-party library functionality

## Command Cheat Sheet

```bash
# Fast feedback loop (during development)
make phpunit-unit          # Run form/unit tests only (~1 sec)

# Integration verification
make phpunit-functional    # Run integration tests (~10 sec)

# Full PHPUnit suite
make phpunit              # All PHPUnit tests (~15 sec)

# E2E tests
make e2e                  # Playwright tests (~30 sec)

# Everything
make test-all             # PHPUnit + E2E (~45 sec)
```

## TDD Workflow

### Red-Green-Refactor with Appropriate Test Type

**1. Start with Form Unit Test (Fastest)**
```bash
# tests/Form/Type/PostTypeTest.php
public function testRejectsNegativePosition(): void
{
    $form = $this->factory->create(PostType::class);
    $form->submit(['media' => [['position' => -1]]]);
    $this->assertFalse($form->isValid());
}

# Run: make phpunit-unit (0.5 sec)
# ✅ Test fails (Red)
# ✅ Add validation constraint
# ✅ Test passes (Green)
```

**2. Add Integration Test (Medium Speed)**
```bash
# tests/Integration/Entity/PostPersistenceTest.php
public function testMediaCascadesSave(): void
{
    $post = new Post();
    $post->addMedia(new PostMedia());
    $this->em->persist($post);
    $this->em->flush();
    $this->assertNotNull($post->getMedia()[0]->getId());
}

# Run: make phpunit (5 sec)
# ✅ Test fails (Red)
# ✅ Add cascade configuration
# ✅ Test passes (Green)
```

**3. Add E2E Test Last (Slowest)**
```bash
# tests/e2e/post-crud.spec.ts
test('should add media via button', async ({ page }) => {
    await page.click('[data-testid="add-media"]');
    await expect(page.locator('.media-item')).toBeVisible();
});

# Run: make e2e (30 sec)
# ✅ Verify full integration works
```

## Best Practices Summary

1. **Start with fastest tests** (form unit tests)
2. **Add integration tests** for database operations
3. **Use E2E sparingly** for JavaScript-only features
4. **Run unit tests frequently** during development
5. **Run full suite** before committing
6. **Let E2E tests catch** integration issues

## Questions?

- Form validation? → Form Unit Test
- Database cascade? → Integration Test  
- JavaScript interaction? → E2E Test
- Simple HTTP route? → Functional Test
- Pure algorithm? → Unit Test

**When in doubt**: Start with the fastest test that can verify the behavior.
