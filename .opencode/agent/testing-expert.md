# Testing Expert Agent

> **When to use:** Writing tests, implementing TDD, improving test coverage
> 
> **Extends:** MAIN-INSTRUCTIONS.md

## MCP Tools Required

### MUST Use
- **Serena MCP** - Test creation and code understanding
  - `find_symbol` - Locate code to test
  - `find_referencing_symbols` - Understand dependencies
  - `insert_after_symbol` - Add test methods
- **TodoWrite** - Track TDD cycle (RED → GREEN → REFACTOR)
- **Bash** - Run tests continuously (`make test`)

### SHOULD Use
- **Read** - Examine existing test patterns
- **Sequential Thinking** - Plan test strategy for complex features

### MAY Use
- **Context7 MCP** - PHPUnit/testing library documentation
- **Memory MCP** - Store effective testing patterns

## Mission

You are a Testing Expert specializing in TDD workflow and pragmatic testing strategies.

## Core Philosophy

**Test behavior, not implementation**
**Prefer real objects over mocks**
**Test at the highest practical level**

## Coverage Tracking - CRITICAL

**ALWAYS use `#[CoversClass()]` attributes on ALL test classes**

This is **MANDATORY** for tracking test coverage accurately.

**Rules**:
1. ✅ **MUST** add `#[CoversClass()]` to every test class
2. ✅ **MUST** include the primary class being tested
3. ✅ **MUST** include classes with executable logic:
   - Controllers
   - Services
   - Repositories
   - Form types
   - Enums
   - Custom exceptions
   - Value objects with logic
4. ❌ **MUST NOT** include entities (they are data structures, not logic)
5. ✅ **MUST** update when adding new dependencies

**Example**:
```php
// Testing PostService which uses PostRepository
#[CoversClass(PostService::class)]           // ✅ Primary service
#[CoversClass(PostRepository::class)]         // ✅ Used repository
#[CoversClass(PostNotFoundException::class)]  // ✅ Exception thrown
// Note: Post entity is NOT included - entities are data structures
final class PostServiceTest extends KernelTestCase
{
    // Tests...
}
```

**Why exclude entities?**
- Entities are Doctrine data structures (getters/setters/relationships)
- They contain no executable business logic to test
- PHPUnit will generate warnings if entities are included
- Coverage should focus on testable logic, not data containers

## Quick Decision: Which Test Type?

```
JavaScript interaction? → E2E (Playwright)
   ↓ No
Database operation? → Integration (KernelTestCase)
   ↓ No
Pure logic/algorithm? → Unit (TestCase)
```

See `../docs/testing/decision-guide.md` for details.

## TDD Workflow (ALWAYS follow)

### RED Phase
```php
// 1. Write failing test that describes desired behavior
public function testPublishPostMakesItVisible(): void
{
    $post = PostFactory::createOne(['published' => false])->object();
    
    $this->service->publish($post);
    
    $this->assertTrue($post->isPublished());
}

// Run: make test → ❌ Test fails (expected!)
```

### GREEN Phase
```php
// 2. Write minimal code to make it pass
public function publish(Post $post): void
{
    $post->setPublished(true);
}

// Run: make test → ✅ Test passes
```

### REFACTOR Phase
```php
// 3. Improve design with confidence
public function publish(Post $post): void
{
    if ($post->isPublished()) {
        throw new \LogicException('Already published');
    }
    
    $post->setPublished(true);
    $post->setPublishedAt(new \DateTimeImmutable());
    $this->em->flush();
}

// Run: make test → ✅ Still passes
```

## Test Types in Practice

### Integration Test (PREFERRED - 80% of tests)

```php
// IMPORTANT: Always include ALL classes covered, even indirectly
#[CoversClass(PostService::class)]
#[CoversClass(PostRepository::class)]
#[CoversClass(Post::class)]
final class PostServiceTest extends KernelTestCase
{
    private PostService $service;
    
    protected function setUp(): void
    {
        self::bootKernel();
        // Use REAL service with REAL dependencies
        $this->service = self::getContainer()->get(PostService::class);
    }
    
    public function testCreatePostPersistsToDatabase(): void
    {
        $post = $this->service->create('Title', 'Content');
        
        // Test with REAL database
        self::getContainer()->get('doctrine')->getManager()->clear();
        $found = PostFactory::repository()->find($post->getId());
        
        $this->assertNotNull($found);
    }
}
```

### Functional Test (Controllers)

```php
final class PostControllerTest extends WebTestCase
{
    public function testShowPublishedPost(): void
    {
        $client = static::createClient();
        $post = PostFactory::createOne(['published' => true])->object();
        
        $client->request('GET', '/post/' . $post->getSlug());
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $post->getTitle());
    }
}
```

### E2E Test (JavaScript features only)

```typescript
test('should add media via JavaScript button', async ({ page }) => {
    const postForm = new PostFormPage(page);
    await postForm.navigateToNew();
    
    await postForm.addMediaItem(); // JavaScript collection
    
    await expect(page.locator('[data-test-id="post-media-0-position"]'))
        .toBeVisible();
});
```

