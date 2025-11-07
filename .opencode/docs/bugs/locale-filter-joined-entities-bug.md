# Bug Report: Locale Filter Not Applied to Joined Entity Translations

## Status
**CONFIRMED BUG** - Discovered via `PostControllerTest::testShowWithFoundPost` for EN locale

## Summary
The Doctrine `locale_filter` is not properly filtering translations of related entities when they are loaded via explicit JOINs in DQL queries. This causes the wrong translation to be used when accessing related entity properties.

## Evidence

### Test Output
```
[DEBUG fr] Category has 2 translations loaded
[DEBUG fr] Category Slug from getSlug(): categorie-test-fr ✅ CORRECT

[DEBUG en] Category has 2 translations loaded  ← BUG!
[DEBUG en] Category Slug from getSlug(): categorie-test-fr  ← WRONG! Should be "test-category-en"
```

## Root Cause

### The Problem
When `PostRepository::findOneActive()` executes with locale filter enabled for 'en':

```php
$qb = $this->createQueryBuilder('p')
    ->leftJoin('p.category', 'c')       // Join Category
    ->addSelect('PARTIAL c.{id}')
    ->leftJoin('c.translations', 'ct')  // Join Category translations
    ->addSelect('PARTIAL ct.{id, title, slug}')
    ->where('p.active = :active')
    ->andWhere('pt.slug = :slug')
    // ...
```

**Expected**: `c.translations` collection should contain ONLY the 'en' translation
**Actual**: `c.translations` collection contains BOTH 'fr' and 'en' translations

### Why This Breaks

The `Category::getSlug()` method relies on this assumption:

```php
private function getLocalizedTranslation(): ?CategoryTranslation
{
    // With the locale filter enabled, there is only one translation in the collection.
    $translations = $this->getTranslations()->first();
    return false === $translations ? null : $translations;
}
```

When the collection has 2 translations, `->first()` returns whichever translation was added first (usually 'fr'), regardless of the active locale.

## Impact

### Where It Fails
1. **PostController::show()** - Line 53:
   ```php
   if ($categorySlug !== $post->getCategory()?->getSlug()) {
       throw $this->createNotFoundException();  // ← Fails for non-default locales
   }
   ```

2. **Any code that accesses translated properties of related entities**:
   - `$post->getCategory()->getTitle()`
   - `$post->getCategory()->getSlug()`
   - `$post->getCategory()->getDescription()`
   - etc.

### Affected Endpoints
- ✅ `/fr/articles/{categorySlug}/{postSlug}` - Works (default locale)
- ❌ `/en/posts/{categorySlug}/{postSlug}` - 404 Not Found

## Technical Details

### Locale Filter Implementation
**File**: `src/Services/Filter/LocaleFilter.php`

```php
class LocaleFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if (!$reflexionClass->implementsInterface(IsTranslation::class)) {
            return '';
        }
        
        return sprintf('%s.%s = %s', 
            $targetTableAlias, 
            'locale', 
            $this->getParameter('locale')
        );
    }
}
```

The filter SHOULD add `WHERE ct.locale = 'en'` to the SQL query, but apparently it doesn't work correctly with explicit JOINs in DQL.

## Doctrine SQL Filter Limitations

This is a **known Doctrine ORM limitation**: SQL Filters are designed to work with:
- Simple queries (`findBy`, `findAll`, etc.)
- Lazy-loaded associations

But they have issues with:
- **Explicit JOINs in DQL queries** ← This is our case!
- Collection fetching via `->getTranslations()`

Reference: https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/filters.html#filtering-associations

## Possible Solutions

### Solution 1: Add Explicit WHERE Clause in Repository (RECOMMENDED)
Modify `PostRepository::findOneActive()` to explicitly filter joined translations:

