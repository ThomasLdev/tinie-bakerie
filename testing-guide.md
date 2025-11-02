# Testing Strategy Guide for Tinie Bakerie

> **When to read this guide**: When writing tests, reviewing tests, or implementing TDD workflow.
> 
> **Core file**: This supplements `claude.md` with detailed testing patterns and examples.

---

## Testing Philosophy

This project follows a **pragmatic, integration-focused testing approach**:

**Core Principles** (in priority order):

1. **Test Behavior, Not Implementation**
   - ✅ Test input/output and observable behavior
   - ❌ Don't test internal implementation details
   - 🎯 Goal: Tests should survive refactoring

2. **Prefer Real Objects Over Mocks**
   - ✅ Use concrete class instantiation
   - ✅ Use Symfony's real container in tests
   - ❌ Avoid excessive mocking (creates artificial tests)
   - 🎯 Goal: Test realistic scenarios

3. **Test at the Highest Practical Level**
   - ✅ Prefer functional/integration tests
   - ✅ Use `KernelTestCase` or `WebTestCase` when relevant
   - ❌ Don't write unit tests if a functional test is more appropriate
   - 🎯 Goal: Test the system as users will use it

4. **Test-Driven Development (TDD)**
   - ✅ **ALWAYS** write tests first (Red-Green-Refactor)
   - ✅ Start with failing test (Red)
   - ✅ Write minimal code to pass (Green)
   - ✅ Refactor with confidence (Refactor)
   - 🎯 Goal: Design emerges from tests

5. **Low Maintenance, High Value**
   - ✅ Tests should be easy to understand and maintain
   - ✅ Focus on critical paths and edge cases
   - ❌ Don't test framework code or trivial getters/setters
   - 🎯 Goal: Maximize ROI of testing effort

---

## TDD Workflow (Red-Green-Refactor)

**MUST follow this cycle for all new features**:

### Phase 1: RED (Write Failing Test)

```php
// 1. Start with the test that describes desired behavior
namespace App\Tests\Functional\Services;

use App\Factory\PostFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PostPublishServiceTest extends KernelTestCase
{
    public function testPublishPostMakesItVisible(): void
    {
        self::bootKernel();
        $service = self::getContainer()->get(PostPublishService::class);
        
        // Given: an unpublished post
        $post = PostFactory::createOne(['published' => false])->object();
        
        // When: we publish it
        $service->publish($post);
        
        // Then: it should be visible
        $this->assertTrue($post->isPublished());
        $this->assertNotNull($post->getPublishedAt());
    }
}
```

