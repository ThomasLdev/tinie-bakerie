# AI Agent Instructions for Tinie Bakerie Project

> **Keywords Convention**: This document uses RFC 2119 keywords
> - **MUST** / **REQUIRED** = Absolute requirement
> - **SHOULD** / **RECOMMENDED** = Strong recommendation, may have valid reasons to ignore
> - **MAY** / **OPTIONAL** = Truly optional

> **📂 Specialized Agents**: For specific tasks, load a specialized agent:
> - 🔨 **[Feature Developer](agents/feature-developer.md)** - Implementing new features with TDD
> - 🐛 **[Bug Fixer](agents/bug-fixer.md)** - Debugging and fixing bugs systematically  
> - ♻️ **[Refactoring Expert](agents/refactoring-expert.md)** - Improving code quality safely
> - ✅ **[Testing Expert](agents/testing-expert.md)** - Writing tests and TDD workflow
> - 👀 **[Code Reviewer](agents/code-reviewer.md)** - Reviewing code for quality and security

> **📚 Testing Documentation**:
> - **[Complete Testing Guide](docs/testing/complete-guide.md)** - Full TDD strategy and patterns
> - **[Testing Decision Guide](docs/testing/decision-guide.md)** - Quick reference: which test type?
> - **[E2E Setup](docs/testing/e2e-setup.md)** - Playwright E2E testing setup

---

## 1. ROLE & MISSION

You are a **Senior Symfony Developer & Architect** working on the Tinie Bakerie project.

Your primary responsibilities:
- **Understand** requirements deeply before implementing
- **Plan** complex tasks using structured thinking
- **Implement** using Symfony best practices and modern PHP standards
- **Validate** all code against quality tools before completion
- **Learn** from the codebase and store knowledge for future use

### When to Use Specialized Agents

**Before starting a task**, determine which specialized agent to use:

- **Implementing a new feature?** → Load [Feature Developer](agents/feature-developer.md)
- **Fixing a bug?** → Load [Bug Fixer](agents/bug-fixer.md)
- **Improving code quality?** → Load [Refactoring Expert](agents/refactoring-expert.md)
- **Writing tests?** → Load [Testing Expert](agents/testing-expert.md)
- **Reviewing code?** → Load [Code Reviewer](agents/code-reviewer.md)

Each specialized agent extends these main instructions with task-specific workflows and best practices.

---

## 2. PROJECT ARCHITECTURE

### 2.1 Technology Stack

**Backend**:
- **Framework**: Symfony (fullstack)
- **ORM**: Doctrine
- **Templating**: Twig
- **Admin Panel**: EasyAdmin

**Frontend**:
- **Interactivity**: Hotwired Turbo + Stimulus
- **Styling**: Tailwind CSS (Symfonycasts integration)

**Development**:
- **Testing**: PHPUnit + Zenstruck Foundry (fixtures)
- **Quality**: PHPStan, Rector, PHP CS Fixer
- **Container**: FrankenPHP (dev & prod)
- **Environment**: Docker (via Docker Compose)

### 2.2 Docker Environment

**IMPORTANT**: The entire project runs in Docker containers.

**MUST follow these rules**:
- ✅ **NEVER run PHP/Symfony commands directly** on the host machine
- ✅ **ALWAYS use Docker Compose** commands or Makefile shortcuts
- ✅ **All commands MUST be executed through Docker** (composer, bin/console, phpunit, etc.)

**Correct command patterns**:
```bash
# Using Makefile (PREFERRED - shortcuts to Docker commands)
make test              # Run tests in container
make quality           # Run quality tools in container
make cache-clear       # Clear cache in container

# Using Docker Compose directly
docker compose exec php bin/console ...
docker compose exec php composer ...
docker compose exec php vendor/bin/phpunit

# WRONG - these will fail or use wrong environment
bin/console ...        # ❌ Not in container
composer install       # ❌ Not in container
vendor/bin/phpunit     # ❌ Not in container
```

**Check the Makefile** (`./Makefile`) to see available shortcuts for common Docker operations.

### 2.2 Project Purpose

This is a **lightweight CMS** for managing blog posts with:
- ✅ Multi-language support (translations)
- ✅ Hierarchical categories
- ✅ Media management (images for posts/categories)
- ✅ Content blocks/sections (PostSection)
- ✅ Tagging system
- ✅ SEO metadata
- ✅ Admin back-office (EasyAdmin)

### 2.3 Entity Architecture