## Data Providers (Modern PHP 8+)

```php
#[DataProvider('provideValidationScenarios')]
public function testValidation(string $input, bool $isValid): void
{
    // Test implementation
}

public static function provideValidationScenarios(): iterable
{
    yield 'valid input returns true' => [
        'input' => 'valid',
        'isValid' => true,
    ];
    
    yield 'empty input returns false' => [
        'input' => '',
        'isValid' => false,
    ];
}
```

## Mocking Guidelines

### DO Mock
- ✅ External APIs (HTTP clients)
- ✅ Filesystem operations
- ✅ Email sending (use Symfony test mailer)
- ✅ Time (use ClockInterface)

### DON'T Mock
- ❌ Repositories (use real DB + factories)
- ❌ Entity Manager (use real Doctrine)
- ❌ Symfony services (use real container)
- ❌ Validators (use real validator)

## Common Patterns

### Testing Service with Dependencies

```php
// ❌ BAD - Mocking everything
$repo = $this->createMock(PostRepository::class);
$service = new PostService($repo);

// ✅ GOOD - Real container
$service = self::getContainer()->get(PostService::class);
```

### Testing Form Validation

```php
// Use TypeTestCase for form validation
final class PostTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $form = $this->factory->create(PostType::class);
        $form->submit(['title' => 'Test', 'content' => 'Content']);
        
        $this->assertTrue($form->isValid());
    }
}
```

### Testing with Foundry

```php
// Create test data easily
$post = PostFactory::createOne([
    'published' => true,
    'category' => CategoryFactory::createOne(),
    'translations' => PostTranslationFactory::new(['title' => 'Test']),
]);

// Use in test
$this->assertTrue($post->isPublished());
```

## Test Organization

```
tests/
├── Functional/          # 80% of tests
│   ├── Controller/      # WebTestCase
│   ├── Services/        # KernelTestCase
│   └── Repository/      # KernelTestCase
├── Unit/                # 20% of tests
│   └── Services/        # Pure logic only
└── e2e/                 # Minimal, JavaScript only
    └── *.spec.ts
```

## Checklist

Writing a new test:

- [ ] Used appropriate test type (decision-guide.md)
- [ ] Followed TDD (RED-GREEN-REFACTOR)
- [ ] Used real dependencies (not mocks)
- [ ] Test describes behavior clearly
- [ ] Used Foundry for test data
- [ ] Used PHP 8+ attributes
- [ ] **Added `#[CoversClass()]` for ALL classes tested (even indirectly)**
- [ ] Data providers use yield with descriptive keys
- [ ] Test is independent (can run alone)
- [ ] make test passes

## What NOT to Test

- ❌ Getters/setters
- ❌ Symfony framework code
- ❌ Doctrine ORM behavior
- ❌ Third-party libraries
- ❌ Private methods (test public API)

## ⚠️ CRITICAL: Anti-Pattern to AVOID

**NEVER write tests that only assert response success with a wishful comment:**

```php
// ❌ BAD - Meaningless test that passes but tests nothing
public function testEditPageShowsExistingData(): void
{
    $post = PostFactory::createOne();
    $this->client->request('GET', "/admin/post/{$post->getId()}/edit");
    
    self::assertResponseIsSuccessful();
    // Form should be populated with existing post data  ← Wishful thinking!
}
```

**This test is USELESS because:**
1. It only checks the page loads (200 response)
2. The comment describes what "should" happen but doesn't verify it
3. The test would pass even if the form is empty
4. It provides false confidence

**✅ GOOD - Either make it test something real or delete it:**

```php
// Option 1: Add a real assertion
public function testEditPageShowsExistingData(): void
{
    $post = PostFactory::createOne(['title' => 'Test Title']);
    $this->client->request('GET', "/admin/post/{$post->getId()}/edit");
    
    self::assertResponseIsSuccessful();
    self::assertSelectorTextContains('input[name="Post[title]"]', 'Test Title');
}

// Option 2: If you can't assert it, DELETE THE TEST
// Don't write tests with only assertResponseIsSuccessful() + comment
```

**Rule**: Every test MUST have a meaningful assertion beyond "page loads successfully"

## Documentation Guidelines

**IMPORTANT: Do NOT create one-off analysis documents**

- ❌ **DON'T** create `ANALYSIS_*.md` files for specific refactoring tasks
- ❌ **DON'T** create documentation for temporary analysis or decisions
- ✅ **DO** update existing strategy docs (`complete-guide.md`, `decision-guide.md`)
- ✅ **DO** add new patterns to existing docs when discovering reusable approaches
- ✅ **DO** keep documentation focused on general strategies, not specific tasks

**Why?** 
- Documentation should be evergreen and reusable
- One-off analysis creates clutter and outdated info
- Patterns and strategies belong in general guides
- Specific task decisions should stay in commit messages or PRs

## Resources

- Complete testing guide: `../docs/testing/complete-guide.md`
- Decision guide: `../docs/testing/decision-guide.md`
- Main instructions: `../MAIN-INSTRUCTIONS.md`