**Run test → It fails (class doesn't exist) → This is expected! ✅**

### Phase 2: GREEN (Make It Pass)

```php
// 2. Write minimal implementation to pass the test
namespace App\Services\Post;

final class PostPublishService
{
    public function publish(Post $post): void
    {
        $post->setPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable());
        // Minimal code to make test pass
    }
}
```

**Run test → It passes ✅**

### Phase 3: REFACTOR (Improve Design)

```php
// 3. Now refactor with confidence (tests protect you)
namespace App\Services\Post;

final class PostPublishService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
    ) {}
    
    public function publish(Post $post): void
    {
        if ($post->isPublished()) {
            throw new \LogicException('Post already published');
        }
        
        $post->setPublished(true);
        $post->setPublishedAt(new \DateTimeImmutable());
        $this->em->flush();
        
        $this->dispatcher->dispatch(new PostPublishedEvent($post));
    }
}
```

**Run test → Still passes ✅ → Add test for edge case → Repeat cycle**

---

## Test Level Decision Matrix

**Use this table to decide which test type to write**:

| Scenario | Test Type | Base Class | Why |
|----------|-----------|------------|-----|
| **Controller/Route** | Functional | `WebTestCase` | Test HTTP layer, routing, responses |
| **Service using DB** | Integration | `KernelTestCase` | Test with real database, repos |
| **Service using other services** | Integration | `KernelTestCase` | Test with real dependencies |
| **Complex business logic (pure)** | Unit | `TestCase` | Pure logic, no dependencies |
| **Form validation** | Functional | `KernelTestCase` | Test with real validator |
| **Repository query** | Integration | `KernelTestCase` | Test with real database |
| **Command (CLI)** | Functional | `KernelTestCase` | Test full command execution |
| **Event Subscriber** | Integration | `KernelTestCase` | Test event dispatching |
| **Validation Constraint** | Integration | `KernelTestCase` | Test with real validator |

**Rule of Thumb**:
- 🔵 **80% Functional/Integration tests** (`KernelTestCase`, `WebTestCase`)
- 🟢 **20% Unit tests** (Pure logic, algorithms, calculations)
- 🔴 **0% Tests that mock everything** (No value)

---

## When to Use Each Test Type

### Functional Tests (WebTestCase) - PREFERRED

**MUST use `WebTestCase` for**:
- ✅ All controllers (test routes, responses, templates)
- ✅ Form submissions
- ✅ Authentication/authorization flows
- ✅ AJAX/Turbo endpoints
- ✅ Redirects and flash messages

**Example**:
```php
namespace App\Tests\Functional\Controller;

use App\Factory\PostFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PostControllerTest extends WebTestCase
{
    public function testShowPublishedPost(): void
    {
        $client = static::createClient();
        
        // Use real factory with real database
        $post = PostFactory::createOne([
            'published' => true,
            'translations' => PostTranslationFactory::new(['title' => 'Test Post']),
        ])->object();
        
        // Test real HTTP request
        $client->request('GET', '/post/' . $post->getSlug());
        
        // Assert on real response
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Test Post');
    }
    
    public function testShowUnpublishedPostReturns404(): void
    {
        $client = static::createClient();
        $post = PostFactory::createOne(['published' => false])->object();
        
        $client->request('GET', '/post/' . $post->getSlug());
        
        $this->assertResponseStatusCodeSame(404);
    }
}
```

**Why this is better than mocking**:
- ✅ Tests real database queries (catches N+1 issues)
- ✅ Tests real Twig rendering
- ✅ Tests real routing
- ✅ Tests real Symfony behavior
- ✅ Survives refactoring

### Integration Tests (KernelTestCase) - PREFERRED

**MUST use `KernelTestCase` for**:
- ✅ Services that interact with database
- ✅ Services that use other services
- ✅ Repository custom methods
- ✅ Commands
- ✅ Event subscribers
- ✅ Validation constraints

**Example**:
```php
namespace App\Tests\Functional\Services;

use App\Factory\PostFactory;
use App\Services\Post\PostPublishService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PostPublishServiceTest extends KernelTestCase
{
    private PostPublishService $service;
    
    protected function setUp(): void
    {
        self::bootKernel();
        
        // Get REAL service from container with REAL dependencies
        $this->service = self::getContainer()->get(PostPublishService::class);
    }
    
    public function testPublishingSetsPublishedDateAndPersists(): void
    {
        // Given: unpublished post (real database)
        $post = PostFactory::createOne(['published' => false])->object();
        $postId = $post->getId();
        
        // When: we publish
        $this->service->publish($post);
        
        // Then: changes are persisted (test real database)
        self::getContainer()->get('doctrine')->getManager()->clear();
        $refreshedPost = PostFactory::repository()->find($postId);
        
        $this->assertTrue($refreshedPost->isPublished());
        $this->assertInstanceOf(\DateTimeImmutable::class, $refreshedPost->getPublishedAt());
    }
}
```

**Why this is better than mocking**:
- ✅ Tests real service container wiring
- ✅ Tests real database persistence
- ✅ Tests real Doctrine behavior (cascades, events)
- ✅ Catches configuration errors
- ✅ No brittle mocks to maintain

### Unit Tests (TestCase) - RARE

**ONLY use pure unit tests (`TestCase`) for**:
- ✅ Pure algorithms/calculations (no dependencies)
- ✅ Value objects
- ✅ Pure utility functions
- ✅ Complex business rules (isolated logic)

**Example** (rare case):
```php
namespace App\Tests\Unit\Services\Slug;

use App\Services\Slug\SlugGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SlugGenerator::class)]
final class SlugGeneratorTest extends TestCase
{
    public function testGenerateSlugFromTitle(): void
    {
        $generator = new SlugGenerator(); // No dependencies!
        
        $result = $generator->generate('Hello World! été 2024');
        
        $this->assertSame('hello-world-ete-2024', $result);
    }
    
    #[DataProvider('provideEdgeCases')]
    public function testEdgeCases(string $input, string $expected): void
    {
        $generator = new SlugGenerator();
        $this->assertSame($expected, $generator->generate($input));
    }
    
    public static function provideEdgeCases(): iterable
    {
        yield 'empty string returns empty' => [
            'input' => '',
            'expected' => '',
        ];
        
        yield 'only special characters returns empty' => [
            'input' => '!!!',
            'expected' => '',
        ];
        
        yield 'accented characters are normalized' => [
            'input' => 'Émilie',
            'expected' => 'emilie',
        ];
        
        yield 'multiple spaces are collapsed' => [
            'input' => 'Hello   World',
            'expected' => 'hello-world',
        ];
    }
}
```

**When NOT to use unit tests**:
- ❌ Services that depend on repositories → Use `KernelTestCase`
- ❌ Services that depend on other services → Use `KernelTestCase`
- ❌ Controllers → Use `WebTestCase`
- ❌ Anything touching the database → Use `KernelTestCase`

---

## Mocking Guidelines

### When Mocking is ACCEPTABLE

**SHOULD mock for**:
- ✅ External HTTP API calls
- ✅ Filesystem operations
- ✅ Email sending (use Symfony's test mailer)
- ✅ Time-dependent code (use `ClockInterface` mock)
- ✅ Random number generation
- ✅ Third-party services (payment gateways, etc.)

**Example** (acceptable mock):
```php
namespace App\Tests\Functional\Services;

use App\Services\Weather\WeatherApiClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class WeatherServiceTest extends KernelTestCase
{
    public function testGetForecastFromExternalApi(): void
    {
        self::bootKernel();
        
        // Mock external API (we don't control it)
        $apiClient = $this->createMock(WeatherApiClient::class);
        $apiClient->method('fetch')->willReturn(['temp' => 20]);
        
        // But use REAL service with mocked external dependency
        $service = new WeatherService($apiClient);
        
        $result = $service->getForecast('Paris');
        
        $this->assertEquals(20, $result->getTemperature());
    }
}
```

### When Mocking is WRONG

**MUST NOT mock**:
- ❌ Doctrine repositories → Use real database with factories
- ❌ Symfony services → Use real container
- ❌ Entity Manager → Use real Doctrine
- ❌ Validator → Use real validator
- ❌ Form types → Test with real form handling
- ❌ Event dispatcher → Use real dispatcher

**Bad Example** (DON'T do this):
```php
// ❌ BAD: Too much mocking, tests nothing real
public function testCreatePost(): void
{
    $em = $this->createMock(EntityManagerInterface::class);
    $repo = $this->createMock(PostRepository::class);
    $slugger = $this->createMock(SlugService::class);
    $validator = $this->createMock(ValidatorInterface::class);
    
    $slugger->method('generate')->willReturn('test-slug');
    $validator->method('validate')->willReturn([]);
    
    $service = new PostService($em, $repo, $slugger, $validator);
    
    // This test verifies nothing about real behavior!
}
```

**Good Example** (DO this instead):
```php
// ✅ GOOD: Use real dependencies
public function testCreatePost(): void
{
    self::bootKernel();
    $service = self::getContainer()->get(PostService::class); // Real service!
    
    $post = $service->createPost('Test Title', 'Content');
    
    // Test real database persistence
    $this->assertNotNull($post->getId());
    $this->assertEquals('test-title', $post->getSlug());
}
```

---

## What to Test vs What NOT to Test

### ✅ MUST Test

**Business Logic & Behavior**:
- ✅ User flows (registration, login, posting, etc.)
- ✅ Business rules (publish validation, permissions)
- ✅ Data transformations
- ✅ Error handling (404s, validation errors, exceptions)
- ✅ Edge cases (empty input, boundary values)
- ✅ Security (authorization, input validation)

**Examples**:
```php
// ✅ Test business rule
public function testCannotPublishPostWithoutTitle(): void
{
    $post = PostFactory::createOne(['published' => false])->object();
    $post->setTitle(''); // Invalid state
    
    $this->expectException(ValidationException::class);
    $this->service->publish($post);
}

// ✅ Test edge case
public function testSearchWithEmptyQueryReturnsAllPosts(): void
{
    PostFactory::createMany(5);
    
    $results = $this->searchService->search('');
    
    $this->assertCount(5, $results);
}

// ✅ Test authorization
public function testUnpublishedPostNotVisibleToGuests(): void
{
    $client = static::createClient();
    $post = PostFactory::createOne(['published' => false])->object();
    
    $client->request('GET', '/post/' . $post->getSlug());
    
    $this->assertResponseStatusCodeSame(404);
}
```

### ❌ MUST NOT Test

**Framework/Library Code**:
- ❌ Doctrine ORM behavior (trust it works)
- ❌ Symfony routing (trust it works)
- ❌ Twig rendering (trust it works)
- ❌ Form validation (unless custom constraint)

**Trivial Code**:
- ❌ Simple getters/setters
- ❌ Basic constructors
- ❌ Framework-generated code

**Implementation Details**:
- ❌ Private method behavior (test public API)
- ❌ Internal state (test observable output)
- ❌ Method call counts (brittle)

**Bad Examples** (DON'T do this):
```php
// ❌ BAD: Testing getter/setter
public function testSetTitle(): void
{
    $post = new Post();
    $post->setTitle('Test');
    $this->assertEquals('Test', $post->getTitle()); // Useless!
}

// ❌ BAD: Testing implementation detail
public function testServiceCallsRepositoryFindMethod(): void
{
    $repo = $this->createMock(PostRepository::class);
    $repo->expects($this->once())->method('find')->with(123); // Brittle!
    
    $service = new PostService($repo);
    $service->getPost(123);
}

// ❌ BAD: Testing private method
public function testPrivateValidateMethod(): void
{
    $reflection = new \ReflectionMethod(PostService::class, 'validate');
    $reflection->setAccessible(true); // NO! Test public API only
}
```

---

## Test Organization & Conventions

### Directory Structure

```
tests/
├── Functional/           # WebTestCase & KernelTestCase (80% of tests)
│   ├── Controller/       # WebTestCase for all controllers
│   ├── Services/         # KernelTestCase for services with dependencies
│   ├── Repository/       # KernelTestCase for custom repo methods
│   └── Command/          # KernelTestCase for CLI commands
├── Unit/                 # Pure unit tests (20% of tests)
│   └── Services/         # Only for pure logic, no dependencies
└── bootstrap.php
```

### Naming Conventions

**Test class names**:
```
{ClassBeingTested}Test.php

PostController → PostControllerTest
PostService → PostServiceTest
PostRepository → PostRepositoryTest
```

**Test method names** (be descriptive):
```php
// ✅ GOOD: Describes behavior
public function testPublishedPostsAreVisibleToGuests(): void
public function testUnpublishedPostReturns404(): void
public function testSearchReturnsPostsMatchingTitle(): void

// ❌ BAD: Vague
public function testShow(): void
public function testItWorks(): void
public function testPost(): void
```

### AAA Pattern (Arrange-Act-Assert)

**MUST structure all tests with AAA**:

```php
public function testPublishingPostSendsNotification(): void
{
    // ARRANGE: Set up test state
    self::bootKernel();
    $service = self::getContainer()->get(PostPublishService::class);
    $post = PostFactory::createOne(['published' => false])->object();
    
    // ACT: Perform action being tested
    $service->publish($post);
    
    // ASSERT: Verify outcome
    $this->assertTrue($post->isPublished());
    $this->assertEmailCount(1);
}
```

---

## PHPUnit Attributes & Data Providers

### MUST use PHP 8+ attributes instead of docblocks

PHPUnit 10+ supports native PHP attributes which are cleaner, type-safe, and better supported by IDEs.

**Common Attributes**:

```php
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Depends;

#[CoversClass(PostService::class)]
final class PostServiceTest extends KernelTestCase
{
    #[Test]
    #[TestDox('Published posts are visible to guests')]
    public function publishedPostsAreVisible(): void
    {
        // Test implementation
    }
    
    #[Test]
    #[Group('integration')]
    #[Group('post')]
    public function testAnotherScenario(): void
    {
        // Test implementation
    }
}
```

### Data Providers - MUST use yield with descriptive keys

✅ **CORRECT** - Iterator with descriptive keys:

```php
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CategoryService::class)]
final class CategoryServiceTest extends KernelTestCase
{
    #[DataProvider('provideSearchScenarios')]
    public function testSearchCategories(string $locale, bool $hasPosts, int $expectedCount): void
    {
        // Test implementation using the parameters
        $results = $this->service->search($locale, $hasPosts);
        $this->assertCount($expectedCount, $results);
    }
    
    /**
     * Data provider with descriptive test case names
     */
    public static function provideSearchScenarios(): iterable
    {
        yield 'french categories with posts' => [
            'locale' => 'fr',
            'hasPosts' => true,
            'expectedCount' => 5,
        ];
        
        yield 'french categories without posts' => [
            'locale' => 'fr',
            'hasPosts' => false,
            'expectedCount' => 0,
        ];
        
        yield 'english categories with posts' => [
            'locale' => 'en',
            'hasPosts' => true,
            'expectedCount' => 3,
        ];
        
        yield 'empty locale returns no results' => [
            'locale' => '',
            'hasPosts' => true,
            'expectedCount' => 0,
        ];
    }
}
```

**Why use yield with descriptive keys?**
- ✅ **Clear test output**: PHPUnit displays the key name when test fails
- ✅ **Self-documenting**: No need to guess what the test case represents
- ✅ **Named parameters**: More readable than positional arrays
- ✅ **Memory efficient**: Iterator doesn't load all data at once
- ✅ **IDE support**: Better autocomplete and type inference

### Complex Example with Objects

```php
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PostPublishService::class)]
final class PostPublishServiceTest extends KernelTestCase
{
    #[DataProvider('provideInvalidPostStates')]
    public function testCannotPublishInvalidPost(Post $post, string $expectedErrorMessage): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        
        $this->service->publish($post);
    }
    
    public static function provideInvalidPostStates(): iterable
    {
        yield 'post without title' => [
            'post' => PostFactory::new(['title' => ''])->withoutPersisting()->create()->object(),
            'expectedErrorMessage' => 'Title cannot be empty',
        ];
        
        yield 'post without content' => [
            'post' => PostFactory::new(['content' => ''])->withoutPersisting()->create()->object(),
            'expectedErrorMessage' => 'Content cannot be empty',
        ];
        
        yield 'already published post' => [
            'post' => PostFactory::new(['published' => true])->withoutPersisting()->create()->object(),
            'expectedErrorMessage' => 'Post is already published',
        ];
        
        yield 'post with future publish date' => [
            'post' => PostFactory::new([
                'publishedAt' => new \DateTimeImmutable('+1 day')
            ])->withoutPersisting()->create()->object(),
            'expectedErrorMessage' => 'Cannot publish future-dated post',
        ];
    }
}
```

### Key Rules

1. **MUST** use `#[DataProvider('methodName')]` instead of `@dataProvider`
2. **MUST** use `#[CoversClass(ClassName::class)]` on test classes
3. **MUST** use `yield` in data providers with descriptive string keys
4. **MUST** use named array keys in yielded data (`'input' => 'value'`)
5. **SHOULD** use `#[TestDox('description')]` for complex test scenarios
6. **SHOULD** use `#[Group('name')]` to organize related tests
7. **MAY** use other attributes when relevant (Depends, Requires*, etc.)

---

## Using Foundry for Test Data

**MUST use Foundry factories for all test data**:

```php
use App\Factory\PostFactory;
use App\Factory\CategoryFactory;

// Create single entity
$post = PostFactory::createOne([
    'title' => 'Custom Title',
    'published' => true,
]);

// Create multiple entities
PostFactory::createMany(10);

// Create with relationships
$post = PostFactory::createOne([
    'category' => CategoryFactory::createOne(),
    'tags' => TagFactory::createMany(3),
]);

// Use the object
$postEntity = $post->object();

// Or use repository
$foundPost = PostFactory::repository()->findOneBy(['slug' => 'test']);
```

**Why Foundry**:
- ✅ Real database data (not mocks)
- ✅ Handles complex relationships automatically
- ✅ Realistic test scenarios
- ✅ Easy to read and maintain
- ✅ Same factories used for fixtures

---

## Test Coverage Guidelines

**Target Coverage** (not a goal, but a guideline):
- 🎯 **Critical paths**: 100% coverage
- 🎯 **Business logic**: 90%+ coverage
- 🎯 **Controllers**: 80%+ coverage
- 🎯 **Overall project**: 70%+ coverage

**MUST have tests for**:
- ✅ All controllers and routes
- ✅ All services with business logic
- ✅ All custom repository methods
- ✅ All custom validation constraints
- ✅ All commands
- ✅ All event subscribers
- ✅ Critical security features

**SHOULD have tests for**:
- ✅ Form types (functional tests)
- ✅ Complex Twig extensions
- ✅ Factory methods

**MAY skip tests for**:
- ❌ Simple DTOs/Value objects (unless complex validation)
- ❌ Doctrine entities (unless complex methods)
- ❌ Simple configuration classes
- ❌ Trivial helper methods

**Remember**: Coverage percentage is not the goal. The goal is **confidence in deployments**.

---

## Testing Anti-Patterns to AVOID

### ❌ Over-Mocking
```php
// BAD: Mocking everything = testing nothing
$em = $this->createMock(EntityManagerInterface::class);
$repo = $this->createMock(PostRepository::class);
$slugger = $this->createMock(SlugService::class);
// ... mock everything
```

### ❌ Testing Implementation
```php
// BAD: Test how code works, not what it does
$repo->expects($this->once())->method('find'); // Brittle!
```

### ❌ Testing Private Methods
```php
// BAD: Private methods are implementation details
$reflection = new \ReflectionMethod($service, 'privateMethod');
```

### ❌ One Assertion Per Test (dogma)
```php
// BAD: Unnecessary verbosity
public function testPostHasTitle(): void { $this->assertNotNull($post->getTitle()); }
public function testPostHasContent(): void { $this->assertNotNull($post->getContent()); }

// GOOD: Test the behavior
public function testCreatePostWithRequiredFields(): void
{
    $this->assertNotNull($post->getTitle());
    $this->assertNotNull($post->getContent());
    $this->assertNotNull($post->getSlug());
    // Related assertions belong together
}
```

### ❌ Testing Getters/Setters
```php
// BAD: Waste of time
public function testGetTitle(): void
{
    $post->setTitle('Test');
    $this->assertEquals('Test', $post->getTitle());
}
```

### ❌ Sleep/Timing Tests
```php
// BAD: Flaky tests
sleep(5);
$this->assertTrue($condition); // May fail randomly
```

### ❌ Tests That Don't Assert Anything
```php
// BAD: Test that always passes
public function testSomething(): void
{
    $service->doSomething(); // No assertion!
}

// GOOD: Test observable behavior
public function testSomething(): void
{
    $result = $service->doSomething();
    $this->assertEquals('expected', $result);
}
```

### ❌ Using Docblocks Instead of Attributes
```php
// BAD: Old PHPUnit docblock style
final class PostServiceTest extends TestCase
{
    /**
     * @dataProvider edgeCaseProvider
     * @covers PostService::create
     * @group integration
     */
    public function testCreate($input, $expected)  // No type hints!
    {
        // ...
    }
}

// GOOD: Modern PHP 8+ attributes
use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, Group};

#[CoversClass(PostService::class)]
#[Group('integration')]
final class PostServiceTest extends TestCase
{
    #[DataProvider('provideEdgeCases')]
    public function testCreate(string $input, string $expected): void  // Type-safe!
    {
        // ...
    }
}
```

### ❌ Array-Based Data Providers Without Descriptive Keys
```php
// BAD: No context, unclear what's being tested
public function edgeCaseProvider(): array
{
    return [
        ['', ''],           // What is this testing?
        ['test', 'test'],   // Unclear
        [null, ''],         // No description
    ];
}

// GOOD: Iterator with descriptive keys
public static function provideEdgeCases(): iterable
{
    yield 'empty string returns empty' => [
        'input' => '',
        'expected' => '',
    ];
    
    yield 'normal string unchanged' => [
        'input' => 'test',
        'expected' => 'test',
    ];
    
    yield 'null input returns empty' => [
        'input' => null,
        'expected' => '',
    ];
}
```

### ❌ Positional Arrays in Data Providers
```php
// BAD: Hard to understand, order-dependent
public static function provideTestCases(): iterable
{
    yield 'test case' => ['value1', 'value2', true, 42, ['array']];
    // Which parameter is which? 😕
}

// GOOD: Named keys make it obvious
public static function provideTestCases(): iterable
{
    yield 'user with valid email' => [
        'email' => 'test@example.com',
        'username' => 'testuser',
        'isActive' => true,
        'age' => 42,
        'roles' => ['ROLE_USER'],
    ];
}
```