```php
$qb = $this->createQueryBuilder('p')
    ->leftJoin('p.category', 'c')
    ->addSelect('PARTIAL c.{id}')
    ->leftJoin('c.translations', 'ct', 'WITH', 'ct.locale = :locale')  // ← Add condition
    ->addSelect('PARTIAL ct.{id, title, slug}')
    ->setParameter('locale', $this->getCurrentLocale())  // Need to inject locale
    // ...
```

**Pros**: 
- Explicit and clear
- Fixes the immediate issue
- Works reliably with DQL

**Cons**: 
- Need to add locale parameter to all similar queries
- Must remember to do this for every repository method
- Requires injecting locale into repositories

### Solution 2: Fetch Category Separately (WORKAROUND)
Don't join Category translations in the main query:

```php
public function show(string $categorySlug, string $postSlug, Request $request): array
{
    $post = $this->cache->getOne($request->getLocale(), $postSlug);
    
    if (!$post instanceof Post) {
        throw $this->createNotFoundException();
    }
    
    // Force lazy-load category with locale filter active
    $category = $post->getCategory();
    $category->getSlug();  // Trigger lazy load with filter
    
    if ($categorySlug !== $category->getSlug()) {
        throw $this->createNotFoundException();
    }
    
    return ['post' => $post];
}
```

**Pros**: 
- Minimal code changes
- Leverages existing locale filter

**Cons**: 
- Additional query (N+1 potential)
- Relies on lazy loading behavior
- May not work if entity is cached/serialized

### Solution 3: Change getLocalizedTranslation() Logic (NOT RECOMMENDED)
Instead of using `->first()`, search for the correct locale:

```php
private function getLocalizedTranslation(): ?CategoryTranslation
{
    foreach ($this->getTranslations() as $translation) {
        if ($translation->getLocale() === $this->getCurrentLocale()) {
            return $translation;
        }
    }
    return $this->getTranslations()->first() ?: null;
}
```

**Pros**: 
- Defensive programming
- Works regardless of filter state

**Cons**: 
- Requires injecting current locale into entities (bad practice)
- Doesn't fix root cause
- Performance impact (loop through translations)

### Solution 4: Use Gedmo Translatable Extension ~~(BEST LONG-TERM)~~
Replace custom translation implementation with Gedmo's proven solution:
- https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md

**Pros**: 
- Battle-tested solution
- Handles all edge cases
- Automatic locale management

**Cons**: 
- Major refactoring required
- Migration effort for existing data
- **Doesn't work well with Form Types and EasyAdmin** ← This is why we use custom implementation

## Recommendation

**Immediate Fix**: Solution 1 (Add explicit WHERE clauses)
- Add helper method to extract locale from filter
- Explicitly filter joined translations with `WITH` clause
- See: `.opencode/docs/bugs/PROPOSED-FIX.md` and `FIX-EXAMPLE.php`

**Why not Gedmo?**: 
The project uses a custom translation implementation because Gedmo Translatable has issues with:
- Symfony Form Types (complex translation field handling)
- EasyAdmin CRUD operations (translation UI/UX challenges)

The custom implementation works great with forms/admin - just needs the JOIN fix!

## Files Affected

### Need Fixing
- `src/Repository/PostRepository.php` - Methods: `findAllActive()`, `findOneActive()`
- `src/Repository/CategoryRepository.php` - Methods: `findAll()`, `findAllSlugs()`, `findOne()`
- Any other repository that joins translations

### Related
- `src/Services/Filter/LocaleFilter.php` - Current filter implementation
- `src/Entity/Category.php` - Assumes filter works correctly
- `src/Entity/Post.php` - Assumes filter works correctly
- `src/Controller/PostController.php` - Validation fails due to bug

## Test Coverage

### Failing Test
- `tests/Functional/Controller/PostControllerTest::testShowWithFoundPost` for EN locale

### Passing Tests (False Positive)
- Same test for FR locale (works because it's the default/first translation)

## Related Issues
- Category controller works fine because it doesn't validate related entity slugs
- The bug only manifests when accessing properties of related translated entities
- Cache layer amplifies the problem by caching entities with wrong translations