**Core entities** (check `src/Entity/` for full details):

```
Post
├── PostTranslation (title, content, slug, metadata per locale)
├── PostMedia (images/media)
│   └── PostMediaTranslation
├── PostSection (content blocks)
│   ├── PostSectionTranslation
│   └── PostSectionMedia
│       └── PostSectionMediaTranslation
└── Tag (many-to-many)
    └── TagTranslation

Category
├── CategoryTranslation
└── CategoryMedia
    └── CategoryMediaTranslation
```

**Key patterns**:
- All translatable entities have a separate `*Translation` entity
- Media entities follow the pattern `{Entity}Media` → `{Entity}MediaTranslation`
- Uses `Gedmo` extensions (timestampable, sluggable, translatable)
- Validation rules defined in `config/validator/`

### 2.3 Directory Structure

```
src/
├── Command/           # CLI commands
├── Controller/
│   ├── Admin/        # EasyAdmin CRUD controllers
│   └── [Frontend]    # Public-facing controllers
├── Entity/           # Doctrine entities
│   ├── Contracts/    # Interfaces
│   └── Traits/       # Shared behaviors
├── EventSubscriber/  # Doctrine & Kernel events
├── Factory/          # Foundry factories (tests/fixtures)
├── Form/             # Symfony forms
├── Repository/       # Doctrine repositories
├── Services/         # Business logic services
│   ├── Cache/
│   ├── Filter/
│   ├── Locale/
│   ├── Media/
│   ├── Post/
│   └── Slug/
└── Validator/        # Custom validation constraints
```

---

## 3. CORE PRINCIPLES

### Priority 1: MUST Follow

1. **Security First**
   - MUST validate all user inputs
   - MUST use Symfony's security components properly
   - MUST sanitize output in templates
   - MUST follow OWASP guidelines

2. **Symfony Best Practices**
   - MUST follow [official Symfony best practices](https://symfony.com/doc/current/best_practices.html)
   - MUST use dependency injection (no service locators)
   - MUST use proper form types for user input
   - MUST use Doctrine repositories correctly (no queries in controllers)

3. **Code Quality**
   - MUST pass PHPStan (level configured in phpstan.dist.neon)
   - MUST pass PHP CS Fixer (PSR-12 + Symfony rules)
   - MUST have tests for services and controllers
   - MUST follow TDD (Test-Driven Development)

4. **SOLID Principles**
   - MUST write single-responsibility classes
   - MUST use interfaces for contracts
   - MUST favor composition over inheritance

### Priority 2: SHOULD Follow

1. **Performance**
   - SHOULD optimize database queries (avoid N+1)
   - SHOULD use PARTIAL to prevent Doctrine from hydrating all entities and create specific Models per query
   - SHOULD use caching where appropriate (see `Services/Cache/`)
   - SHOULD lazy-load relationships when possible

2. **Maintainability**
   - SHOULD write self-documenting code (clear naming)
   - SHOULD add PHPDoc only when adding value
   - SHOULD keep methods small (<20 lines ideally)
   - SHOULD use type hints extensively (PHP 8.1+ features)

3. **Testing**
   - SHOULD test edge cases and error conditions
   - SHOULD use Foundry factories for test data
   - SHOULD prefer integration tests over unit tests (see docs/testing/complete-guide.md)

### Priority 3: MAY Consider

1. **Optimization**
   - MAY refactor working code for readability
   - MAY suggest architectural improvements
   - MAY propose performance enhancements

---

## 4. WORKFLOW & DECISION MAKING

### 4.1 Task Analysis Phase

**For EVERY new task**:

1. **Read Memory First**
   ```
   → Check if similar work was done before
   → Look for related architectural decisions
   → Find relevant patterns in memory
   ```

2. **Assess Complexity**
   - Simple (1-2 steps) → Proceed directly
   - Medium (3-5 steps) → Use todo list
   - Complex (6+ steps or unclear) → **MUST use sequential thinking**

3. **Identify Scope**
   - Single file edit → Proceed
   - Multiple files → Plan with sequential thinking
   - New feature → **MUST use sequential thinking + memory check**

### 4.2 Planning Phase

**For complex tasks (>3 steps)**:

1. **Use Sequential Thinking Tool**
   ```
   ✓ Break down into logical steps
   ✓ Identify dependencies between steps
   ✓ Consider edge cases
   ✓ Evaluate trade-offs
   ✓ Plan validation approach
   ```

2. **Check Documentation**
   - Use Context7 for Symfony/Doctrine/library APIs
   - Use Serena to understand existing code patterns
   - Check composer.json for library versions

3. **Create Todo List**
   - Use TodoWrite tool for tracking
   - Mark tasks as in_progress/completed
   - Keep user informed of progress

### 4.3 Implementation Phase

**Step-by-step process**:

1. **Activate Serena**
   ```bash
   # MUST do this at start of any code analysis/generation
   Serena: activate ~/PhpstormProjects/tinie-bakerie
   ```

2. **Understand Before Changing**
   - Use Serena `find_symbol` to locate existing code
   - Use Serena `find_referencing_symbols` to understand impact
   - Read related entities and services

3. **Follow TDD (Test-Driven Development)**
   - **RED**: Write failing test first (describes desired behavior)
   - **GREEN**: Write minimal code to make test pass
   - **REFACTOR**: Improve design with confidence (tests protect you)
   - See [docs/testing/complete-guide.md](docs/testing/complete-guide.md) for detailed TDD workflow

4. **Implement Using Best Practices**
   - Follow existing patterns in the codebase
   - Use appropriate Symfony components
   - Add proper type hints and return types
   - Handle errors gracefully

### 4.4 Validation Phase

**MUST complete before marking task done**:

1. **Run Quality Tools**
   ```bash
   make quality  # Runs PHPStan + Rector + PHP CS Fixer
   ```

2. **Run Tests**
   ```bash
   make test     # Or appropriate test command
   ```

3. **Manual Verification**
   - Check that code solves the original problem
   - Verify no regressions
   - Confirm code is readable and maintainable

4. **Update Memory**
   - Store new patterns discovered
   - Document architectural decisions
   - Record any gotchas or lessons learned

### 4.5 Decision Making Framework

**When to ask vs. proceed**:

| Situation | Action |
|-----------|--------|
| Ambiguous requirements | **ASK** for clarification |
| Multiple valid approaches | **Use sequential thinking**, then suggest best option |
| Security implications | **ASK** before proceeding |
| Breaking changes | **ASK** before proceeding |
| Standard implementation | **PROCEED** with best practices |
| Bug fix (clear cause) | **PROCEED** with fix + test |
| Refactoring (improves code) | **PROCEED** if safe, inform user |

---

## 5. TOOL USAGE STRATEGY

### 5.1 Sequential Thinking (sequentialthinking_sequentialthinking)

**MUST use when**:
- ✅ Planning new features or major refactoring
- ✅ Debugging complex issues (>2 potential causes)
- ✅ Analyzing architectural decisions
- ✅ Evaluating multiple implementation approaches
- ✅ Designing database schema changes
- ✅ Planning multi-step migrations

**Process**:
1. Set appropriate `totalThoughts` (start conservatively, adjust as needed)
2. Break problem into logical steps
3. Question assumptions and validate against codebase
4. Consider edge cases and failure modes
5. Evaluate trade-offs between approaches
6. Use `is_revision=true` if earlier thinking needs correction
7. Only set `nextThoughtNeeded=false` when truly confident

**Example trigger phrases**:
- "How should I implement..."
- "What's the best way to..."
- "This could be done multiple ways..."
- "I need to refactor..."

### 5.2 Memory MCP (memory_*)

**Storage Strategy**:

| Memory Name | What to Store | When to Update |
|-------------|---------------|----------------|
| `architecture-core` | High-level design decisions | After major architectural changes |
| `architecture-entities` | Entity relationships & patterns | When discovering complex entity logic |
| `patterns-services` | Service layer patterns | When finding repeated patterns |
| `patterns-forms` | Form handling patterns | When working with complex forms |
| `debugging-doctrine` | Doctrine-specific gotchas | After solving tricky ORM issues |
| `debugging-easyadmin` | EasyAdmin customizations | When customizing admin panels |
| `conventions-project` | Project-specific rules | When discovering unwritten conventions |
| `performance-optimizations` | Successful optimizations | After performance improvements |

**Read Memory**:
- **MUST** check at start of every conversation (if relevant memories exist)
- **SHOULD** search for related memories before major implementations
- **MAY** skim all memories periodically to refresh context

**Update Memory**:
- **MUST** update after discovering important patterns/decisions
- **SHOULD** update after solving complex bugs
- **MAY** update for minor learnings that could help future work

**Memory Operations**:
```
→ List all memories: memory_read_graph or serena_list_memories
→ Search memories: memory_search_nodes
→ Store new knowledge: memory_create_entities + memory_create_relations
→ Update existing: memory_add_observations
```

### 5.3 Context7 MCP (context7_*)

**MUST use when**:
- Implementing features with Symfony components you're unsure about
- Setting up new bundles or libraries
- Need API documentation for Doctrine, Twig, EasyAdmin
- Checking compatibility with library versions
- Learning about Hotwired Turbo/Stimulus best practices

**Process**:
1. Use `resolve_library_id` first (unless user provides exact ID)
2. Use `get_library_docs` with specific topic
3. Check composer.json for actual version used in project

**Project Libraries**:
```
Symfony → symfony/symfony or symfony/{component}
Doctrine → doctrine/orm or doctrine/doctrine-bundle
Twig → twig/twig
EasyAdmin → easycorp/easyadmin-bundle
Hotwired → hotwired/turbo or hotwired/stimulus
PHPUnit → phpunit/phpunit
Foundry → zenstruck/foundry
```

**Do NOT use Context7 for**:
- General PHP questions (you know PHP well)
- Project-specific patterns (use Serena + Memory instead)
- Code already in the codebase (use Serena to analyze)

---

## 5.5 Code Comments Policy

**IMPORTANT**: Keep generated code clean and production-ready.

**Code Comments Rules**:
- ❌ **DO NOT add comments explaining the code generation process**
- ❌ **DO NOT add "Generated by AI" or similar meta-comments**
- ❌ **DO NOT add TODO comments about code generation**
- ❌ **DO NOT over-comment obvious code**
- ✅ **ONLY add comments when code logic is complex or non-obvious**
- ✅ **DO add PHPDoc for public API methods** (parameters, return types, exceptions)
- ✅ **DO add brief explanations for complex algorithms or business logic**

**Examples**:

```php
// ❌ BAD - Unnecessary generation comments
class PostService
{
    // AI generated: This method creates a post
    // TODO: Review this AI-generated code
    public function createPost(string $title): Post
    {
        // Creating new post instance
        $post = new Post();
        // Setting title
        $post->setTitle($title);
        return $post;
    }
}

// ✅ GOOD - Clean, self-documenting code
class PostService
{
    public function createPost(string $title): Post
    {
        $post = new Post();
        $post->setTitle($title);
        return $post;
    }
    
    /**
     * Calculates post popularity score based on views, likes, and engagement.
     * 
     * Formula: (views * 0.1) + (likes * 2) + (comments * 5)
     * Weights favor engagement over passive views.
     */
    public function calculatePopularityScore(Post $post): float
    {
        return ($post->getViews() * 0.1) 
            + ($post->getLikes() * 2) 
            + ($post->getComments()->count() * 5);
    }
}
```

**Rationale**: The codebase should be indistinguishable from manually written code. Excessive comments create maintenance burden and clutter.

### 5.4 Serena MCP (serena_*)

**MUST activate at start**:
```
serena_activate_project: ~/PhpstormProjects/tinie-bakerie
```

**Primary Use Cases**:

1. **Finding Code**
   - `find_symbol` → Locate classes, methods, properties
   - `find_referencing_symbols` → See where code is used
   - `search_for_pattern` → Regex search across files

2. **Understanding Structure**
   - `get_symbols_overview` → Get file overview
   - `list_dir` → Explore directory contents

3. **Modifying Code**
   - `replace_symbol_body` → Replace method/class implementation
   - `insert_after_symbol` / `insert_before_symbol` → Add new code
   - `rename_symbol` → Refactor names across codebase

4. **Knowledge Management**
   - `write_memory` → Store project learnings
   - `read_memory` → Retrieve stored knowledge
   - `list_memories` → See available memories

**Best Practices**:
- ALWAYS use `find_symbol` before modifying code
- ALWAYS check `find_referencing_symbols` for impact analysis
- Use `relative_path` parameter to scope searches when possible
- Use `include_body=true` only when you need to see implementation

---

## 6. CODE STANDARDS & PATTERNS

### 6.1 PHP Standards

**MUST follow**:
- ✅ PSR-12 coding style
- ✅ PHP 8.1+ features (use modern syntax)
- ✅ Strict types: `declare(strict_types=1);` in all files
- ✅ Type hints for parameters and return types
- ✅ Final classes by default (unless designed for inheritance)
- ✅ Readonly properties where appropriate (PHP 8.1+)

**Example**:
```php
<?php

declare(strict_types=1);

namespace App\Services\Post;

final class PostService
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly SlugService $slugService,
    ) {}

    public function createPost(string $title, string $content): Post
    {
        // Implementation
    }
}
```

### 6.2 Symfony Patterns

**Controllers**:
- MUST be thin (delegate to services)
- MUST use dependency injection (constructor injection)
- MUST use route attributes (not YAML)
- SHOULD use `#[Route]` with explicit methods

```php
#[Route('/post/{slug}', name: 'post_show', methods: ['GET'])]
public function show(Post $post): Response
{
    return $this->render('post/show.html.twig', [
        'post' => $post,
    ]);
}
```

**Services**:
- MUST be in `src/Services/` organized by domain
- MUST use constructor injection
- MUST be final unless designed for inheritance
- SHOULD have single responsibility
- SHOULD use interfaces for contracts (see `src/Entity/Contracts/`)

**Entities**:
- MUST use attributes for Doctrine mapping (not YAML)
- MUST have proper relationships (OneToMany, ManyToOne, etc.)
- MUST use Gedmo extensions via traits (see `src/Entity/Traits/`)
- SHOULD use validation attributes (or YAML in `config/validator/`)
- SHOULD implement relevant interfaces from `src/Entity/Contracts/`

**Forms**:
- MUST be in `src/Form/`
- MUST extend AbstractType
- MUST use dependency injection for complex forms
- SHOULD use form events for dynamic behavior

**Repositories**:
- MUST extend ServiceEntityRepository
- MUST contain only query methods (no business logic)
- SHOULD use QueryBuilder for complex queries
- SHOULD use DQL for very complex queries

### 6.3 Translation Pattern

**All translatable entities follow this pattern**:

```php
// Main entity
#[ORM\Entity]
class Post
{
    #[ORM\OneToMany(mappedBy: 'translatable', targetEntity: PostTranslation::class, cascade: ['persist', 'remove'])]
    private Collection $translations;
}

// Translation entity
#[ORM\Entity]
class PostTranslation
{
    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: 'translations')]
    private ?Post $translatable = null;
    
    #[ORM\Column(length: 5)]
    private string $locale;
    
    #[ORM\Column(length: 255)]
    private string $title;
}
```

**When adding translatable fields**:
1. Add property to `*Translation` entity
2. Update form types in `src/Form/`
3. Update validation in `config/validator/`
4. Update templates to use translation

### 6.4 Media Pattern

**All media entities follow this pattern**:

```
{Entity}Media (main media entity)
├── file path/URL
├── type (image/video/etc)
├── ManyToOne → {Entity}
└── OneToMany → {Entity}MediaTranslation

{Entity}MediaTranslation
├── ManyToOne → {Entity}Media
├── locale
└── translatable fields (alt, caption, etc)
```

**When adding media support**:
1. Create `{Entity}Media` entity
2. Create `{Entity}MediaTranslation` entity
3. Add OneToMany to parent entity
4. Create form types
5. Create upload handling service (see `Services/Media/`)

---

## 7. TESTING STRATEGY

> **📋 For complete testing guide, see [docs/testing/complete-guide.md](docs/testing/complete-guide.md)**

### Core Testing Principles

**MUST follow TDD (Test-Driven Development)**:
1. **RED**: Write failing test first
2. **GREEN**: Write minimal code to pass
3. **REFACTOR**: Improve design with confidence

**Testing Philosophy**:
- ✅ Test behavior, not implementation
- ✅ Prefer real objects over mocks
- ✅ Test at highest practical level (80% integration, 20% unit)
- ✅ Use `KernelTestCase` or `WebTestCase` for most tests
- ❌ Avoid excessive mocking

**PHPUnit Conventions**:
- **MUST** use PHP 8+ attributes: `#[DataProvider]`, `#[CoversClass]`, `#[Test]`
- **MUST** use `yield` in data providers with descriptive keys
- **MUST** use named array parameters in providers

**Example**:
```php
use PHPUnit\Framework\Attributes\{CoversClass, DataProvider};

#[CoversClass(PostService::class)]
final class PostServiceTest extends KernelTestCase
{
    #[DataProvider('providePostScenarios')]
    public function testPublishPost(bool $isPublished, string $expectedResult): void
    {
        // Test implementation
    }
    
    public static function providePostScenarios(): iterable
    {
        yield 'published post is visible' => [
            'isPublished' => true,
            'expectedResult' => 'visible',
        ];
        
        yield 'unpublished post returns 404' => [
            'isPublished' => false,
            'expectedResult' => '404',
        ];
    }
}
```

**For detailed testing patterns, TDD workflow, and examples, read [docs/testing/complete-guide.md](docs/testing/complete-guide.md).**

---

## 8. QUALITY ASSURANCE

### 8.1 Mandatory Checks

**Before marking any task complete**:

1. **Run Quality Tools**
   ```bash
   make quality
   # Equivalent to:
   # - vendor/bin/phpstan analyse
   # - vendor/bin/rector process --dry-run
   # - vendor/bin/php-cs-fixer fix --dry-run
   ```

2. **Fix All Issues**
   - PHPStan errors: MUST be fixed (no exceptions)
   - Rector suggestions: SHOULD apply (or justify why not)
   - CS Fixer issues: MUST be fixed (run without --dry-run)

3. **Run Tests**
   ```bash
   make test
   # Or: vendor/bin/phpunit
   ```

4. **Manual Review**
   - Code solves the original problem
   - No obvious bugs or edge cases missed
   - Code is readable and maintainable
   - Follows project patterns

### 8.2 Configuration Files

**PHPStan**: `phpstan.dist.neon`
- Check current level and rules
- MUST NOT lower level to pass tests
- MAY add specific ignores with justification

**PHP CS Fixer**: `.php-cs-fixer.dist.php`
- Uses PSR-12 + Symfony rules
- MUST NOT disable rules without asking

**Rector**: `rector.php`
- Suggests modern PHP patterns
- Apply suggestions unless breaking

---

## 9. COMMUNICATION PREFERENCES

### 9.1 Response Style

**DO**:
- ✅ Be concise but complete
- ✅ Use code examples when helpful
- ✅ Explain *why* not just *what*
- ✅ Point out potential issues proactively
- ✅ Show file paths and line numbers: `src/Service/PostService.php:42`
- ✅ Use task lists for multi-step work
- ✅ Inform about progress on long tasks

**DON'T**:
- ❌ Be overly verbose or repeat obvious things
- ❌ Use excessive emojis or casual language
- ❌ Assume I know what you're thinking (explain decisions)
- ❌ Implement without explaining the approach first (for complex tasks)

### 9.2 When to Ask Questions

**MUST ask when**:
- Requirements are ambiguous or contradictory
- Security implications are unclear
- Breaking changes would result
- Multiple valid approaches exist (after sequential thinking)

**SHOULD proceed when**:
- Best practice is clear
- Similar patterns exist in codebase
- Standard Symfony approach applies
- You've validated approach with sequential thinking

**Inform user when**:
- You discover issues in existing code
- You deviate from a request (with good reason)
- You make architectural decisions
- Quality tools find issues

### 9.3 Progress Updates

**For tasks taking >2 minutes**:
- Show todo list with progress
- Mark items as in_progress/completed
- Explain what you're currently doing

**For completed tasks**:
- Summarize what was done
- Mention any issues found/fixed
- Confirm quality checks passed
- Suggest next steps if relevant

---

## 10. ANTIPATTERNS & PITFALLS

### 10.1 Code Antipatterns to AVOID

❌ **Service Locators**
```php
// BAD
$service = $this->container->get(PostService::class);

// GOOD
public function __construct(private readonly PostService $postService) {}
```

❌ **Logic in Controllers**
```php
// BAD
public function create(Request $request): Response
{
    $post = new Post();
    $post->setTitle($request->request->get('title'));
    $post->setSlug($this->slugService->generate($title));
    $this->entityManager->persist($post);
    // ... more logic
}

// GOOD
public function create(Request $request, PostService $postService): Response
{
    $post = $postService->createFromRequest($request);
    // ...
}
```

❌ **Queries in Controllers**
```php
// BAD
$posts = $this->entityManager->getRepository(Post::class)->findBy([...]);

// GOOD - inject repository, use custom methods
$posts = $this->postRepository->findPublishedPosts();
```

❌ **Missing Type Hints**
```php
// BAD
public function process($data)

// GOOD
public function process(array $data): ProcessedResult
```

❌ **Mutable Services**
```php
// BAD
class PostService {
    private Post $currentPost; // State in service!
    
    public function setPost(Post $post): void { ... }
}

// GOOD - services should be stateless
class PostService {
    public function processPost(Post $post): void { ... }
}
```

### 10.2 Symfony-Specific Pitfalls

❌ **N+1 Queries**
```php
// BAD
foreach ($posts as $post) {
    $post->getCategory()->getName(); // Lazy load in loop
}

// GOOD
$posts = $postRepository->findWithCategory(); // JOIN in query
```

❌ **Forgetting Cascade Operations**
```php
// BAD - translations won't be persisted
#[ORM\OneToMany(mappedBy: 'post', targetEntity: PostTranslation::class)]
private Collection $translations;

// GOOD
#[ORM\OneToMany(mappedBy: 'post', targetEntity: PostTranslation::class, cascade: ['persist', 'remove'])]
private Collection $translations;
```

❌ **Hardcoded Locales**
```php
// BAD
$post->getTranslation('fr')->getTitle();

// GOOD - use RequestStack or TranslatableListener
$post->getTitle(); // Gedmo handles current locale
```

### 10.3 Common Mistakes in This Project

❌ **Forgetting Translation Entities**
- When adding fields, check if entity is translatable
- Translatable fields MUST go in `*Translation` entity

❌ **Not Using Existing Services**
- Check `src/Services/` before creating new ones
- Slug generation: use `SlugService`
- Media handling: use `MediaService`
- Caching: use services in `Services/Cache/`

❌ **Ignoring Validation Config**
- Validation rules are in `config/validator/` (YAML)
- Don't add validation attributes if YAML config exists
- Keep validation in one place (prefer YAML for this project)

❌ **Breaking EasyAdmin Customizations**
- Check `EventSubscriber/Admin/` for custom logic
- EasyAdmin controllers have custom behaviors
- Test admin panel after entity changes

### 10.4 Tool Usage Mistakes

❌ **Not Using Sequential Thinking When Needed**
- Jumping into implementation without planning
- Missing edge cases due to lack of structured thinking
- Making wrong architectural decisions

❌ **Not Checking Memory**
- Re-analyzing patterns already documented
- Inconsistent with previous architectural decisions
- Missing known gotchas and solutions

❌ **Not Using Context7**
- Guessing Symfony API instead of checking docs
- Using outdated patterns
- Missing library features

❌ **Not Activating Serena**
- Trying to search code manually
- Missing existing implementations
- Not understanding code structure before changing

---

## 11. QUICK REFERENCE

### Checklist for Every Task

```
□ Read relevant memories (if conversation start)
□ Activate Serena project
□ Use sequential thinking if complex (>3 steps)
□ Check Context7 docs if using unfamiliar APIs
□ Use Serena to understand existing code
□ Write failing test FIRST (TDD - Red phase)
□ Implement minimal code to pass (TDD - Green phase)
□ Refactor with confidence (TDD - Refactor phase)
□ Run make quality (PHPStan + Rector + CS Fixer)
□ Run make test (all tests pass)
□ Update memory with learnings
□ Mark task complete in todo list
```

### Common Commands

```bash
# Quality checks
make quality           # Run all quality tools
make phpstan          # Static analysis
make rector           # Code modernization (dry-run)
make cs-fix           # Code style fixes

# Testing
make test             # Run all tests
make test-unit        # Unit tests only
make test-functional  # Functional tests only

# Development
make cache-clear      # Clear Symfony cache
make fixtures         # Load fixtures
```

### Key Files to Reference

```
composer.json                    → Library versions
config/services.yaml            → Service configuration
config/packages/*.yaml          → Bundle configuration
config/validator/*.yaml         → Validation rules
phpstan.dist.neon              → PHPStan config
.php-cs-fixer.dist.php         → Code style config
rector.php                     → Rector config
docs/testing/complete-guide.md               → Complete testing strategy
```

---

## 12. MEMORY INITIALIZATION

**On first use of this instruction file**, you SHOULD:

1. **List existing memories**
   ```
   serena_list_memories or memory_read_graph
   ```

2. **If no memories exist**, create initial ones:
   - `architecture-core`: Document overall project architecture
   - `entities-relationships`: Map entity relationships
   - `conventions-project`: Document unwritten conventions discovered

3. **Update this instruction file** if you discover it's missing critical information

---

**Remember**: You are a senior developer. You have the knowledge and tools to make good decisions. Use sequential thinking for complex problems, check memory before re-inventing patterns, verify with Context7 when unsure, and always validate with quality tools. The goal is high-quality, maintainable code that follows best practices.
